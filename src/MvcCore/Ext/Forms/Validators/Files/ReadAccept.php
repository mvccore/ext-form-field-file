<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
 */

namespace MvcCore\Ext\Forms\Validators\Files;

/**
 * Responsibility: Read accept configuration and prepare 
 *                 all mime types and extensions.
 * @mixin \MvcCore\Ext\Forms\Validators\Files
 */
trait ReadAccept {

	/**
	 * Read input file accept attribute value for mimetypes and extension files validation.
	 * @return bool|NULL
	 */
	protected function readAccept () {
		$acceptingExtensions = [];
		$acceptingMimeTypes = [];
		$extToolsMimesExtsClass = static::MVCCORE_EXT_TOOLS_MIMES_EXTS_CLASS;
		
		foreach ($this->accept as $rawAccept) {
			$accept = trim($rawAccept);
			if (substr($accept, 0, 1) === '.' && strlen($accept) > 1) {
				$ext = strtolower(substr($accept, 1));
				$acceptingExtensions[$ext] = 1;
			} else if (preg_match("#^([a-z-]+)/(.*)$#", $accept)) {
				// mimes from accept could have strange values like: audio/*;capture=microphone
				$semiColonPos = strpos($accept, ';');
				if ($semiColonPos !== FALSE) 
					$accept = substr($accept, 0, $semiColonPos);
				$acceptingMimeTypes[$accept] = NULL;
			} else {
				return $this->handleUploadError(
					static::UPLOAD_ERR_UNKNOWN_ACCEPT, [$rawAccept]
				);
			}
		}

		// Get possible mimetype(s) for extension(s) defined by MvcCore validators library:
		$completedMimeTypes = [];
		if ($acceptingExtensions) {
			$acceptingExtensions = array_keys($acceptingExtensions);
			foreach ($acceptingExtensions as $acceptingExtension) {
				$mimeTypesByExt = $extToolsMimesExtsClass::GetMimeTypesByExtension(
					$acceptingExtension
				);
				if ($mimeTypesByExt === NULL) {
					return $this->handleUploadError(
						static::UPLOAD_ERR_UNKNOWN_EXT, [$acceptingExtension]
					);
				} else {
					foreach ($mimeTypesByExt as $mimeTypeByExt) {
						if (isset($acceptingMimeTypes[$mimeTypeByExt])) {
							$acceptingMimeTypes[$mimeTypeByExt][] = $acceptingExtension;	
						} else {
							$acceptingMimeTypes[$mimeTypeByExt] = [$acceptingExtension];
						}
						$completedMimeTypes[$mimeTypeByExt] = 1;
					}
				}
			}
		}

		// Get for all mimetype(s) allowed file extensions:
		foreach ($acceptingMimeTypes as $acceptingMimeType => $acceptingExtension) {
			if (isset($completedMimeTypes[$acceptingMimeType])) {
				$allowedExtensions = $acceptingExtension;
			} else {
				$allowedExtensions = $extToolsMimesExtsClass::GetExtensionsByMimeType(
					$acceptingMimeType
				);
			}
			if ($allowedExtensions === NULL) {
				return $this->handleUploadError(
					static::UPLOAD_ERR_UNKNOWN_MIME, [$acceptingMimeType]
				);
			} else {
				$mimeTypeRegExp = $this->readAcceptPrepareMimeTypeRegExp(
					$acceptingMimeType
				);
				$this->mimeTypesAndExts[$acceptingMimeType] = [
					$mimeTypeRegExp, $allowedExtensions
				];
			}
		}
		
		return TRUE;
	}

	/**
	 * Prepare regular expression match pattern from mimetype string.
	 * @param  string $mimeType 
	 * @return string
	 */
	protected function readAcceptPrepareMimeTypeRegExp ($mimeType) {
		// escape all regular expression special characters, 
		// which could be inside correct mimetype string except `*`:
		$mimeType = addcslashes(trim($mimeType), "-.+");
		return '#^' . str_replace('*', '(.*)', $mimeType) . '$#';
	}
}
