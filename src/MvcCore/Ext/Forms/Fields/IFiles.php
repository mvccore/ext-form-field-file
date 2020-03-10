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

namespace MvcCore\Ext\Forms\Fields;

/**
 * Responsibility: define getters and setters for field properties: `accept`, 
 * 				   `capture`, `allowedFileNameChars`, `minCount`, `maxCount`,
 * 				   `minSize` and `maxSize`.
 * Interface for classes:
 * - `\MvcCore\Ext\Forms\Fields\File`
 * - `\MvcCore\Ext\Forms\Validators\Files`
 */
interface IFiles
{
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
	public function GetAccept ();

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
	 * @return \MvcCore\Ext\Forms\IField
	 */
	public function SetAccept (array $accept = []);

	/**
	 * Get boolean attribute indicates that capture of media directly from the 
	 * device's sensors using a media capture mechanism is preferred, 
	 * such as a webcam or microphone. This HTML attribute is used on mobile devices.
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-capture
	 * @return string|NULL
	 */
	public function GetCapture ();

	/**
	 * Set boolean attribute indicates that capture of media directly from the 
	 * device's sensors using a media capture mechanism is preferred, 
	 * such as a webcam or microphone. This HTML attribute is used on mobile devices.
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-capture
	 * @param string|NULL $capture 
	 * @return \MvcCore\Ext\Forms\IField
	 */
	public function SetCapture ($capture = 'camera');

	/**
	 * Get allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `\MvcCore\Ext\Forms\Validators\Files::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @return string|NULL
	 */
	public function GetAllowedFileNameChars ();

	/**
	 * Set allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `\MvcCore\Ext\Forms\Validators\Files::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @param string|NULL $allowedFileNameChars
	 * @return \MvcCore\Ext\Forms\IField
	 */
	public function SetAllowedFileNameChars ($allowedFileNameChars);

	/**
	 * Get minimum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMinCount ();

	/**
	 * Set minimum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $minCount
	 * @return \MvcCore\Ext\Forms\IField
	 */
	public function SetMinCount ($minCount);

	/**
	 * Get maximum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMaxCount ();

	/**
	 * Set maximum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $maxCount
	 * @return \MvcCore\Ext\Forms\IField
	 */
	public function SetMaxCount ($maxCount);

	/**
	 * Get minimum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMinSize ();

	/**
	 * Set minimum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $minSize
	 * @return \MvcCore\Ext\Forms\IField
	 */
	public function SetMinSize ($minSize);

	/**
	 * Get maximum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @return int|NULL
	 */
	public function GetMaxSize ();

	/**
	 * Set maximum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param int|NULL $maxSize
	 * @return \MvcCore\Ext\Forms\IField
	 */
	public function SetMaxSize ($maxSize);
}
