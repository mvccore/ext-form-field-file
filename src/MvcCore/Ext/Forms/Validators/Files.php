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

namespace MvcCore\Ext\Forms\Validators;

/**
 * Responsibility: Validate everything necessary for uploaded files and check 
 *				   files by `accept` attribute rules by magic bytes.
 * @see http://php.net/manual/en/features.file-upload.php
 * @see http://php.net/manual/en/features.file-upload.common-pitfalls.php
 */
class Files 
	extends		\MvcCore\Ext\Forms\Validator
	implements	\MvcCore\Ext\Forms\IValidator,
				\MvcCore\Ext\Forms\Fields\IMultiple,
				\MvcCore\Ext\Forms\Fields\IFiles
{
	use \MvcCore\Ext\Forms\Field\Props\Multiple;
	use \MvcCore\Ext\Forms\Field\Props\Files;

	const UPLOAD_ERR_MIN_FILES		=  9;
	const UPLOAD_ERR_MAX_FILES		= 10;
	const UPLOAD_ERR_NOT_POSTED		= 11; 
	const UPLOAD_ERR_NOT_FILE		= 12;
	const UPLOAD_ERR_EMPTY_FILE		= 13;
	const UPLOAD_ERR_TOO_LARGE_FILE	= 14;
	const UPLOAD_ERR_MIN_SIZE		= 15;
	const UPLOAD_ERR_MAX_SIZE		= 16;
	const UPLOAD_ERR_NO_FILEINFO	= 17;						
	const UPLOAD_ERR_NO_MIMES_EXTS	= 18;
	const UPLOAD_ERR_UNKNOWN_ACCEPT	= 19;
	const UPLOAD_ERR_UNKNOWN_EXT	= 20;
	const UPLOAD_ERR_UNKNOWN_MIME	= 21;
	const UPLOAD_ERR_NOT_ACCEPTED	= 22;

	/**
	 * Validation failure message template definitions.
	 * @var array
	 */
	protected static $errorMessages = [
		UPLOAD_ERR_OK					=> "There is no error, the file uploaded with success.",				// 0
		UPLOAD_ERR_INI_SIZE				=> "The uploaded file exceeds maximum size to upload. (`{1}` bytes).",	// 1
		/** @bugfix: http://php.net/manual/en/features.file-upload.php#74692 */
		//UPLOAD_ERR_FORM_SIZE			=> "The uploaded file exceeds max. size to upload: `{1}`.",				// 2
		UPLOAD_ERR_PARTIAL				=> "The uploaded file was only partially uploaded.",					// 3
		UPLOAD_ERR_NO_FILE				=> "No file was uploaded.",												// 4
		UPLOAD_ERR_NO_TMP_DIR			=> "Missing a temporary folder for uploaded file.",						// 6
		UPLOAD_ERR_CANT_WRITE			=> "Failed to write uploaded file to disk.",							// 7
		UPLOAD_ERR_EXTENSION			=> "A PHP extension stopped the file upload.",							// 8
		self::UPLOAD_ERR_MIN_FILES		=> "Field allows to upload `{1}` file(s) at minimum.",					// 9
		self::UPLOAD_ERR_MAX_FILES		=> "Field allows to upload `{1}` file(s) at maximum.",					// 10
		self::UPLOAD_ERR_NOT_POSTED		=> "The file wasn't uploaded via HTTP POST.",							// 11
		self::UPLOAD_ERR_NOT_FILE		=> "The uploaded file is not valid file.",								// 12
		self::UPLOAD_ERR_EMPTY_FILE		=> "The uploaded file is empty.",										// 13
		self::UPLOAD_ERR_TOO_LARGE_FILE	=> "The uploaded file is too large.",									// 14
		self::UPLOAD_ERR_MIN_SIZE		=> "One of uploaded files is too small. Min. required size is `{1}`.",	// 15
		self::UPLOAD_ERR_MAX_SIZE		=> "One of uploaded files is too large. Max. allowed size is `{1}`.",	// 16
		self::UPLOAD_ERR_NO_FILEINFO	=> "A PHP function for magic bytes "
											. "recognition is missing (`finfo`).",								// 17
		self::UPLOAD_ERR_NO_MIMES_EXTS	=> "MvcCore extension library to get mimetype(s) by "
											. "file extension and backwards is not "
											. "installed (`mvccore/ext-tool-mimetype-extension`).",				// 18
		self::UPLOAD_ERR_UNKNOWN_ACCEPT	=> "Unknown accept attribute value found: `{1}`.",						// 19
		self::UPLOAD_ERR_UNKNOWN_EXT	=> "Unknown file mimetype found for accept file extension: `{1}`.",		// 20
		self::UPLOAD_ERR_UNKNOWN_MIME	=> "Unknown file extension found for accept file mimetype: `{1}`.",		// 21
		self::UPLOAD_ERR_NOT_ACCEPTED	=> "The uploaded file is not in the expected file format (`{1}`).",		// 22

	];

	/**
	 * Field specific values (camel case) and their validator default values.
	 * @var array
	 */
	protected static $fieldSpecificProperties = [
		'multiple'				=> NULL,
		'accept'				=> NULL,
		'allowedFileNameChars'	=> \MvcCore\Ext\Forms\Fields\File::ALLOWED_FILE_NAME_CHARS_DEFAULT,
		'minCount'				=> NULL,
		'maxCount'				=> NULL,
		'minSize'				=> NULL,
		'maxSize'				=> NULL,
	];

	/**
	 * Uploaded files collection completed from request object from global `$_FILES` array.
	 * Every item in array is `\stdClass` object with following records:
	 * - `name`			- string from `$_FILES['name']`, sanitized by `basename()`, by max. length and by allowed characters.
	 * - `type`			- string from `$_FILES['type']`, checked by `finfo` PHP extension and allowed file extensions for mime type.
	 * - `tmpFullPath`	- string from `$_FILES['tmp_name']`, checked by `is_uploaded_file()`.
	 * - `error`		- int from `$_FILES['error']`, always `0` in success upload.
	 * - `size`			- int from `$_FILES['size']`, checked by `filesize()`,
	 * - `extension`	- lower case file extension parsed by `pathinfo()` from sanitized `name` record.
	 * @var \stdClass[]
	 */
	protected $files = [];

	/**
	 * Array with string mimetypes keys and values as arrays with string extensions.
	 * @var array
	 */
	protected $mimeTypesAndExts = [];

	/**
	 * Validate `$_FILES` array items stored in request object. Check if file is valid
	 * uploaded file, sanitize file name and check file mimetype by `finfo` extension by accept attribute values.
	 * Return `NULL` for failure or success result as array with `\stdClass`(es) for each file.
	 * @param string|array	$rawSubmittedValue Raw user input - for this validator always `NULL`.
	 * @return \stdClass[]|NULL	Safe submitted files array or `NULL` if not possible to return safe value.
	 */
	public function Validate ($rawSubmittedValue) {
		// 1. Complete files array from global `$_FILES` stored in request object:
		xxx($this);
		if (!$this->completeFiles()) return NULL;
		// 2. Prepare all accept mimetype regular expressions for `finfo_file()` function result.
		if (!$this->readAccept()) return NULL;
		// 3. check if `finfo_file()` function exists. File info extension is 
		// presented from PHP 5.3+ by default, so this error probably never happened.
		if (!function_exists('finfo_file')) return $this->handlePhpUploadError(self::UPLOAD_ERR_NO_FILEINFO);
		foreach ($this->files as $file) {
			// 4. Check errors completed by PHP:
			if ($file->error !== 0) return $this->handlePhpUploadError($file->error);
			// 5. Check file by `is_uploaded_file()`, `is_file()` and by `filesize()`:
			if (!$this->validateValidFileAndFilesSize($file)) return NULL;
			// 6. Sanitize safe file name and sanitize max. file name length:
			$this->validateSanitizeFileNameAndAddFileExt($file);
			// 7. Validate file by allowed mime type if any mime type defined by `finfo_file()`:
			if (!$this->validateAllowedMimeTypeAndExtension($file)) return NULL;
			
		}
		return $this->files;
	}

	/**
	 * Complete files array from global `$_FILES` stored in request object.
	 * @return bool|NULL
	 */
	protected function & completeFiles () {
		$this->files = [];
		$filesFieldItems = $this->form->GetRequest()->GetFile($this->field->GetName());
		if (!$filesFieldItems) return;
		if ($this->multiple) {
			foreach ($filesFieldItems['name'] as $index => $fileName) {
				$this->files[] = (object) [
					'name'			=> basename($fileName),
					'type'			=> $filesFieldItems['type'][$index],
					'tmpFullPath'	=> $filesFieldItems['tmp_name'][$index],
					'error'			=> $filesFieldItems['error'][$index],
					'size'			=> $filesFieldItems['size'][$index],
					//'extension' is completed later in `$this->validateSanitizeFileNameAndAddFileExt();`
				];
			}
		} else {
			$this->files[] = (object) [
				'name'			=> basename($filesFieldItems['name']),
				'type'			=> $filesFieldItems['type'],
				'tmpFullPath'	=> $filesFieldItems['tmp_name'],
				'error'			=> $filesFieldItems['error'],
				'size'			=> $filesFieldItems['size'],
				//'extension' is completed later in `$this->validateSanitizeFileNameAndAddFileExt();`
			];
		}
		$filesCount = count($this->files);
		if ($this->minCount !== NULL && $filesCount < $this->minCount) 
			return $this->handlePhpUploadError(self::UPLOAD_ERR_MIN_FILES, [$this->minCount]);
		if ($this->maxCount !== NULL && $filesCount > $this->maxCount) 
			return $this->handlePhpUploadError(self::UPLOAD_ERR_MAX_FILES, [$this->maxCount]);
		if ($filesCount > 0) 
			return TRUE;
		return $this->handlePhpUploadError(UPLOAD_ERR_NO_FILE);
	}

	/**
	 * Read input file accept attribute value for mimetypes and extension files validation.
	 * @return bool|NULL
	 */
	protected function readAccept () {
		$extensions = [];
		$mimeTypes = [];
		// Check if mimetypes and extensions validator class
		$extToolsMimesExtsClass = '\\MvcCore\\Ext\\Tools\\MimeTypesExtensions';
		if (!class_exists($extToolsMimesExtsClass)) 
			return $this->handlePhpUploadError(self::UPLOAD_ERR_NO_MIMES_EXTS);

		foreach ($this->accept as $rawAccept) {
			$accept = trim($rawAccept);
			if (substr($accept, 0, 1) === '.' && strlen($accept) > 1) {
				$ext = strtolower(substr($accept, 1));
				$extensions[$ext] = 1;
			} else if (preg_match("#^([a-z-]+)/(.*)#", $accept)) {
				// mimes from accept could have strange values like: audio/*;capture=microphone
				$semiColonPos = strpos($accept, ';');
				if ($semiColonPos !== FALSE) 
					$accept = substr($accept, 0, $semiColonPos);
				$mimeTypes[$accept] = 1;
			} else {
				return $this->handlePhpUploadError(self::UPLOAD_ERR_UNKNOWN_ACCEPT, [$rawAccept]);
			}
		}
		// Get possible mimetype(s) for extension(s) defined by MvcCore validators library:
		if ($extensions) {
			foreach ($extensions as $extension) {
				$mimeTypesByExt = $extToolsMimesExtsClass::GetMimeTypesByExtension($extension);
				if ($mimeTypesByExt === NULL) {
					return $this->handlePhpUploadError(self::UPLOAD_ERR_UNKNOWN_EXT, [$extension]);
				} else {
					foreach ($mimeTypesByExt as $mimeTypeByExt) 
						$mimeTypes[$mimeTypeByExt] = 1;
				}
			}
		}
		// Get for all mimetype(s) allowed file extensions:
		$mimeTypes = array_keys($mimeTypes);
		foreach ($mimeTypes as $mimeType) {
			$allowedExtensions = $extToolsMimesExtsClass::GetExtensionsByMimeType($mimeType);
			if ($allowedExtensions === NULL) {
				return $this->handlePhpUploadError(self::UPLOAD_ERR_UNKNOWN_MIME, [$mimeType]);
			} else {
				$mimeTypeRegExp = $this->readAcceptPrepareMimeTypeRegExp($mimeType);
				$this->mimeTypesAndExts[$mimeType] = [$mimeTypeRegExp, $allowedExtensions];
			}
		}
		return TRUE;
	}

	/**
	 * Prepare regular expression match pattern from mimetype string.
	 * @param string $mimeType 
	 * @return string
	 */
	protected function readAcceptPrepareMimeTypeRegExp ($mimeType) {
		// escape all regular expression special characters, 
		// which could be inside correct mimetype string except `*`:
		$mimeType = addcslashes(trim($mimeType), "-.+");
		return '#^' . str_replace('*', '(.*)', $mimeType) . '$#';
	}

	/**
	 * Check file by `is_uploaded_file()`, `is_file()` and by `filesize()`.
	 * @param \stdClass & $file
	 * @return bool|NULL
	 */
	protected function validateValidFileAndFilesSize (& $file) {
		if (!is_uploaded_file($file->tmpFullPath))
			return $this->handlePhpUploadError(self::UPLOAD_ERR_NOT_POSTED);
		if (!is_file($file->tmpFullPath))
			return $this->handlePhpUploadError(self::UPLOAD_ERR_NOT_FILE);
		$fileSize = filesize($file->tmpFullPath);
		if ($fileSize < 1)
			return $this->handlePhpUploadError(self::UPLOAD_ERR_EMPTY_FILE);
		if ($fileSize === FALSE)
			return $this->handlePhpUploadError(self::UPLOAD_ERR_TOO_LARGE_FILE);
		if ($this->minSize !== NULL && $fileSize < $this->minSize)
			return $this->handlePhpUploadError(
				self::UPLOAD_ERR_MIN_SIZE, [$this->getBytesInHumanForm($this->minSize)]
			);
		if ($this->maxSize !== NULL && $fileSize > $this->maxSize)
			return $this->handlePhpUploadError(
				self::UPLOAD_ERR_MAX_SIZE, [$this->getBytesInHumanForm($this->maxSize)]
			);
		$file->size = $fileSize;
		return TRUE;
	}

	/**
	 * Sanitize safe file name and sanitize max. file name length
	 * and add file extension info `$file` `\stdClass` collection.
	 * @param \stdClass & $file
	 * @return void
	 */
	protected function validateSanitizeFileNameAndAddFileExt (& $file) {
		// Sanitize safe file name:
		$allowedFileNameCharsPattern = '#[^' 
			. addcslashes($$this->allowedFileNameChars, "#[](){}<>?!=^$.+|:\\") 
		. ']#';
		$file->name = preg_replace($allowedFileNameCharsPattern, '', $file->name);
		// Sanitize max. file name length:
		$pathInfo = pathinfo($file->name);
		$extension = mb_strtolower($pathInfo['extension']);
		$file->extension = $extension;
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

	/**
	 * Validate file by allowed mime type if any mime type defined by `finfo_file()`
	 * @param \stdClass & $file
	 * @return bool|NULL
	 */
	protected function validateAllowedMimeTypeAndExtension (& $file) {
		$allowed = FALSE;
		$finfo = finfo_open(FILEINFO_MIME);
		$fileMimeType = @finfo_file($finfo, $file->tmpFullPath);
		$semicolonPos = strpos($fileMimeType, ';');
		if ($semicolonPos !== FALSE) $fileMimeType = substr($fileMimeType, 0, $semicolonPos);
		finfo_close($finfo);
		if ($this->mimeTypes) {
			foreach ($this->mimeTypes as $mimeType => $mimeTypeAndExtensions) {
				list($mimeTypeRegExpPattern, $allowedFileExtensions) = $mimeTypeAndExtensions;
				if (preg_match($mimeTypeRegExpPattern, $fileMimeType)) {
					if (in_array($file->extension, $allowedFileExtensions)) {
						$file->type = $mimeType;
						$allowed = TRUE;
						break;
					}
				}
			}
		}
		if (!$allowed) 
			return $this->handlePhpUploadError(self::UPLOAD_ERR_NOT_ACCEPTED, [$file->name]);
		return TRUE;
	}

	/**
	 * Add error message arguments for specific PHP build-in errors,
	 * add error message into form session namespace, remove all tmp files and return NULL.
	 * @see http://php.net/manual/en/features.file-upload.php
	 * @see http://php.net/manual/en/features.file-upload.common-pitfalls.php
	 * @param int   $errorNumber
	 * @param array $errorMsgArgs
	 * @return NULL
	 */
	protected function handlePhpUploadError ($errorNumber, $errorMsgArgs = []) {
		if ($errorNumber === UPLOAD_ERR_INI_SIZE) {
			$form = $this->form;
			// `post_max_size` is always handled at submit process begin.
			$errorMsgArgs = [
				$form::GetPhpIniSizeLimit('upload_max_filesize')
			];
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

	/**
	 * Converts a long string of bytes into a readable format e.g KB, MB, GB, TB, YB
	 * @param int $bytes num The number of bytes.
	 * @return string
	 */
	protected function getBytesInHumanForm ($bytes = 0) {
		$i = floor(log($bytes) / log(1024));
		$sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
	}
}
