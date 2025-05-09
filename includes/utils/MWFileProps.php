<?php
/**
 * MimeMagic helper functions for detecting and dealing with MIME types.
 *
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
 */

use MediaWiki\FileRepo\File\File;
use Wikimedia\FileBackend\FileBackend;
use Wikimedia\FileBackend\FSFile\FSFile;
use Wikimedia\Mime\MimeAnalyzer;

/**
 * MimeMagic helper wrapper
 *
 * @since 1.28
 */
class MWFileProps {
	/** @var MimeAnalyzer */
	private $magic;

	public function __construct( MimeAnalyzer $magic ) {
		$this->magic = $magic;
	}

	/**
	 * Get an associative array containing information about
	 * a file with the given storage path.
	 *
	 * Resulting array fields include:
	 *   - fileExists
	 *   - size (filesize in bytes)
	 *   - mime (as major/minor)
	 *   - media_type (value to be used with the MEDIATYPE_xxx constants)
	 *   - metadata (handler specific)
	 *   - sha1 (in base 36)
	 *   - width
	 *   - height
	 *   - bits (bitrate)
	 *   - file-mime
	 *   - major_mime
	 *   - minor_mime
	 *
	 * @param string $path Filesystem path to a file
	 * @param string|bool|null $ext The file extension, or true to extract it from the filename.
	 *  Set it to false to ignore the extension. Might be null in case the file is going to be
	 *  stashed.
	 * @return array
	 * @since 1.28
	 */
	public function getPropsFromPath( $path, $ext ) {
		$fsFile = new FSFile( $path );

		$info = $this->newPlaceholderProps();
		$info['fileExists'] = $fsFile->exists();
		if ( $info['fileExists'] ) {
			$info['size'] = $fsFile->getSize(); // bytes
			$info['sha1'] = $fsFile->getSha1Base36();

			# MIME type according to file contents
			$info['file-mime'] = $this->magic->guessMimeType( $path, false );
			# Logical MIME type
			$ext = ( $ext === true ) ? FileBackend::extensionFromPath( $path ) : (string)$ext;

			# XXX: MimeAnalyzer::improveTypeFromExtension() may return null (T253483).
			# Unclear if callers of this method expect that.
			$info['mime'] = $this->magic->improveTypeFromExtension( $info['file-mime'], $ext );

			[ $info['major_mime'], $info['minor_mime'] ] = File::splitMime( $info['mime'] );
			$info['media_type'] = $this->magic->getMediaType( $path, $info['mime'] );

			# Height, width and metadata
			// @phan-suppress-next-line PhanTypeMismatchArgumentNullable See XXX above
			$handler = MediaHandler::getHandler( $info['mime'] );
			if ( $handler ) {
				$sizeAndMetadata = $handler->getSizeAndMetadataWithFallback( $fsFile, $path );
				if ( $sizeAndMetadata ) {
					$info = $sizeAndMetadata + $info;
				}
			}
		}

		return $info;
	}

	/**
	 * Empty place holder props for non-existing files
	 *
	 * Resulting array fields include:
	 *   - fileExists
	 *   - size (filesize in bytes)
	 *   - mime (as major/minor)
	 *   - media_type (value to be used with the MEDIATYPE_xxx constants)
	 *   - metadata (handler specific)
	 *   - sha1 (in base 36)
	 *   - width
	 *   - height
	 *   - bits (bitrate)
	 *   - file-mime
	 *   - major_mime
	 *   - minor_mime
	 *
	 * @return array
	 * @since 1.28
	 */
	public function newPlaceholderProps() {
		return FSFile::placeholderProps() + [
			'metadata' => [],
			'width' => 0,
			'height' => 0,
			'bits' => 0,
			'media_type' => MEDIATYPE_UNKNOWN
		];
	}
}
