<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view 
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Forms\Validators\Files\Validations;

/**
 * Responsibility: 
 */
trait NameAndExtension {

	/**
	 * Sanitize safe file name and sanitize max. file name length
	 * and add file extension info `$file` `\stdClass` collection.
	 * @param \stdClass & $file
	 * @return void
	 */
	protected function validateNameAndExtension (& $file) {
		// Sanitize safe file name:
		$allowedFileNameCharsPattern = '#[^' 
			. addcslashes($this->allowedFileNameChars, "#[](){}<>?!=^$.+|:") 
		. ']#';
		$file->name = preg_replace(
			$allowedFileNameCharsPattern, '', $file->name
		);
		$file->name = str_replace('..', '', $file->name);
		// Sanitize max. file name length:
		$pathInfo = pathinfo($file->name);
		$file->name = $pathInfo['basename'];
		$extension = mb_strtolower($pathInfo['extension']);
		$file->extension = $extension;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$winReservedFileNames = explode(',', static::WIN_RESERVED_FILENAMES);
			if (in_array(mb_strtoupper($file->name), $winReservedFileNames, TRUE)) 
				return $this->handleUploadError(
					static::UPLOAD_ERR_RESERVED_NAME, [$file->name]
				);
			if (in_array(mb_strtoupper($pathInfo['filename']), $winReservedFileNames, TRUE)) 
				return $this->handleUploadError(
					static::UPLOAD_ERR_RESERVED_NAME, [$pathInfo['filename']]
				);
		}
		if (mb_strlen($file->name) > 255) {
			$extensionLength = mb_strlen($extension);
			if ($extensionLength > 0) {
				$fileName = basename($file->name, '.' . $extension);
				$file->name = mb_substr($fileName, 0, 255 - 1 - $extensionLength) . '.' . $extension;
			} else {
				$file->name = mb_substr($file->name, 0, 255);
			}
		}
	}
}
