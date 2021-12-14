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

namespace MvcCore\Ext\Forms\Fields;

/**
 * Responsibility: define getters and setters for field properties: `accept`, 
 *                 `capture`, `allowedFileNameChars`, `minCount`, `maxCount`,
 *                 `minSize` and `maxSize`.
 * Interface for class:
 * - `\MvcCore\Ext\Forms\Fields\File`
 */
interface IFile {
	
	#region contants

	const CONFIG_ERR_NO_ACCEPT_PROPERTY		= 100;
	const CONFIG_ERR_WRONG_FORM_ENCTYPE		= 101;
	const CONFIG_ERR_UPLOADS_NOT_ALOWED		= 102;
	const CONFIG_ERR_MAX_UPLOAD_SIZE_LOWER	= 103;
	const CONFIG_ERR_MAX_POST_SIZE_LOWER	= 104;
	const CONFIG_ERR_MAX_FILES_COUNT_LOWER	= 105;
	const CONFIG_ERR_MISMATCH_MIN_MAX_COUNT = 106;
	const CONFIG_ERR_MISMATCH_MIN_MAX_SIZE	= 107;

	#endregion


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
	 * @param  \string[] $accept 
	 * @return \MvcCore\Ext\Forms\Fields\File
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
	 * @param  string|NULL $capture 
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetCapture ($capture = 'camera');


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
	 * @param  int|NULL $minCount
	 * @return \MvcCore\Ext\Forms\Fields\File
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
	 * @param  int|NULL $maxCount
	 * @return \MvcCore\Ext\Forms\Fields\File
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
	 * You can use integer value or human form string like `1MB`.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param  int|string|NULL $minSize
	 * @return \MvcCore\Ext\Forms\Fields\File
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
	 * You can use integer value or human form string like `5MB`.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param  int|string|NULL $maxSize
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetMaxSize ($maxSize);
	
}
