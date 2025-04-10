<?php
namespace MediaWiki\Content\Transform;

use MediaWiki\Page\PageReference;
use MediaWiki\Parser\ParserOptions;

/**
 * @internal
 * An object to hold preload transform params.
 */
class PreloadTransformParamsValue implements PreloadTransformParams {
	/** @var PageReference */
	private $page;

	/** @var array */
	private $params;

	/** @var ParserOptions */
	private $parserOptions;

	public function __construct( PageReference $page, ParserOptions $parserOptions, array $params = [] ) {
		$this->page = $page;
		$this->parserOptions = $parserOptions;
		$this->params = $params;
	}

	public function getPage(): PageReference {
		return $this->page;
	}

	public function getParams(): array {
		return $this->params;
	}

	public function getParserOptions(): ParserOptions {
		return $this->parserOptions;
	}
}
