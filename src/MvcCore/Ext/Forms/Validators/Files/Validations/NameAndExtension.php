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

namespace MvcCore\Ext\Forms\Validators\Files\Validations;

/**
 * Responsibility: Validate file name and extension.
 * @mixin \MvcCore\Ext\Forms\Validators\Files
 */
trait NameAndExtension {

	/**
	 * Sanitize safe file name and sanitize max. file name length
	 * and add file extension info `$file` `\stdClass` collection.
	 * @param  \stdClass & $file
	 * @return void
	 */
	protected function validateNameAndExtension (& $file) {
		// Decode URI component if necessary:
		while (preg_match("#%([0-9a-zA-Z]{2})#", $file->name))
			$file->name = rawurldecode($file->name);
		$file->name = str_replace('%', '%25', $file->name);
		// Parse file name:
		$pathInfo = pathinfo($file->name);
		$file->extension = mb_strtolower($pathInfo['extension']); // extension only
		$fileNameOnly = $pathInfo['filename']; // file name only
		// Sanitize file name without extension:
		if ($this->allowedFileNameCharsHandler !== NULL) {
			// Sanitize file name without extension by custom handler:
			$fileNameOnly = call_user_func_array(
				$this->allowedFileNameCharsHandler,
				[$fileNameOnly]
			);
		} else {
			// Sanitize file name without extension by allowed characters only:
			if ($this->allowedFileNameChars === NULL)
				$this->allowedFileNameChars = static::ALLOWED_FILE_NAME_CHARS_DEFAULT;
			$allowedFileNameCharsPattern = '#[^' 
				. addcslashes($this->allowedFileNameChars, "#^$[](){}!+=") 
			. ']#';
			$fileNameOnly = preg_replace(
				$allowedFileNameCharsPattern, '', $fileNameOnly
			);
		}
		// Remove special ASCII chars from 00-31 and 127, @see https://www.asciitable.com/:
		$fileNameOnly = preg_replace('#[\x00-\x1F\x7F]#u', '', $fileNameOnly);
		// Remove Windows And Linux special characters for files and directories:
		$fileNameOnly = preg_replace('#[\<\>\:"/\\\|\?\*\&]#', '', $fileNameOnly);
		// Validate file extension:
		$allowedFileExtCharsPattern = '#[^' 
			. addcslashes(static::ALLOWED_FILE_EXTENSION_CHARS, "#$!+") 
		. ']#';
		$file->extension = preg_replace(
			$allowedFileExtCharsPattern, '', $file->extension
		);
		// Complete file name and extension:
		$file->name = $fileNameOnly . '.' . $file->extension;
		// Sanitize double dots:
		$file->name = str_replace('..', '', $file->name);
		// Sanitize min. file name length:
		if (mb_strlen($fileNameOnly) === 0)
			return $this->handleUploadError(static::UPLOAD_ERR_NO_NAME);
		// Sanitize web file name special cases:
		$webReservedFileNames = explode('|', static::WEB_RESERVED_FILENAMES);
		if (count($webReservedFileNames) > 0 && in_array(mb_strtolower($file->name), $webReservedFileNames, TRUE)) 
			return $this->handleUploadError(
				static::UPLOAD_ERR_RESERVED_NAME, [$file->name]
			);
		// Sanitize possible `./` substrings, if there is used custom filename handle:
		if (mb_strpos($file->name, './') !== FALSE)
			return $this->handleUploadError(
				static::UPLOAD_ERR_RESERVED_NAME, [$file->name]
			);
		// Sanitize Windows file name special cases:
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$winReservedFileNames = explode('|', static::WIN_RESERVED_FILENAMES);
			if (count($winReservedFileNames) > 0) {
				if (in_array(mb_strtoupper($file->name), $winReservedFileNames, TRUE)) 
					return $this->handleUploadError(
						static::UPLOAD_ERR_RESERVED_NAME, [$file->name]
					);
				if (in_array(mb_strtoupper($fileNameOnly), $winReservedFileNames, TRUE)) 
					return $this->handleUploadError(
						static::UPLOAD_ERR_RESERVED_NAME, [$fileNameOnly]
					);
			}
		}
		// Sanitize max. file name length:
		if (mb_strlen($file->name) > 255) {
			$extensionLength = mb_strlen($file->extension);
			if ($extensionLength > 0) {
				$fileName = basename($file->name, '.' . $file->extension);
				$file->name = mb_substr($fileName, 0, 255 - 1 - $extensionLength) . '.' . $extension;
			} else {
				$file->name = mb_substr($file->name, 0, 255);
			}
		}
	}
}
