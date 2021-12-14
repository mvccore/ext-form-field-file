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

namespace MvcCore\Ext\Forms\Validators;

/**
 * Responsibility: Validate everything necessary for uploaded files and check 
 *                 files by `accept` attribute rules by magic bytes.
 * Interface for class:
 * - `\MvcCore\Ext\Forms\Validators\Files`
 * @see http://php.net/manual/en/features.file-upload.php
 * @see http://php.net/manual/en/features.file-upload.common-pitfalls.php
 */
interface IFiles {

	#region constants
	
	/**
	 * Default allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this constant. This constant is used only
	 * if there is not specified any custom characters and characters groups by method(s): 
	 * `$field->SetAllowedFileNameChars('...');` or  `$validator->SetAllowedFileNameChars('...');`.
	 * @var string
	 */
	const ALLOWED_FILE_NAME_CHARS_DEFAULT = '-a-zA-Z0-9;,.\'_@ #^$[](){}!+=';
	
	/**
	 * Allowed file extension characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this constant. 
	 * @var string
	 */
	const ALLOWED_FILE_EXTENSION_CHARS = '-a-zA-Z0-9\'_@$!+';

	/**
	 * @see https://docs.microsoft.com/en-us/windows/win32/fileio/naming-a-file
	 * @var string
	 */
	const WIN_RESERVED_FILENAMES = 'CON|PRN|AUX|NUL|COM1|COM2|COM3|COM4|COM5|COM6|COM7|COM8|COM9|LPT1|LPT2|LPT3|LPT4|LPT5|LPT6|LPT7|LPT8|LPT9';
	
	/**
	 * Extensions with web environment special meaning.
	 * @var string
	 */
	const WEB_RESERVED_FILENAMES = '.htaccess|.htpasswd|web.config|.user.ini';

	/**
	 * MvcCore extension class nam to get 
	 * mimetype by file extension and backwards.
	 * @see https://github.com/mvccore/ext-tool-mimetype-extension
	 * @var string
	 */
	const MVCCORE_EXT_TOOLS_MIMES_EXTS_CLASS = '\\MvcCore\\Ext\\Tools\\MimeTypesExtensions';
	
	const UPLOAD_ERR_MIN_FILES						=  9;
	const UPLOAD_ERR_MAX_FILES						= 10;
	const UPLOAD_ERR_NOT_POSTED						= 11; 
	const UPLOAD_ERR_NOT_FILE						= 12;
	const UPLOAD_ERR_EMPTY_FILE						= 13;
	const UPLOAD_ERR_TOO_LARGE_FILE					= 14;
	const UPLOAD_ERR_MIN_SIZE						= 15;
	const UPLOAD_ERR_MAX_SIZE						= 16;
	const UPLOAD_ERR_NO_FILEINFO					= 17;
	const UPLOAD_ERR_NO_MIMES_EXT					= 18;
	const UPLOAD_ERR_UNKNOWN_ACCEPT					= 19;
	const UPLOAD_ERR_UNKNOWN_EXT					= 20;
	const UPLOAD_ERR_UNKNOWN_MIME					= 21;
	const UPLOAD_ERR_NO_NAME						= 22;
	const UPLOAD_ERR_RESERVED_NAME					= 23;
	const UPLOAD_ERR_NOT_ACCEPTED					= 24;
	const UPLOAD_ERR_FILE_BOMB						= 25;
	const UPLOAD_ERR_FILE_BOMB_TOO_HIGH_COMPRESSION	= 26;
	const UPLOAD_ERR_FILE_BOMB_TOO_MANY_LEVELS		= 27;
	const UPLOAD_ERR_FILE_BOMB_TOO_MANY_FILES		= 28;

	#endregion
	
	
	/**
	 * Get custom handler to sanitize uploaded file name characters.
	 * This handler has priority before property `allowedFileNameChars`,
	 * so if the handler is defined, processing by allowed file name 
	 * characters is not executed.
	 * It's necessary to define callable with first argument 
	 * to be raw uploaded file name string and result to be 
	 * sanitized file name string. String URI decoding, double dots
	 * or special system characters removing, special system file 
	 * names and other cases is not necessary to handle, those
	 * validations are processed after this custom handler.
	 * @return callable|NULL
	 */
	public function GetAllowedFileNameCharsHandler ();

	/**
	 * Set custom handler to sanitize uploaded file name characters.
	 * This handler has priority before property `allowedFileNameChars`,
	 * so if the handler is defined, processing by allowed file name 
	 * characters is not executed.
	 * It's necessary to define callable with first argument 
	 * to be raw uploaded file name string and result to be 
	 * sanitized file name string. String URI decoding, double dots
	 * or special system characters removing, special system file 
	 * names and other cases is not necessary to handle, those
	 * validations are processed after this custom handler.
	 * @param  callable|NULL $allowedFileNameCharsHandler
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetAllowedFileNameCharsHandler ($allowedFileNameCharsHandler);
	
	
	/**
	 * Get allowed file name characters and characters groups for submit regular expression.
	 * Custom handler in property `allowedFileNameCharsHandler` has priority before 
	 * this, so if the handler is defined, processing by allowed file name 
	 * characters is not executed.
	 * All regular expression special characters will be automatically escaped by 
	 * `addcslashes()` function to create proper regular expression pattern 
	 * to keep only characters and characters groups presented in this variable. 
	 * If there are not defined any characters, there is used in submit filename 
	 * sanitization PHP constant: `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @return string|NULL
	 */
	public function GetAllowedFileNameChars ();

	/**
	 * Set allowed file name characters and characters groups for submit regular expression.
	 * Custom handler in property `allowedFileNameCharsHandler` has priority before 
	 * this, so if the handler is defined, processing by allowed file name 
	 * characters is not executed.
	 * All regular expression special characters will be automatically escaped by 
	 * `addcslashes()` function to create proper regular expression pattern 
	 * to keep only characters and characters groups presented in this variable. 
	 * If there are not defined any characters, there is used in submit filename 
	 * sanitization PHP constant: `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @param  string|NULL $allowedFileNameChars
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetAllowedFileNameChars ($allowedFileNameChars);


	/**
	 * Get maximum number of allowed files count inside 
	 * single uploaded archive file. If uploaded archive 
	 * has more files inside than this number, it's 
	 * proclaimed as archive bomb and it's not uploaded.
	 * Default value is `1000`.
	 * @return int
	 */
	public function GetArchiveMaxItems ();
	
	/**
	 * Set maximum number of allowed files count inside 
	 * single uploaded archive file. If uploaded archive 
	 * has more files inside than this number, it's 
	 * proclaimed as archive bomb and it's not uploaded.
	 * Default value is `1000`.
	 * @param  int $archiveMaxItems Default `1000`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxItems ($archiveMaxItems = 1000);
	

	/**
	 * Maximum number of allowed ZIP archive levels inside.
	 * If uploaded archive contains another zip archive and
	 * those archive another and another, this is maximum
	 * level for nested ZIP archives. If Archive contains 
	 * more levels than this, it's proclaimed as archive 
	 * bomb and it's not uploaded. Default value is `3`.
	 * @return int
	 */
	public function GetArchiveMaxLevels ();
	
	/**
	 * Maximum number of allowed ZIP archive levels inside.
	 * If uploaded archive contains another zip archive and
	 * those archive another and another, this is maximum
	 * level for nested ZIP archives. If Archive contains 
	 * more levels than this, it's proclaimed as archive 
	 * bomb and it's not uploaded. Default value is `3`.
	 * @param  int $archiveMaxLevels Default `3`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxLevels ($archiveMaxLevels = 3);
	

	/**
	 * Get maximum archive compression percentage.
	 * If archive file has lower percentage size
	 * than all archive file items together, 
	 * it's proclaimed as archive bomb and it's 
	 * not uploaded. v
	 * @return float
	 */
	public function GetArchiveMaxCompressPercentage ();
	
	/**
	 * Set maximum archive compression percentage.
	 * If archive file has lower percentage size
	 * than all archive file items together, 
	 * it's proclaimed as archive bomb and it's 
	 * not uploaded. Default value is `5.0`.
	 * @param  float $archiveMaxCompressPercentage Default `5.0`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxCompressPercentage ($archiveMaxCompressPercentage = 5.0);
	

	/**
	 * PNG image maximum width or maximum height.
	 * PNG images use ZIP compression and that's why 
	 * those images could be used as ZIP bombs.
	 * This limit helps to prevent file bombs 
	 * based on PNG images. Default value is `10000`.
	 * @return int
	 */
	public function GetPngImageMaxWidthHeight ();
	
	/**
	 * PNG image maximum width or maximum height.
	 * PNG images use ZIP compression and that's why 
	 * those images could be used as ZIP bombs.
	 * This limit helps to prevent file bombs 
	 * based on PNG images. Default value is `10000`.
	 * @param  int $pngImageMaxWidthHeight Default `10000`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetPngImageMaxWidthHeight ($pngImageMaxWidthHeight = 10000);
	

	/**
	 * Add bomb scanner class(es) to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * @param  \string[] $bombScannerClasses
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function AddBombScanners ();
	
	/**
	 * Get bomb scanner class(es) to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * @return \string[]|NULL
	 */
	public function GetBombScanners ();

	/**
	 * Set bomb scanner class(es) to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * @param  \string[] $bombScannerClasses
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetBombScanners ();


	/**
	 * Complete uploaded files temporary directory.
	 * @return string
	 */
	public function GetUploadsTmpDir ();
}
