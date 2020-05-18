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
	 * `self::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
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
	 * @return \MvcCore\Ext\Forms\Field|\MvcCore\Ext\Forms\IField
	 */
	public function SetAccept (array $accept = []) {
		/** @var $this \MvcCore\Ext\Forms\IField */
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
		return $this->capture;
	}

	/**
	 * Set boolean attribute indicates that capture of media directly from the 
	 * device's sensors using a media capture mechanism is preferred, 
	 * such as a webcam or microphone. This HTML attribute is used on mobile devices.
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-capture
	 * @param string|NULL $capture 
	 * @return \MvcCore\Ext\Forms\Field|\MvcCore\Ext\Forms\IField
	 */
	public function SetCapture ($capture = 'camera') {
		/** @var $this \MvcCore\Ext\Forms\IField */
		$this->capture = $capture;
		return $this;
	}

	/**
	 * Get allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `\MvcCore\Ext\Forms\Validators\Files::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @return string|NULL
	 */
	public function GetAllowedFileNameChars () {
		return $this->allowedFileNameChars;
	}

	/**
	 * Set allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `\MvcCore\Ext\Forms\Validators\Files::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @param string|NULL $allowedFileNameChars
	 * @return \MvcCore\Ext\Forms\Field|\MvcCore\Ext\Forms\IField
	 */
	public function SetAllowedFileNameChars ($allowedFileNameChars) {
		/** @var $this \MvcCore\Ext\Forms\IField */
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
		return $this->minCount;
	}

	/**
	 * Set minimum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $minCount
	 * @return \MvcCore\Ext\Forms\Field|\MvcCore\Ext\Forms\IField
	 */
	public function SetMinCount ($minCount) {
		/** @var $this \MvcCore\Ext\Forms\IField */
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
		return $this->maxCount;
	}

	/**
	 * Set maximum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $maxCount
	 * @return \MvcCore\Ext\Forms\Field|\MvcCore\Ext\Forms\IField
	 */
	public function SetMaxCount ($maxCount) {
		/** @var $this \MvcCore\Ext\Forms\IField */
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
		return $this->minSize;
	}

	/**
	 * Set minimum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $minSize
	 * @return \MvcCore\Ext\Forms\Field|\MvcCore\Ext\Forms\IField
	 */
	public function SetMinSize ($minSize) {
		/** @var $this \MvcCore\Ext\Forms\IField */
		$this->minSize = $minSize === NULL ? NULL : intval($minSize);
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
		return $this->maxSize;
	}

	/**
	 * Set maximum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $maxSize
	 * @return \MvcCore\Ext\Forms\Field|\MvcCore\Ext\Forms\IField
	 */
	public function SetMaxSize ($maxSize) {
		/** @var $this \MvcCore\Ext\Forms\IField */
		$this->maxSize = $maxSize === NULL ? NULL : intval($maxSize);
		return $this;
	}
}
