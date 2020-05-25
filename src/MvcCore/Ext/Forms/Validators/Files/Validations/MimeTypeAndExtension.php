<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view 
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Forms\Validators\Files\Validations;

/**
 * Responsibility: 
 */
trait MimeTypeAndExtension
{
	/**
	 * Validate file by allowed mime type if any mime type defined 
	 * by `finfo_file()` and by allowed file extension.
	 * @param \stdClass & $file
	 * @return bool|NULL
	 */
	protected function validateMimeTypeAndExtension (& $file) {
		$allowed = FALSE;
		$finfo = finfo_open(FILEINFO_MIME);
		$fileRealMimeType = @finfo_file($finfo, $file->tmpFullPath);
		$semicolonPos = strpos($fileRealMimeType, ';');
		if ($semicolonPos !== FALSE) 
			$fileRealMimeType = substr($fileRealMimeType, 0, $semicolonPos);
		finfo_close($finfo);
		if ($this->mimeTypesAndExts) {
			foreach ($this->mimeTypesAndExts as $mimeTypeAndExtensions) {
				list(
					$mimeTypeRegExpPattern, $allowedFileExtensions
				) = $mimeTypeAndExtensions;
				if (preg_match($mimeTypeRegExpPattern, $fileRealMimeType)) {
					$mimeTypeCouldHaveGivenExtension = in_array(
						$file->extension, $allowedFileExtensions, TRUE
					);
					if ($mimeTypeCouldHaveGivenExtension) {
						$file->type = $fileRealMimeType;
						$allowed = TRUE;
						break;
					}
				}
			}
		}
		if (!$allowed) 
			return $this->handleUploadError(
				static::UPLOAD_ERR_NOT_ACCEPTED, [$file->name]
			);
		return TRUE;
	}

}
