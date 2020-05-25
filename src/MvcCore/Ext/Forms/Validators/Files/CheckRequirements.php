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

namespace MvcCore\Ext\Forms\Validators\Files;

/**
 * Responsibility: Check required functions and extensions installed.
 */
trait CheckRequirements
{
	/**
	 * Check installed extensions for upload validation.
	 * @return boolean|NULL
	 */
	protected function checkRequiremets () {
		// Check if `finfo_file()` function exists. File info extension is 
		// presented from PHP 5.3+ by default, so this error probably never happened.
		if (!function_exists('finfo_file')) 
			return $this->handleUploadError(
				static::UPLOAD_ERR_NO_FILEINFO
			);
		
		// Check if mimetypes and extensions validator class
		$extToolsMimesExtsClass = static::MVCCORE_EXT_TOOLS_MIMES_EXTS_CLASS;
		if (!class_exists($extToolsMimesExtsClass)) 
			return $this->handleUploadError(
				static::UPLOAD_ERR_NO_MIMES_EXT
			);

		// Complete uploaded files temporary directory:
		$this->GetUploadsTmpDir();
		
		return TRUE;
	}
}
