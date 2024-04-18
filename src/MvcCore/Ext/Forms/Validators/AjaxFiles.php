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

use \MvcCore\Ext\Tools\MimeTypesExtensions;

/**
 * Responsibility: Validate everything necessary for sent files and check 
 *                 files by `accept` attribute rules by magic bytes.
 * @see http://php.net/manual/en/features.file-upload.php
 * @see http://php.net/manual/en/features.file-upload.common-pitfalls.php
 * 
 * @phpstan-type SubmittedFileItem object{"filename":string,"type":string,"content":string}
 * @phpstan-type GlobalFilesItem array{"name":string|string[],"type":string|string[],"tmp_name":string|string[],"size":int|int[],"error":int|int[]}
 * @phpstan-type DataInfo object{"dataPosition":int,"dataLength":int,"expectedSize":int}
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class		AjaxFiles 
extends		\MvcCore\Ext\Forms\Validators\Files {
	
	/**
	 * Default writing bugger size to write Base64 encoded 
	 * images from AJAX JSON body into uploads tmp dir.
	 * @var int
	 */
	const WRITE_BUFFER_SIZE_DEFAULT = 1048576; // 1 MB
	
	#region instance properties

	/**
	 * Writing bugger size to write Base64 encoded images from 
	 * AJAX JSON body into uploads tmp dir. Default is 1 MB in bytes (1024*1024).
	 * @var int
	 */
	protected $writeBufferSize = self::WRITE_BUFFER_SIZE_DEFAULT;
	
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
	 * @param  int       $writeBufferSize
	 * Writing bugger size to write Base64 encoded images from 
	 * AJAX JSON body into uploads tmp dir. Default is 1 MB in bytes (1024*1024).
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
		array $bombScanners = [],
		$writeBufferSize = NULL
	) {
		$this->consolidateCfg($cfg, func_get_args(), func_num_args());
		parent::__construct($cfg);
	}

	/**
	 * Get writing bugger size to write Base64 encoded images from 
	 * AJAX JSON body into uploads tmp dir. Default is 1 MB in bytes (1024*1024).
	 * @return int
	 */
	public function GetWriteBufferSize () {
		return $this->writeBufferSize;
	}

	/**
	 * Set writing bugger size to write Base64 encoded images from 
	 * AJAX JSON body into uploads tmp dir. Default is 1 MB in bytes (1024*1024).
	 * @param  int $writeBufferSize 
	 * @return \MvcCore\Ext\Forms\Validators\AjaxFiles
	 */
	public function SetWriteBufferSize ($writeBufferSize): static {
		$this->writeBufferSize = $writeBufferSize;
		return $this;
	}
	
	#endregion
	
	#region protected instance methods

	/**
	 * Complete submit data (usually from PHP superglobal varialbe `$_FILES`).
	 * @return array<string,string|array<int,string>>
	 */
	protected function completeSubmitData () {
		$fieldName = $this->field->GetName();
		/** @var SubmittedFileItem|SubmittedFileItem[] $rawParam */
		$rawParam = $this->form->GetRequest()->GetParam($fieldName, FALSE);
		$data = [];
		if ($this->multiple) {
			if (is_array($rawParam)) {
				foreach ($rawParam as $rawItem)
					if (is_object($rawItem))
						$this->completeSubmitItem($rawItem, $data);
			}
		} else {
			if (is_object($rawParam))
				$this->completeSubmitItem($rawParam, $data);
		}
		return $data;
	}

	/**
	 * Complete virtual `$_FILES` array item into `$data` by submitted values
	 * and write submitted Base64 encoded file into uploads tmp dir.
	 * @param  SubmittedFileItem $rawItem 
	 * @param  GlobalFilesItem   $data 
	 * @return void
	 */
	protected function completeSubmitItem (\stdClass $rawItem, array & $data) {
		$field = $this->field;
		$name = $rawItem->{$field::AJAX_OBJECT_FIELD_FILENAME};
		$type = $rawItem->{$field::AJAX_OBJECT_FIELD_TYPE};
		$base64Content = & $rawItem->{$field::AJAX_OBJECT_FIELD_CONTENT};
		if ($this->multiple) {
			if (!isset($data['name'])) $data['name'] = [];
			if (!isset($data['type'])) $data['type'] = [];
			if (!isset($data['tmp_name'])) $data['tmp_name'] = [];
			if (!isset($data['size'])) $data['size'] = [];
			if (!isset($data['error'])) $data['error'] = [];

			$data['name'][] = $name;
			$data['type'][] = $type;
			list ($tmpFullPath, $fileSize, $error) = $this->completeSubmitFile(
				$base64Content, $type
			);
			$data['tmp_name'][] = $tmpFullPath;
			$data['size'][] = $fileSize;
			$data['error'][] = $error;

		} else {
			$data['name'] = $name;
			$data['type'] = $type;
			list ($tmpFullPath, $fileSize, $error) = $this->completeSubmitFile(
				$base64Content, $type
			);
			$data['tmp_name'] = $tmpFullPath;
			$data['size'] = $fileSize;
			$data['error'] = $error;
		}
	}

	/**
	 * Validate submitted Base64 encoded file and write it into uploads tmp dir.
	 * @param  string $base64Content 
	 * @param  string $mimeType 
	 * @return array{"0":bool,"1":int,"2":string}
	 */
	protected function completeSubmitFile (& $base64Content, $mimeType) {
		$tmpFullPath = '';
		$fileSize = 0;
		$error = UPLOAD_ERR_OK;
		
		/** @var DataInfo $dataInfo */
		$dataInfo = (object) [
			'dataPosition'	=> 0,
			'dataLength'	=> 0,
			'expectedSize'	=> 0,
		];

		if ($base64Content === NULL || !is_string($base64Content)) {
			$error = UPLOAD_ERR_NO_FILE;
		} else {
			// data:.../...;base64,...
			if (preg_match('/^data\:([a-zA-Z]+\/[a-zA-Z]+);base64\,([a-zA-Z0-9\+\/]+\=*)$/', $base64Content)) {
				$dataInfo = $this->completeSubmitFileDataInfo($base64Content);
			} else {
				$error = UPLOAD_ERR_NO_FILE;
			}
		}
		if ($error === UPLOAD_ERR_NO_FILE)
			return [$tmpFullPath, $fileSize, $error];

		$uploadsTmpDir = $this->GetUploadsTmpDir();
		if (!is_dir($uploadsTmpDir)) {
			$error = UPLOAD_ERR_NO_TMP_DIR;
			return [$tmpFullPath, $fileSize, $error];
		}

		list ($tmpFullPath, $error) = $this->completeSubmitFileWtite2Tmp($base64Content, $mimeType, $dataInfo);
		if ($error !== 0)
			return [$tmpFullPath, $fileSize, $error];

		$fileSize = filesize($tmpFullPath);
		
		$maxIniFileSize = $this->form->GetPhpIniSizeLimit("upload_max_filesize");
		if ($maxIniFileSize !== NULL && $fileSize > $maxIniFileSize) {
			$error = UPLOAD_ERR_INI_SIZE;
			return [$tmpFullPath, $fileSize, $error];
		}
				
		if ($dataInfo->expectedSize > 0 && $dataInfo->expectedSize > $fileSize) {
			$error = UPLOAD_ERR_PARTIAL;
			return [$tmpFullPath, $fileSize, $error];
		}

		return [$tmpFullPath, $fileSize, $error];
	}
	
	/**
	 * Compute from Base64 encoded data:
	 * - data start position
	 * - data length without Base64 head
	 * - expected file size
	 * @param  string $base64Content 
	 * @return DataInfo
	 */
	protected function completeSubmitFileDataInfo (& $base64Content) {
		$dataPos = strpos($base64Content, ';base64,') + 8;
		$dataLen = strlen($base64Content);
		$dataLenNoEq = $dataLen;
		for ($i = $dataLen - 1; $i >= $dataPos; $i--) {
			$char = substr($base64Content, $i, 1);
			if ($char === '=') {
				$dataLenNoEq--;
			} else {
				break;
			}
		}
		$dataLenNoEq -= $dataPos;
		$expectedSize = intval(($dataLenNoEq * 3) / 4);

		return (object) [
			'dataPosition'	=> $dataPos,
			'dataLength'	=> $dataLen,
			'expectedSize'	=> $expectedSize,
		];
	}

	/**
	 * Write Base64 encoded file into uploads tmp dir and 
	 * return tmp full path and possible error integer.
	 * @param  string   $base64Content 
	 * @param  string   $mimeType 
	 * @param  DataInfo $dataInfo 
	 * @return array{"0":int,"1":string}
	 */
	protected function completeSubmitFileWtite2Tmp (& $base64Content, $mimeType, \stdClass $dataInfo) {
		$tmpFullPath = '';
		$error = 0;

		$uploadsTmpDir = $this->GetUploadsTmpDir();
		if (!is_dir($uploadsTmpDir)) {
			$error = UPLOAD_ERR_NO_TMP_DIR;
			return [$tmpFullPath, $error];
		}

		$uniqueId = str_replace('.', '', uniqid('', TRUE));
		$tmpFullPath = $uploadsTmpDir . '/upload_' . $uniqueId;
		
		$exts = MimeTypesExtensions::GetExtensionsByMimeType($mimeType);
		if ($exts !== NULL || (is_array($exts) && count($exts) > 0 && $exts[0] !== '')) {
			$tmpFullPath .= '.' . $exts[0];
		}

		try {
			$fh = fopen($tmpFullPath, 'wb');
			stream_filter_append($fh, 'convert.base64-decode');
			/** @var DataInfo $dataInfo */
			$pos = $dataInfo->dataPosition;
			$len = $dataInfo->dataLength;
			$bufferSize = is_int($this->writeBufferSize) && $this->writeBufferSize > 0
				? $this->writeBufferSize
				: self::WRITE_BUFFER_SIZE_DEFAULT;
			while ($pos < $len) {
				$base64Part = substr($base64Content, $pos, $bufferSize);
				fwrite($fh, $base64Part);
				$pos += $bufferSize;
			}
			fclose($fh);
		} catch (\Exception $e) {
			$error = UPLOAD_ERR_CANT_WRITE;
		}
		
		return [$tmpFullPath, $error];
	}

	/**
	 * Check file by `is_file()`.
	 * @param  \stdClass & $file
	 * @return bool|NULL
	 */
	protected function validateIsUploadedFile (& $file) {
		if (!is_file($file->tmpFullPath))
			return $this->handleUploadError(static::UPLOAD_ERR_NOT_FILE);
		return TRUE;
	}

	#endregion
	
}
