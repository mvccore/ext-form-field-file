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
 * @see http://php.net/manual/en/features.file-upload.php
 * @see http://php.net/manual/en/features.file-upload.common-pitfalls.php
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class		Files 
extends		\MvcCore\Ext\Forms\Validator
implements	\MvcCore\Ext\Forms\IValidator,
			\MvcCore\Ext\Forms\Fields\IMultiple,
			\MvcCore\Ext\Forms\Validators\IFiles {
	
	#region traits

	use \MvcCore\Ext\Forms\Field\Props\Multiple;
	use \MvcCore\Ext\Forms\Field\Props\File;
	
	use \MvcCore\Ext\Forms\Validators\Files\ConfigProps;
	use \MvcCore\Ext\Forms\Validators\Files\ConfigGettersSetters;
	use \MvcCore\Ext\Forms\Validators\Files\CheckRequirements;
	use \MvcCore\Ext\Forms\Validators\Files\CompleteFiles;
	use \MvcCore\Ext\Forms\Validators\Files\ReadAccept;
	use \MvcCore\Ext\Forms\Validators\Files\Validations\FileAndSize;
	use \MvcCore\Ext\Forms\Validators\Files\Validations\NameAndExtension;
	use \MvcCore\Ext\Forms\Validators\Files\Validations\MimeTypeAndExtension;
	use \MvcCore\Ext\Forms\Validators\Files\Validations\Bomb;
	
	#endregion
	
	#region static properties

	/**
	 * Validation failure message template definitions.
	 * @var array
	 */
	protected static $errorMessages = [
		UPLOAD_ERR_OK									=> "There is no error, the file uploaded with success.",						// 0
		UPLOAD_ERR_INI_SIZE								=> "Uploaded file exceeds maximum size to upload. ('{1}' bytes).",				// 1
		/** @bugfix: http://php.net/manual/en/features.file-upload.php#74692 */
		//UPLOAD_ERR_FORM_SIZE							=> "Uploaded file exceeds max. size to upload: '{1}'.",							// 2
		UPLOAD_ERR_PARTIAL								=> "Uploaded file was only partially uploaded.",								// 3
		UPLOAD_ERR_NO_FILE								=> "No file was uploaded.",														// 4
		UPLOAD_ERR_NO_TMP_DIR							=> "Missing a temporary folder for uploaded file.",								// 6
		UPLOAD_ERR_CANT_WRITE							=> "Failed to write uploaded file to disk.",									// 7
		UPLOAD_ERR_EXTENSION							=> "System extension stopped the file upload.",									// 8
		self::UPLOAD_ERR_MIN_FILES						=> "Field allows to upload '{1}' file(s) at minimum.",							// 9
		self::UPLOAD_ERR_MAX_FILES						=> "Field allows to upload '{1}' file(s) at maximum.",							// 10
		self::UPLOAD_ERR_NOT_POSTED						=> "File wasn't uploaded via HTTP POST.",										// 11
		self::UPLOAD_ERR_NOT_FILE						=> "Uploaded file is not valid file.",											// 12
		self::UPLOAD_ERR_EMPTY_FILE						=> "Uploaded file is empty.",													// 13
		self::UPLOAD_ERR_TOO_LARGE_FILE					=> "Uploaded file is too large.",												// 14
		self::UPLOAD_ERR_MIN_SIZE						=> "One of uploaded files is too small. Min. required size is '{1}'.",			// 15
		self::UPLOAD_ERR_MAX_SIZE						=> "One of uploaded files is too large. Max. allowed size is '{1}'.",			// 16
		self::UPLOAD_ERR_NO_FILEINFO					=> "System extension for files recognition is missing.",						// 17
		self::UPLOAD_ERR_NO_MIMES_EXT					=> "System extension `{1}` for mime type(s) and extensions is missing.",		// 18
		self::UPLOAD_ERR_UNKNOWN_ACCEPT					=> "Unknown accept attribute value found: '{1}'.",								// 19
		self::UPLOAD_ERR_UNKNOWN_EXT					=> "Unknown file mimetype found for accept file extension: '{1}'.",				// 20
		self::UPLOAD_ERR_UNKNOWN_MIME					=> "Unknown file extension found for accept file mimetype: '{1}'.",				// 21
		self::UPLOAD_ERR_NO_NAME						=> "Uploaded file name is empty, it maybe contains only disallowed characters.",// 22
		self::UPLOAD_ERR_RESERVED_NAME					=> "Uploaded file name has system reserved name ('{1}').",						// 23
		self::UPLOAD_ERR_NOT_ACCEPTED					=> "Uploaded file is not in the expected file format ('{1}').",					// 24
		self::UPLOAD_ERR_FILE_BOMB						=> "Uploaded file has been evaluated as a potential file bomb ('{1}').",		// 25
		self::UPLOAD_ERR_FILE_BOMB_TOO_HIGH_COMPRESSION	=> "Uploaded file has to high compression.",									// 26
		self::UPLOAD_ERR_FILE_BOMB_TOO_MANY_LEVELS		=> "Uploaded file archive has to many archive levels.",							// 27
		self::UPLOAD_ERR_FILE_BOMB_TOO_MANY_FILES		=> "Uploaded file archive has to many files inside.",							// 28
	];

	/**
	 * Field specific values (camel case) and their validator default values.
	 * @var array
	 */
	protected static $fieldSpecificProperties = [
		'multiple'						=> NULL,
		'accept'						=> NULL,
		'minCount'						=> NULL,
		'maxCount'						=> NULL,
		'minSize'						=> NULL,
		'maxSize'						=> NULL,
	];
	
	#endregion

	#region instance properties

	/**
	 * Uploaded files collection completed from request object from global `$_FILES` array.
	 * Every item in array is `\stdClass` object with following records:
	 * - `name`        - string from `$_FILES['name']`, sanitized by `basename()`, by max. length and by allowed characters.
	 * - `type`        - string from `$_FILES['type']`, checked by `finfo` PHP extension and allowed file extensions for mime type.
	 * - `tmpFullPath` - string from `$_FILES['tmp_name']`, checked by `is_uploaded_file()`.
	 * - `error`       - int from `$_FILES['error']`, always `0` in success upload.
	 * - `size`        - int from `$_FILES['size']`, checked by `filesize()`,
	 * - `extension`   - lower case file extension parsed by `pathinfo()` from sanitized `name` record.
	 * @var \stdClass[]
	 */
	protected $files = [];

	/**
	 * Array with string mimetypes keys and values as arrays with string extensions.
	 * @var array
	 */
	protected $mimeTypesAndExts = [];

	/**
	 * Uploaded files temporary directory, completed 
	 * in method: `$this->checkRequiremets();`.
	 * @var string|NULL
	 */
	protected $uploadsTmpDir = NULL;

	#endregion
	
	#region public instance methods

	/**
	 * Create files validator instance.
	 * 
	 * @param  array     $cfg
	 * Config array with protected properties and it's 
	 * values which you want to configure, presented 
	 * in camel case properties names syntax.
	 * 
	 * @param  string    $allowedFileNameCharsHandler
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
	 * @param  string    $allowedFileNameChars
	 * Allowed file name characters and characters groups for submit regular expression.
	 * Custom handler in property `allowedFileNameCharsHandler` has priority before 
	 * this, so if the handler is defined, processing by allowed file name 
	 * characters is not executed.
	 * All regular expression special characters will be automatically escaped by 
	 * `addcslashes()` function to create proper regular expression pattern 
	 * to keep only characters and characters groups presented in this variable. 
	 * If there are not defined any characters, there is used in submit filename 
	 * sanitization PHP constant: `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * 
	 * @param  int       $archiveMaxItems
	 * Maximum number of allowed files count inside 
	 * single uploaded archive file. If uploaded archive 
	 * has more files inside than this number, it's 
	 * proclaimed as archive bomb and it's not uploaded.
	 * Default value is `1000`.
	 * @param  int       $archiveMaxLevels
	 * Maximum number of allowed ZIP archive levels inside.
	 * If uploaded archive contains another zip archive and
	 * those archive another and another, this is maximum
	 * level for nested ZIP archives. If Archive contains 
	 * more levels than this, it's proclaimed as archive 
	 * bomb and it's not uploaded. Default value is `3`.
	 * @param  float     $archiveMaxCompressPercentage
	 * Maximum archive compression percentage.
	 * If archive file has lower percentage size
	 * than all archive file items together, 
	 * it's proclaimed as archive bomb and it's 
	 * not uploaded. Default value is `10000`.
	 * @param  int       $pngImageMaxWidthHeight
	 * PNG image maximum width or maximum height.
	 * PNG images use ZIP compression and that's why 
	 * those images could be used as ZIP bombs.
	 * This limit helps to prevent file bombs 
	 * based on PNG images.
	 * @param  \string[] $bombScanners
	 * Bomb scanner classes to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * 
	 * @throws \InvalidArgumentException 
	 * @return void
	 */
	public function __construct(
		array $cfg = [],
		$allowedFileNameCharsHandler = NULL,
		$allowedFileNameChars = NULL,
		$archiveMaxItems = NULL,
		$archiveMaxLevels = NULL,
		$archiveMaxCompressPercentage = NULL,
		$pngImageMaxWidthHeight = NULL,
		array $bombScanners = []
	) {
		$this->consolidateCfg($cfg, func_get_args(), func_num_args());
		parent::__construct($cfg);
	}

	/**
	 * Complete uploaded files temporary directory.
	 * @return string
	 */
	public function GetUploadsTmpDir () {
		if ($this->uploadsTmpDir === NULL) {
			$this->uploadsTmpDir = @ini_get("upload_tmp_dir");
			if (!$this->uploadsTmpDir) {
				$app = $this->form->GetApplication();
				$toolClass = $app->GetToolClass();
				$this->uploadsTmpDir = $toolClass::GetSystemTmpDir();
			}
		}
		return $this->uploadsTmpDir;
	}

	/**
	 * Validate `$_FILES` array items stored in request object. Check if file is valid
	 * uploaded file, sanitize file name and check file mimetype by `finfo` extension by accept attribute values.
	 * Return `NULL` for failure or success result as array with `\stdClass`(es) for each file.
	 * @param  string|array     $rawSubmittedValue Raw user input - for this validator always `NULL`.
	 * @return \stdClass[]|NULL Safe submitted files array or `NULL` if not possible to return safe value.
	 */
	public function Validate ($rawSubmittedValue) {
		
		// 1. Check installed extensions for upload validation.
		if (!$this->checkRequiremets())
			return NULL;
		
		// 2. Complete files array from global `$_FILES` stored in request object:
		if (!$this->completeFiles()) 
			return NULL;

		// 3. Prepare all accept mimetype regular expressions for `finfo_file()` function result.
		if (!$this->readAccept()) 
			return NULL;

		foreach ($this->files as $file) {
			// 4. Check errors completed by PHP:
			if ($file->error !== 0) 
				return $this->handleUploadError($file->error);

			// 5. Check file by `is_uploaded_file()`, `is_file()` and by `filesize()`:
			if (!$this->validateFileAndSize($file)) 
				return NULL;

			// 6. Sanitize safe file name and sanitize max. file name length:
			$this->validateNameAndExtension($file);

			// 7. Validate file by allowed mime type if any mime type defined by `finfo_file()`:
			if (!$this->validateMimeTypeAndExtension($file)) 
				return NULL;

			// 8. Try to detect archive bomb if necessary:
			if (!$this->validateBomb($file)) 
				return NULL;
		}
		
		return $this->files;
	}

	#endregion
	
	#region protected instance methods

	/**
	 * Add error message arguments for specific PHP build-in errors,
	 * add error message into form session namespace, remove all tmp files and return NULL.
	 * @see http://php.net/manual/en/features.file-upload.php
	 * @see http://php.net/manual/en/features.file-upload.common-pitfalls.php
	 * @param  int   $errorNumber
	 * @param  array $errorMsgArgs
	 * @return NULL
	 */
	protected function handleUploadError ($errorNumber, $errorMsgArgs = []) {
		if ($errorNumber === UPLOAD_ERR_INI_SIZE) {
			$form = $this->form;
			// `post_max_size` is always handled at submit process begin.
			$errorMsgArgs = [
				$form->GetPhpIniSizeLimit('upload_max_filesize')
			];
		} else if ($errorNumber === self::UPLOAD_ERR_NO_MIMES_EXT) {
			$errorMsgArgs = [self::MVCCORE_EXT_TOOLS_MIMES_EXTS_PKG];
		}
		$this->field->AddValidationError(
			static::GetErrorMessage((int) $errorNumber), 
			$errorMsgArgs
		);
		return $this->removeAllTmpFiles();
	}

	/**
	 * Remove all currently uploaded files from PHP temporary directory and return `NULL`.
	 * @return NULL
	 */
	protected function removeAllTmpFiles () {
		foreach ($this->files as & $file) {
			if (file_exists($file->tmpFullPath)) 
				@unlink($file->tmpFullPath);
		}
		return NULL;
	}
	
	#endregion
}
