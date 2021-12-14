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
 * @mixin \MvcCore\Ext\Forms\Validators\Files
 */
trait ConfigProps {

	/**
	 * Custom handler to sanitize uploaded file name characters.
	 * This handler has priority before property `allowedFileNameChars`,
	 * so if the handler is defined, processing by allowed file name 
	 * characters is not executed.
	 * It's necessary to define callable with first argument 
	 * to be raw uploaded file name string and result to be 
	 * sanitized file name string. String URI decoding, double dots
	 * or special system characters removing, special system file 
	 * names and other cases is not necessary to handle, those
	 * validations are processed after this custom handler.
	 * @var callable|NULL
	 */
	protected $allowedFileNameCharsHandler = NULL;

	/**
	 * Allowed file name characters and characters groups for submit regular expression.
	 * Custom handler in property `allowedFileNameCharsHandler` has priority before 
	 * this, so if the handler is defined, processing by allowed file name 
	 * characters is not executed.
	 * All regular expression special characters will be automatically escaped by 
	 * `addcslashes()` function to create proper regular expression pattern 
	 * to keep only characters and characters groups presented in this variable. 
	 * If there are not defined any characters, there is used in submit filename 
	 * sanitization PHP constant: `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @var string|NULL
	 */
	protected $allowedFileNameChars = NULL;

	/**
	 * Maximum number of allowed files count inside 
	 * single uploaded archive file. If uploaded archive 
	 * has more files inside than this number, it's 
	 * proclaimed as archive bomb and it's not uploaded.
	 * Default value is `1000`.
	 * @var int
	 */
	protected $archiveMaxItems = 1000;

	/**
	 * Maximum number of allowed ZIP archive levels inside.
	 * If uploaded archive contains another zip archive and
	 * those archive another and another, this is maximum
	 * level for nested ZIP archives. If Archive contains 
	 * more levels than this, it's proclaimed as archive 
	 * bomb and it's not uploaded. Default value is `3`.
	 * @var int
	 */
	protected $archiveMaxLevels = 3;

	/**
	 * Maximum archive compression percentage.
	 * If archive file has lower percentage size
	 * than all archive file items together, 
	 * it's proclaimed as archive bomb and it's 
	 * not uploaded. Default value is `5.0`.
	 * @var float
	 */
	protected $archiveMaxCompressPercentage = 5.0;

	/**
	 * PNG image maximum width or maximum height.
	 * PNG images use ZIP compression and that's why 
	 * those images could be used as ZIP bombs.
	 * This limit helps to prevent file bombs 
	 * based on PNG images.
	 * @var int
	 */
	protected $pngImageMaxWidthHeight = 10000;

	/**
	 * Bomb scanner classes to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * @var \string[]|NULL
	 */
	protected $bombScanners = NULL;

}