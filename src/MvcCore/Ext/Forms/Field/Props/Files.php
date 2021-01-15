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

namespace MvcCore\Ext\Forms\Field\Props;

/**
 * Trait for classes:
 * - `\MvcCore\Ext\Forms\Fields\File`
 * - `\MvcCore\Ext\Forms\Validators\Files`
 */
trait Files
{
	/**
	 * List of allowed file mimetypes or file extensions. 
	 * All defined file mimetypes are checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mimetype.
	 * All defined file extensions are translated internally on server side into mimetypes,
	 * then checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mimetype.
	 * Example: `$this->accept = ['image/*', 'audio/mp3', '.docx'];`
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
	 * @var \string[]
	 */
	protected $accept = [];

	/**
	 * Boolean attribute indicates that capture of media directly from the 
	 * device's sensors using a media capture mechanism is preferred, 
	 * such as a webcam or microphone. This HTML attribute is used on mobile devices.
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-capture
	 * @see https://www.w3.org/TR/html-media-capture/#dfn-media-capture-mechanism
	 * @var string|NULL
	 */
	protected $capture = NULL;

	/**
	 * Allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @var string|NULL
	 */
	protected $allowedFileNameChars = NULL;

	/**
	 * Minimum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @var int|NULL
	 */
	protected $minCount = NULL;

	/**
	 * Maximum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @var int|NULL
	 */
	protected $maxCount = NULL;

	/**
	 * Minimum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @var int|NULL
	 */
	protected $minSize = NULL;

	/**
	 * Maximum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @var int|NULL
	 */
	protected $maxSize = NULL;

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
	 * not uploaded. Default value is `10000`.
	 * @var float
	 */
	protected $archiveMaxCompressPercentage = 10.0;

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
	 * @var \string[]
	 */
	protected $bombScanners = [
		'\MvcCore\Ext\Forms\Validators\Files\Validations\BombScanners\ZipArchive',
		'\MvcCore\Ext\Forms\Validators\Files\Validations\BombScanners\PngImage',
		'\MvcCore\Ext\Forms\Validators\Files\Validations\BombScanners\GzArchive'
	];

	/**
	 * Get list of allowed file mime-types or file extensions. 
	 * All defined file mime-types are checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mime-type.
	 * All defined file extensions are translated internally on server side into mime-types,
	 * then checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mime-type.
	 * Example: `$this->accept = ['image/*', 'audio/mp3', '.docx'];`
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
	 * @return \string[]
	 */
	public function GetAccept () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->accept;
	}

	/**
	 * Set list of allowed file mime-types or file extensions. 
	 * All defined file mime-types are checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mime-type.
	 * All defined file extensions are translated internally on server side into mime-types,
	 * then checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mime-type.
	 * Example: `$this->accept = ['image/*', 'audio/mp3', '.docx'];`
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
	 * @param \string[] $accept 
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetAccept (array $accept = []) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->accept = $accept;
		return $this;
	}

	/**
	 * Get boolean attribute indicates that capture of media directly from the 
	 * device's sensors using a media capture mechanism is preferred, 
	 * such as a webcam or microphone. This HTML attribute is used on mobile devices.
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-capture
	 * @return string|NULL
	 */
	public function GetCapture () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->capture;
	}

	/**
	 * Set boolean attribute indicates that capture of media directly from the 
	 * device's sensors using a media capture mechanism is preferred, 
	 * such as a webcam or microphone. This HTML attribute is used on mobile devices.
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-capture
	 * @param string|NULL $capture 
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetCapture ($capture = 'camera') {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->capture = $capture;
		return $this;
	}

	/**
	 * Get allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @return string|NULL
	 */
	public function GetAllowedFileNameChars () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->allowedFileNameChars;
	}

	/**
	 * Set allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @param string|NULL $allowedFileNameChars
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetAllowedFileNameChars ($allowedFileNameChars) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->allowedFileNameChars = $allowedFileNameChars;
		return $this;
	}

	/**
	 * Get minimum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMinCount () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->minCount;
	}

	/**
	 * Set minimum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $minCount
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetMinCount ($minCount) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->minCount = $minCount === NULL ? NULL : intval($minCount);
		return $this;
	}

	/**
	 * Get maximum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMaxCount () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->maxCount;
	}

	/**
	 * Set maximum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $maxCount
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetMaxCount ($maxCount) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->maxCount = $maxCount === NULL ? NULL : intval($maxCount);
		return $this;
	}

	/**
	 * Get minimum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMinSize () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->minSize;
	}

	/**
	 * Set minimum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * You can use integer value or human form string like `1MB`.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $minSize
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetMinSize ($minSize) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		if ($minSize !== NULL)
			$this->minSize = \MvcCore\Ext\Form::ConvertBytesFromHumanForm(
				$minSize
			);
		return $this;
	}

	/**
	 * Get maximum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMaxSize () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->maxSize;
	}

	/**
	 * Set maximum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * You can use integer value or human form string like `5MB`.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|string|NULL $maxSize
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetMaxSize ($maxSize) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		if ($maxSize !== NULL)
			$this->maxSize = \MvcCore\Ext\Form::ConvertBytesFromHumanForm(
				$maxSize
			);
		return $this;
	}

	/**
	 * Set maximum number of allowed files count inside 
	 * single uploaded archive file. If uploaded archive 
	 * has more files inside than this number, it's 
	 * proclaimed as archive bomb and it's not uploaded.
	 * Default value is `1000`.
	 * @param int $archiveMaxItems Default `1000`.
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxItems ($archiveMaxItems = 1000) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->archiveMaxItems = $archiveMaxItems;
		return $this;
	}
	
	/**
	 * Get maximum number of allowed files count inside 
	 * single uploaded archive file. If uploaded archive 
	 * has more files inside than this number, it's 
	 * proclaimed as archive bomb and it's not uploaded.
	 * Default value is `1000`.
	 * @return int
	 */
	public function GetArchiveMaxItems () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->archiveMaxItems;
	}

	/**
	 * Maximum number of allowed ZIP archive levels inside.
	 * If uploaded archive contains another zip archive and
	 * those archive another and another, this is maximum
	 * level for nested ZIP archives. If Archive contains 
	 * more levels than this, it's proclaimed as archive 
	 * bomb and it's not uploaded. Default value is `3`.
	 * @param int $archiveMaxLevels Default `3`.
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxLevels ($archiveMaxLevels = 3) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->archiveMaxLevels = $archiveMaxLevels;
		return $this;
	}
	
	/**
	 * Maximum number of allowed ZIP archive levels inside.
	 * If uploaded archive contains another zip archive and
	 * those archive another and another, this is maximum
	 * level for nested ZIP archives. If Archive contains 
	 * more levels than this, it's proclaimed as archive 
	 * bomb and it's not uploaded. Default value is `3`.
	 * @return int
	 */
	public function GetArchiveMaxLevels () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->archiveMaxLevels;
	}

	/**
	 * Set maximum archive compression percentage.
	 * If archive file has lower percentage size
	 * than all archive file items together, 
	 * it's proclaimed as archive bomb and it's 
	 * not uploaded.
	 * @param float $archiveMaxCompressPercentage Default `10.0`.
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxCompressPercentage ($archiveMaxCompressPercentage = 10.0) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->archiveMaxCompressPercentage = $archiveMaxCompressPercentage;
		return $this;
	}
	
	/**
	 * Get maximum archive compression percentage.
	 * If archive file has lower percentage size
	 * than all archive file items together, 
	 * it's proclaimed as archive bomb and it's 
	 * not uploaded.
	 * @return float
	 */
	public function GetArchiveMaxCompressPercentage () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->archiveMaxCompressPercentage;
	}

	/**
	 * PNG image maximum width or maximum height.
	 * PNG images use ZIP compression and that's why 
	 * those images could be used as ZIP bombs.
	 * This limit helps to prevent file bombs 
	 * based on PNG images. Default value is `10000`.
	 * @param int $pngImageMaxWidthHeight Default `10.0`.
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetPngImageMaxWidthHeight ($pngImageMaxWidthHeight = 10000) {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$this->pngImageMaxWidthHeight = $pngImageMaxWidthHeight;
		return $this;
	}
	
	/**
	 * PNG image maximum width or maximum height.
	 * PNG images use ZIP compression and that's why 
	 * those images could be used as ZIP bombs.
	 * This limit helps to prevent file bombs 
	 * based on PNG images. Default value is `10000`.
	 * @return int
	 */
	public function GetPngImageMaxWidthHeight () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->pngImageMaxWidthHeight;
	}

	/**
	 * Add bomb scanner class(es) to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * @param \string[] $bombScannerClasses,...
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function AddBombScanners () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$args = func_get_args();
		if (count($args) === 1 && is_array($args)) {
			$bombScannerClasses = $args[0];
		} else {
			$bombScannerClasses = $args;
		}
		foreach ($bombScannerClasses as $bombScannerClass) {
			if (!in_array($bombScannerClass, $this->bombScanners, TRUE)) {
				$this->bombScanners[] = $bombScannerClass;
			}
		}
		return $this;
	}
	
	/**
	 * Set bomb scanner class(es) to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * @param \string[] $bombScannerClasses
	 * @return \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetBombScanners () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		$args = func_get_args();
		if (count($args) === 1 && is_array($args)) {
			$this->bombScanners = $args[0];
		} else {
			$this->bombScanners = $args;
		}
		return $this;
	}

	/**
	 * Get bomb scanner class(es) to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * @return \string[]
	 */
	public function GetBombScanners () {
		/** @var $this \MvcCore\Ext\Forms\Fields\File|\MvcCore\Ext\Forms\Validators\Files */
		return $this->bombScanners;
	}
}
