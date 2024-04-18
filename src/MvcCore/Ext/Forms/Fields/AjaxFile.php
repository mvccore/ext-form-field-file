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
 * Responsibility: init, pre-dispatch and render `<input>` HTML element 
 *                 with type `ajax-file`. `AjaxFile` field has it's own validator 
 *                 `AjaxFiles` to check everything necessary for sent 
 *                 files and check files by `accept` attribute rules by 
 *                 magic bytes.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class		AjaxFile 
extends		\MvcCore\Ext\Forms\Fields\File {

	const AJAX_OBJECT_FIELD_FILENAME	= 'filename';
	const AJAX_OBJECT_FIELD_TYPE		= 'type';
	const AJAX_OBJECT_FIELD_CONTENT		= 'content';

	#region instance properties

	/**
	 * Possible values: `ajax-file`.
	 * @var string
	 */
	protected $type = 'ajax-file';

	/**
	 * Validators: 
	 * - `AjaxFiles` - to check everything necessary for sent files and check 
	 *                 files by `accept` attribute rules by magic bytes.
	 * @var \string[]|\MvcCore\Ext\Forms\Validator[]
	 */
	protected $validators = ['AjaxFiles'];
	
	#endregion

	#region public instance methods

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function RenderControl () {
		$typeBefore = $this->type;
		$this->type = 'file';
		$result = parent::RenderControl();
		$this->type = $typeBefore;
		return $result;
	}

	#endregion

	#region protected instance methods

	/**
	 * Check configuration against PHP ini 
	 * values and between each other.
	 * If there is any error, thrown an exception.
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	protected function checkConfiguration () {
		$this
			->checkConfigurationAccept()
			->checkConfigurationFileUploads()
			->checkConfigurationMaxSize()
			->checkConfigurationMaxFiles()
			->checkConfigurationMinMaxCount()
			->checkConfigurationMinMaxSize();
	}

	#endregion

}
