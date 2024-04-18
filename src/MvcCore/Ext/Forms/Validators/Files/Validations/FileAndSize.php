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
 * Responsibility: Validate file and size.
 * @mixin \MvcCore\Ext\Forms\Validators\Files
 */
trait FileAndSize {

	/**
	 * Check file by `is_uploaded_file()` and `is_file()`,
	 * check by `filesize()` and check min. and max. sizes.
	 * @param  \stdClass & $file
	 * @return bool|NULL
	 */
	protected function validateFileAndSize (& $file) {
		if (
			$this->validateIsUploadedFile($file) &&
			$this->validateFileSize($file)
		) {
			return TRUE;
		}
		return NULL;
	}

	/**
	 * Check file by `is_uploaded_file()` and `is_file()`.
	 * @param  \stdClass & $file
	 * @return bool|NULL
	 */
	protected function validateIsUploadedFile (& $file) {
		if (!is_uploaded_file($file->tmpFullPath))
			return $this->handleUploadError(static::UPLOAD_ERR_NOT_POSTED);
		
		if (!is_file($file->tmpFullPath))
			return $this->handleUploadError(static::UPLOAD_ERR_NOT_FILE);
		
		return TRUE;
	}

	/**
	 * Check file size by PHP `filesize()` and check min. and max. sizes.
	 * @param  \stdClass & $file
	 * @return bool|NULL
	 */
	protected function validateFileSize (& $file) {
		$fileSize = filesize($file->tmpFullPath);
		if ($fileSize < 1)
			return $this->handleUploadError(static::UPLOAD_ERR_EMPTY_FILE);
		
		if ($fileSize === FALSE)
			return $this->handleUploadError(static::UPLOAD_ERR_TOO_LARGE_FILE);
		
		if ($this->minSize !== NULL && $fileSize < $this->minSize)
			return $this->handleUploadError(
				static::UPLOAD_ERR_MIN_SIZE, [
					\MvcCore\Ext\Form::ConvertBytesIntoHumanForm($this->minSize)
				]
			);
		
		if ($this->maxSize !== NULL && $fileSize > $this->maxSize)
			return $this->handleUploadError(
				static::UPLOAD_ERR_MAX_SIZE, [
					\MvcCore\Ext\Form::ConvertBytesIntoHumanForm($this->maxSize)
				]
			);

		$file->size = $fileSize;
		
		return TRUE;
	}
}
