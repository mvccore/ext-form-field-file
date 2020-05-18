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
 * Responsibility: init, pre-dispatch and render `<input>` HTML element 
 *				   with type `file`. `File` field has it's own validator 
 *				   `Files` to check everything necessary for uploaded 
 *				   files and check files by `accept` attribute rules by 
 *				   magic bytes.
 */
class File 
	extends		\MvcCore\Ext\Forms\Field
	implements	\MvcCore\Ext\Forms\Fields\IVisibleField, 
				\MvcCore\Ext\Forms\Fields\ILabel,
				\MvcCore\Ext\Forms\Fields\IMultiple,
				\MvcCore\Ext\Forms\Fields\IFiles,
				\MvcCore\Ext\Forms\Fields\IAlwaysValidate
{
	use \MvcCore\Ext\Forms\Field\Props\VisibleField;
	use \MvcCore\Ext\Forms\Field\Props\Label;
	use \MvcCore\Ext\Forms\Field\Props\Multiple;
	use \MvcCore\Ext\Forms\Field\Props\Files;
	use \MvcCore\Ext\Forms\Field\Props\Wrapper;
	
	/**
	 * Default allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this constant. This constant is used only
	 * if there is not specified any custom characters and characters groups by method(s): 
	 * `$field->SetAllowedFileNameChars('...');` or  `$validator->SetAllowedFileNameChars('...');`.
	 */
	const ALLOWED_FILE_NAME_CHARS_DEFAULT = '-a-zA-Z0-9@%&,~`._ !#$^()+={}[]<>\'';

	/**
	 * Possible values: `file`.
	 * @var string
	 */
	protected $type = 'file';

	/**
	 * Validators: 
	 * - `Files` - to check everything necessary for uploaded files and check 
	 *			   files by `accept` attribute rules by magic bytes.
	 * @var string[]|\Closure[]
	 */
	protected $validators = ['Files'];

	/**
	 * This INTERNAL method is called from `\MvcCore\Ext\Form` after field
	 * is added into form instance by `$form->AddField();` method. Do not 
	 * use this method even if you don't develop any form field.
	 * - Check if field has any name, which is required.
	 * - Set up form and field id attribute by form id and field name.
	 * - Set up required.
	 * - Set up translate boolean property.
	 * - Check if there is defined any value for `accept` attribute to validate uploaded files.
	 * - Check if form has correct `enctype` attribute for uploading files.
	 * @param \MvcCore\Ext\Form|\MvcCore\Ext\Forms\IForm $form
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Forms\Fields\Select|\MvcCore\Ext\Forms\IField
	 */
	public function SetForm (\MvcCore\Ext\Forms\IForm $form) {
		/** @var $this \MvcCore\Ext\Forms\IField */
		parent::SetForm($form);
		if ($this->accept === NULL) $this->throwNewInvalidArgumentException(
			'No `accept` property defined.'
		);
		if ($form->GetEnctype() !== \MvcCore\Ext\Forms\IForm::ENCTYPE_MULTIPART) 
			$this->throwNewInvalidArgumentException(
				'Form needs to define `enctype` attribute as `' 
				. \MvcCore\Ext\Forms\IForm::ENCTYPE_MULTIPART . '`.'
			);
		return $this;
	}

	/**
	 * Return field specific data for validator.
	 * @param array $fieldPropsDefaultValidValues 
	 * @return array
	 */
	public function & GetValidatorData ($fieldPropsDefaultValidValues = []) {
		$result = [
			'multiple'				=> $this->multiple,
			'accept'				=> $this->accept,
			'allowedFileNameChars'	=> static::ALLOWED_FILE_NAME_CHARS_DEFAULT,
			'minCount'				=> $this->minCount,
			'maxCount'				=> $this->maxCount,
			'minSize'				=> $this->minSize,
			'maxSize'				=> $this->maxSize,
		];
		return $result;
	}

	/**
	 * This INTERNAL method is called from `\MvcCore\Ext\Form` just before
	 * field is naturally rendered. It sets up field for rendering process.
	 * Do not use this method event if you don't develop any form field.
	 * - Set up field render mode if not defined.
	 * - Translate label text if necessary.
	 * - Set up tab-index if necessary.
	 * @return void
	 */
	public function PreDispatch () {
		parent::PreDispatch();
		$this->preDispatchTabIndex();
	}

	/**
	 * This INTERNAL method is called from `\MvcCore\Ext\Forms\Field\Rendering` 
	 * in rendering process. Do not use this method even if you don't develop any form field.
	 * 
	 * Render control tag only without label or specific errors.
	 * @return string
	 */
	public function RenderControl () {
		if ($this->minCount !== NULL) 
			$this->SetControlAttr('data-min-count', $this->minCount);
		if ($this->maxCount !== NULL) 
			$this->SetControlAttr('data-max-count', $this->maxCount);
		if ($this->minSize !== NULL) 
			$this->SetControlAttr('data-min-size', $this->minSize);
		if ($this->maxSize !== NULL) 
			$this->SetControlAttr('data-max-size', $this->maxSize);
		$attrsStr = $this->renderControlAttrsWithFieldVars([
			'accept',
			'capture',
		]);
		$attrsStrSep = strlen($attrsStr) > 0 ? ' ' : '';
		if ($this->multiple) {
			$attrsStr .= $attrsStrSep . 'multiple="multiple"';
			$attrsStrSep = ' ';
		}
		if (!$this->form->GetFormTagRenderingStatus()) {
			$attrsStr .= $attrsStrSep . 'form="' . $this->form->GetId() . '"';
		}
		$formViewClass = $this->form->GetViewClass();
		/** @var $templates \stdClass */
		$templates = static::$templates;
		$result = $formViewClass::Format($templates->control, [
			'id'		=> $this->id,
			'name'		=> $this->name . ($this->multiple ? '[]' : ''),
			'type'		=> $this->type,
			'value'		=> '',
			'attrs'		=> strlen($attrsStr) > 0 ? ' ' . $attrsStr : '',
		]);
		return $this->renderControlWrapper($result);
	}
}
