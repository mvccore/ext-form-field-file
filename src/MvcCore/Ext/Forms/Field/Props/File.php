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

namespace MvcCore\Ext\Forms\Field\Props;

/**
 * Trait for classes:
 * - `\MvcCore\Ext\Forms\Fields\File`
 * - `\MvcCore\Ext\Forms\Validators\Files`
 * @mixin \MvcCore\Ext\Forms\Fields\File
 * @mixin \MvcCore\Ext\Forms\Validators\Files
 */
trait File {

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
	 * @inheritDoc
	 * @return \string[]
	 */
	public function GetAccept () {
		return $this->accept;
	}

	/**
	 * @inheritDoc
	 * @param  \string[] $accept 
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetAccept (array $accept = []) {
		$this->accept = $accept;
		return $this;
	}

	/**
	 * @inheritDoc
	 * @return string|NULL
	 */
	public function GetCapture () {
		return $this->capture;
	}

	/**
	 * @inheritDoc
	 * @param  string|NULL $capture 
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetCapture ($capture = 'camera') {
		$this->capture = $capture;
		return $this;
	}

	/**
	 * @inheritDoc
	 * @return int|NULL
	 */
	public function GetMinCount () {
		return $this->minCount;
	}

	/**
	 * @inheritDoc
	 * @param  int|NULL $minCount
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetMinCount ($minCount) {
		$this->minCount = $minCount === NULL ? NULL : intval($minCount);
		return $this;
	}

	/**
	 * @inheritDoc
	 * @return int|NULL
	 */
	public function GetMaxCount () {
		return $this->maxCount;
	}

	/**
	 * @inheritDoc
	 * @param  int|NULL $maxCount
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetMaxCount ($maxCount) {
		$this->maxCount = $maxCount === NULL ? NULL : intval($maxCount);
		return $this;
	}

	/**
	 * @inheritDoc
	 * @return int|NULL
	 */
	public function GetMinSize () {
		return $this->minSize;
	}

	/**
	 * @inheritDoc
	 * @param  int|string|NULL $minSize
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetMinSize ($minSize) {
		if (is_string($minSize))
			$this->minSize = \MvcCore\Ext\Form::ConvertBytesFromHumanForm(
				$minSize
			);
		return $this;
	}

	/**
	 * @inheritDoc
	 * @return int|NULL
	 */
	public function GetMaxSize () {
		return $this->maxSize;
	}

	/**
	 * @inheritDoc
	 * @param  int|string|NULL $maxSize
	 * @return \MvcCore\Ext\Forms\Fields\File
	 */
	public function SetMaxSize ($maxSize) {
		if (is_string($maxSize))
			$this->maxSize = \MvcCore\Ext\Form::ConvertBytesFromHumanForm(
				$maxSize
			);
		return $this;
	}

}
