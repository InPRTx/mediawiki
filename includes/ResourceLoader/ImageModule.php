<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @author Trevor Parscal
 */
namespace MediaWiki\ResourceLoader;

use DomainException;
use InvalidArgumentException;
use Wikimedia\Minify\CSSMin;

/**
 * Module for generated and embedded images.
 *
 * @ingroup ResourceLoader
 * @since 1.25
 */
class ImageModule extends Module {
	/** @var bool */
	private $useMaskImage;
	/** @var array|null */
	protected $definition;

	/**
	 * Local base path, see __construct()
	 * @var string
	 */
	protected $localBasePath = '';

	/** @inheritDoc */
	protected $origin = self::ORIGIN_CORE_SITEWIDE;

	/** @var Image[][]|null */
	protected $imageObjects = null;
	/** @var array */
	protected $images = [];
	/** @var string|null */
	protected $defaultColor = null;
	/** @var bool */
	protected $useDataURI = true;
	/** @var array|null */
	protected $globalVariants = null;
	/** @var array */
	protected $variants = [];
	/** @var string|null */
	protected $prefix = null;
	/** @var string */
	protected $selectorWithoutVariant = '.{prefix}-{name}';
	/** @var string */
	protected $selectorWithVariant = '.{prefix}-{name}-{variant}';

	/**
	 * Constructs a new module from an options array.
	 *
	 * @param array $options List of options; if not given or empty, an empty module will be
	 *     constructed
	 * @param string|null $localBasePath Base path to prepend to all local paths in $options. Defaults
	 *     to $IP
	 *
	 * Below is a description for the $options array:
	 * @par Construction options:
	 * @code
	 *     [
	 *         // When set the icon will use mask-image instead of background-image for the CSS output. Using mask-image
	 *         // allows colorization of SVGs in Codex. Defaults to false for backwards compatibility.
	 *         'useMaskImage' => false,
	 *         // Base path to prepend to all local paths in $options. Defaults to $IP
	 *         'localBasePath' => [base path],
	 *         // Path to JSON file that contains any of the settings below
	 *         'data' => [file path string]
	 *         // CSS class prefix to use in all style rules
	 *         'prefix' => [CSS class prefix],
	 *         // Alternatively: Format of CSS selector to use in all style rules
	 *         'selector' => [CSS selector template, variables: {prefix} {name} {variant}],
	 *         // Alternatively: When using variants
	 *         'selectorWithoutVariant' => [CSS selector template, variables: {prefix} {name}],
	 *         'selectorWithVariant' => [CSS selector template, variables: {prefix} {name} {variant}],
	 *         // List of variants that may be used for the image files
	 *         'variants' => [
	 *             // This level of nesting can be omitted if you use the same images for every skin
	 *             [skin name (or 'default')] => [
	 *                 [variant name] => [
	 *                     'color' => [color string, e.g. '#ffff00'],
	 *                     'global' => [boolean, if true, this variant is available
	 *                                  for all images of this type],
	 *                 ],
	 *                 ...
	 *             ],
	 *             ...
	 *         ],
	 *         // List of image files and their options
	 *         'images' => [
	 *             // This level of nesting can be omitted if you use the same images for every skin
	 *             [skin name (or 'default')] => [
	 *                 [icon name] => [
	 *                     'file' => [file path string or array whose values are file path strings
	 *                                    and whose keys are 'default', 'ltr', 'rtl', a single
	 *                                    language code like 'en', or a list of language codes like
	 *                                    'en,de,ar'],
	 *                     'variants' => [array of variant name strings, variants
	 *                                    available for this image],
	 *                 ],
	 *                 ...
	 *             ],
	 *             ...
	 *         ],
	 *     ]
	 * @endcode
	 */
	public function __construct( array $options = [], $localBasePath = null ) {
		$this->useMaskImage = $options['useMaskImage'] ?? false;
		$this->localBasePath = static::extractLocalBasePath( $options, $localBasePath );

		$this->definition = $options;
	}

	/**
	 * Parse definition and external JSON data, if referenced.
	 */
	protected function loadFromDefinition() {
		if ( $this->definition === null ) {
			return;
		}

		$options = $this->definition;
		$this->definition = null;

		if ( isset( $options['data'] ) ) {
			$dataPath = $this->getLocalPath( $options['data'] );
			$data = json_decode( file_get_contents( $dataPath ), true );
			$options = array_merge( $data, $options );
		}

		// Accepted combinations:
		// * prefix
		// * selector
		// * selectorWithoutVariant + selectorWithVariant
		// * prefix + selector
		// * prefix + selectorWithoutVariant + selectorWithVariant

		$prefix = isset( $options['prefix'] ) && $options['prefix'];
		$selector = isset( $options['selector'] ) && $options['selector'];
		$selectorWithoutVariant = isset( $options['selectorWithoutVariant'] )
			&& $options['selectorWithoutVariant'];
		$selectorWithVariant = isset( $options['selectorWithVariant'] )
			&& $options['selectorWithVariant'];

		if ( $selectorWithoutVariant && !$selectorWithVariant ) {
			throw new InvalidArgumentException(
				"Given 'selectorWithoutVariant' but no 'selectorWithVariant'."
			);
		}
		if ( $selectorWithVariant && !$selectorWithoutVariant ) {
			throw new InvalidArgumentException(
				"Given 'selectorWithVariant' but no 'selectorWithoutVariant'."
			);
		}
		if ( $selector && $selectorWithVariant ) {
			throw new InvalidArgumentException(
				"Incompatible 'selector' and 'selectorWithVariant'+'selectorWithoutVariant' given."
			);
		}
		if ( !$prefix && !$selector && !$selectorWithVariant ) {
			throw new InvalidArgumentException(
				"None of 'prefix', 'selector' or 'selectorWithVariant'+'selectorWithoutVariant' given."
			);
		}

		foreach ( $options as $member => $option ) {
			switch ( $member ) {
				case 'images':
				case 'variants':
					if ( !is_array( $option ) ) {
						throw new InvalidArgumentException(
							"Invalid list error. '$option' given, array expected."
						);
					}
					if ( !isset( $option['default'] ) ) {
						// Backwards compatibility
						$option = [ 'default' => $option ];
					}
					foreach ( $option as $data ) {
						if ( !is_array( $data ) ) {
							throw new InvalidArgumentException(
								"Invalid list error. '$data' given, array expected."
							);
						}
					}
					$this->{$member} = $option;
					break;

				case 'useDataURI':
					$this->{$member} = (bool)$option;
					break;
				case 'defaultColor':
				case 'prefix':
				case 'selectorWithoutVariant':
				case 'selectorWithVariant':
					$this->{$member} = (string)$option;
					break;

				case 'selector':
					$this->selectorWithoutVariant = $this->selectorWithVariant = (string)$option;
			}
		}
	}

	/**
	 * Get CSS class prefix used by this module.
	 * @return string
	 */
	public function getPrefix() {
		$this->loadFromDefinition();
		return $this->prefix;
	}

	/**
	 * Get CSS selector templates used by this module.
	 * @return string[]
	 */
	public function getSelectors() {
		$this->loadFromDefinition();
		return [
			'selectorWithoutVariant' => $this->selectorWithoutVariant,
			'selectorWithVariant' => $this->selectorWithVariant,
		];
	}

	/**
	 * Get an Image object for given image.
	 * @param string $name Image name
	 * @param Context $context
	 * @return Image|null
	 */
	public function getImage( $name, Context $context ): ?Image {
		$this->loadFromDefinition();
		$images = $this->getImages( $context );
		return $images[$name] ?? null;
	}

	/**
	 * Get Image objects for all images.
	 * @param Context $context
	 * @return Image[] Array keyed by image name
	 */
	public function getImages( Context $context ): array {
		$skin = $context->getSkin();
		if ( $this->imageObjects === null ) {
			$this->loadFromDefinition();
			$this->imageObjects = [];
		}
		if ( !isset( $this->imageObjects[$skin] ) ) {
			$this->imageObjects[$skin] = [];
			if ( !isset( $this->images[$skin] ) ) {
				$this->images[$skin] = $this->images['default'] ?? [];
			}
			foreach ( $this->images[$skin] as $name => $options ) {
				$fileDescriptor = is_array( $options ) ? $options['file'] : $options;

				$allowedVariants = array_merge(
					( is_array( $options ) && isset( $options['variants'] ) ) ? $options['variants'] : [],
					$this->getGlobalVariants( $context )
				);
				if ( isset( $this->variants[$skin] ) ) {
					$variantConfig = array_intersect_key(
						$this->variants[$skin],
						array_fill_keys( $allowedVariants, true )
					);
				} else {
					$variantConfig = [];
				}

				$image = new Image(
					$name,
					$this->getName(),
					$fileDescriptor,
					$this->localBasePath,
					$variantConfig,
					$this->defaultColor
				);
				$this->imageObjects[$skin][$image->getName()] = $image;
			}
		}

		return $this->imageObjects[$skin];
	}

	/**
	 * Get list of variants in this module that are 'global', i.e., available
	 * for every image regardless of image options.
	 * @param Context $context
	 * @return string[]
	 */
	public function getGlobalVariants( Context $context ): array {
		$skin = $context->getSkin();
		if ( $this->globalVariants === null ) {
			$this->loadFromDefinition();
			$this->globalVariants = [];
		}
		if ( !isset( $this->globalVariants[$skin] ) ) {
			$this->globalVariants[$skin] = [];
			if ( !isset( $this->variants[$skin] ) ) {
				$this->variants[$skin] = $this->variants['default'] ?? [];
			}
			foreach ( $this->variants[$skin] as $name => $config ) {
				if ( $config['global'] ?? false ) {
					$this->globalVariants[$skin][] = $name;
				}
			}
		}

		return $this->globalVariants[$skin];
	}

	public function getStyles( Context $context ): array {
		$this->loadFromDefinition();

		// Build CSS rules
		$rules = [];

		$sources = $oldSources = $context->getResourceLoader()->getSources();
		$this->getHookRunner()->onResourceLoaderModifyEmbeddedSourceUrls( $sources );
		if ( array_keys( $sources ) !== array_keys( $oldSources ) ) {
			throw new DomainException( 'ResourceLoaderModifyEmbeddedSourceUrls hook must not add or remove sources' );
		}
		$script = $sources[ $this->getSource() ];

		$selectors = $this->getSelectors();

		foreach ( $this->getImages( $context ) as $name => $image ) {
			$declarations = $this->getStyleDeclarations( $context, $image, $script );
			$selector = strtr(
				$selectors['selectorWithoutVariant'],
				[
					'{prefix}' => $this->getPrefix(),
					'{name}' => $name,
					'{variant}' => '',
				]
			);
			$rules[] = "$selector {\n\t$declarations\n}";

			foreach ( $image->getVariants() as $variant ) {
				$declarations = $this->getStyleDeclarations( $context, $image, $script, $variant );
				$selector = strtr(
					$selectors['selectorWithVariant'],
					[
						'{prefix}' => $this->getPrefix(),
						'{name}' => $name,
						'{variant}' => $variant,
					]
				);
				$rules[] = "$selector {\n\t$declarations\n}";
			}
		}

		$style = implode( "\n", $rules );

		return [ 'all' => $style ];
	}

	/**
	 * This method must not be used by getDefinitionSummary as doing so would cause
	 * an infinite loop (we use Image::getUrl below which calls
	 * Module:getVersionHash, which calls Module::getDefinitionSummary).
	 *
	 * @param Context $context
	 * @param Image $image Image to get the style for
	 * @param string $script URL to load.php
	 * @param string|null $variant Variant to get the style for
	 * @return string
	 */
	private function getStyleDeclarations(
		Context $context,
		Image $image,
		$script,
		$variant = null
	) {
		$imageDataUri = $this->useDataURI ? $image->getDataUri( $context, $variant, 'original' ) : false;
		$primaryUrl = $imageDataUri ?: $image->getUrl( $context, $script, $variant, 'original' );
		$declarations = $this->getCssDeclarations(
			$primaryUrl
		);
		return implode( "\n\t", $declarations );
	}

	/**
	 * Format the CSS declaration for the image as a background-image property.
	 *
	 * @param string $primary Primary URI
	 * @return string[] CSS declarations
	 */
	protected function getCssDeclarations( $primary ): array {
		$primaryUrl = CSSMin::buildUrlValue( $primary );
		if ( $this->supportsMaskImage() ) {
			return [
				"-webkit-mask-image: $primaryUrl;",
				"mask-image: $primaryUrl;",
			];
		}
		return [
			"background-image: $primaryUrl;",
		];
	}

	/**
	 * @return bool
	 */
	public function supportsMaskImage() {
		return $this->useMaskImage;
	}

	/**
	 * @return bool
	 */
	public function supportsURLLoading() {
		return false;
	}

	/**
	 * Get the definition summary for this module.
	 *
	 * @param Context $context
	 * @return array
	 */
	public function getDefinitionSummary( Context $context ) {
		$this->loadFromDefinition();
		$summary = parent::getDefinitionSummary( $context );

		$options = [];
		foreach ( [
			'localBasePath',
			'images',
			'variants',
			'prefix',
			'selectorWithoutVariant',
			'selectorWithVariant',
		] as $member ) {
			$options[$member] = $this->{$member};
		}

		$summary[] = [
			'options' => $options,
			'fileHashes' => $this->getFileHashes( $context ),
		];
		return $summary;
	}

	/**
	 * Helper method for getDefinitionSummary.
	 * @param Context $context
	 * @return array
	 */
	private function getFileHashes( Context $context ) {
		$this->loadFromDefinition();
		$files = [];
		foreach ( $this->getImages( $context ) as $image ) {
			$files[] = $image->getPath( $context );
		}
		$files = array_values( array_unique( $files ) );
		return array_map( [ self::class, 'safeFileHash' ], $files );
	}

	/**
	 * @param string|FilePath $path
	 * @return string
	 */
	protected function getLocalPath( $path ) {
		if ( $path instanceof FilePath ) {
			return $path->getLocalPath();
		}

		return "{$this->localBasePath}/$path";
	}

	/**
	 * Extract a local base path from module definition information.
	 *
	 * @param array $options Module definition
	 * @param string|null $localBasePath Path to use if not provided in module definition. Defaults
	 *  to $IP.
	 * @return string Local base path
	 */
	public static function extractLocalBasePath( array $options, $localBasePath = null ) {
		global $IP;

		if ( array_key_exists( 'localBasePath', $options ) ) {
			$localBasePath = (string)$options['localBasePath'];
		}

		return $localBasePath ?? $IP;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return self::LOAD_STYLES;
	}
}
