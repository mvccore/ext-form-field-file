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
 *				   with type `file`. `File` field has it's own validator 
 *				   `Files` to check everything necessary for uploaded 
 *				   files and check files by `accept` attribute rules by 
 *				   magic bytes.
 */
class		File 
extends		\MvcCore\Ext\Forms\Field
implements	\MvcCore\Ext\Forms\Fields\IVisibleField, 
			\MvcCore\Ext\Forms\Fields\ILabel,
			\MvcCore\Ext\Forms\Fields\IMultiple,
			\MvcCore\Ext\Forms\Fields\IFile,
			\MvcCore\Ext\Forms\Fields\IAlwaysValidate {

	use \MvcCore\Ext\Forms\Field\Props\VisibleField;
	use \MvcCore\Ext\Forms\Field\Props\Label;
	use \MvcCore\Ext\Forms\Field\Props\Multiple;
	use \MvcCore\Ext\Forms\Field\Props\Files;
	use \MvcCore\Ext\Forms\Field\Props\Wrapper;
	
	/**
	 * MvcCore Extension - Form - Field - File - version:
	 * Comparison by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '5.0.0';

	/**
	 * Default allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this constant. This constant is used only
	 * if there is not specified any custom characters and characters groups by method(s): 
	 * `$field->SetAllowedFileNameChars('...');` or  `$validator->SetAllowedFileNameChars('...');`.
	 */
	const ALLOWED_FILE_NAME_CHARS_DEFAULT = '-a-zA-Z0-9@,._ ()+={}[]\'';
	
	const CONFIG_ERR_NO_ACCEPT_PROPERTY		= 100;
	const CONFIG_ERR_WRONG_FORM_ENCTYPE		= 101;
	const CONFIG_ERR_UPLOADS_NOT_ALOWED		= 102;
	const CONFIG_ERR_MAX_UPLOAD_SIZE_LOWER	= 103;
	const CONFIG_ERR_MAX_POST_SIZE_LOWER	= 104;
	const CONFIG_ERR_MAX_FILES_COUNT_LOWER	= 105;
	const CONFIG_ERR_MISMATCH_MIN_MAX_COUNT = 106;
	const CONFIG_ERR_MISMATCH_MIN_MAX_SIZE	= 107;

	/**
	 * Configuration error messages.
	 * @var array
	 */
	protected static $configErrorMessages = [
		self::CONFIG_ERR_NO_ACCEPT_PROPERTY		=> "No `accept` property defined.",
		self::CONFIG_ERR_WRONG_FORM_ENCTYPE		=> "Form needs to define `enctype` attribute as `{0}`.",
		self::CONFIG_ERR_UPLOADS_NOT_ALOWED		=> "System has not allowed file upload.",
		self::CONFIG_ERR_MAX_UPLOAD_SIZE_LOWER	=> "System value for max. file upload size is lower than field configuration.",
		self::CONFIG_ERR_MAX_POST_SIZE_LOWER	=> "System value for max. POST size is lower than field configuration.",
		self::CONFIG_ERR_MAX_FILES_COUNT_LOWER	=> "System value for max. uploaded files count is lower than field configuration.",
		self::CONFIG_ERR_MISMATCH_MIN_MAX_COUNT => "Mismatch in min. and max. uploaded files count in field configuration.",
		self::CONFIG_ERR_MISMATCH_MIN_MAX_SIZE	=> "Mismatch in min. and max. uploaded files sizes in field configuration.",
	];

	/**
	 * Possible values: `file`.
	 * @var string
	 */
	protected $type = 'file';

	/**
	 * Validators: 
	 * - `Files` - to check everything necessary for uploaded files and check 
	 *			   files by `accept` attribute rules by magic bytes.
	 * @var \string[]|\MvcCore\Ext\Forms\Validator[]
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
	 * @param \MvcCore\Ext\Form $form
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Forms\Fields\Select
	 */
	public function SetForm (\MvcCore\Ext\IForm $form) {
		/** @var $this \MvcCore\Ext\Forms\Field */
		parent::SetForm($form);
		$this->checkConfiguration();
		return $this;
	}
	
	/**
	 * Check configuration against PHP ini 
	 * values and between each other.
	 * If there is any error, thrown an exception.
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	protected function checkConfiguration () {
		if ($this->accept === NULL) 
			$this->throwConfigException(
				static::CONFIG_ERR_NO_ACCEPT_PROPERTY
			);

		$multipartFormEnctype = \MvcCore\Ext\IForm::ENCTYPE_MULTIPART;
		if ($this->form->GetEnctype() !== $multipartFormEnctype) 
			$this->throwConfigException(
				str_replace(
					'{0}', $multipartFormEnctype,
					static::CONFIG_ERR_WRONG_FORM_ENCTYPE
				)
			);

		$rawFileUploads = @ini_get("file_uploads");
		if (
			!$rawFileUploads || 
			strtolower($rawFileUploads) == 'off'
		) $this->throwConfigException(
			static::CONFIG_ERR_UPLOADS_NOT_ALOWED
		);

		if ($this->maxSize !== NULL) {
			$maxIniFileSize = $this->form->GetPhpIniSizeLimit(
				"upload_max_filesize"
			);
			if (
				$maxIniFileSize !== NULL && 
				$this->maxSize > $maxIniFileSize
			) 
				$this->throwConfigException(
					static::CONFIG_ERR_MAX_UPLOAD_SIZE_LOWER
				);

			$maxIniPostSize = $this->form->GetPhpIniSizeLimit(
				"post_max_size"
			);
			if (
				$maxIniPostSize !== NULL &&
				$this->maxSize > $maxIniPostSize
			) 
				$this->throwConfigException(
					static::CONFIG_ERR_MAX_POST_SIZE_LOWER
				);
		}

		if ($this->multiple) {
			$maxFiles = $this->form->GetPhpIniSizeLimit(
				"max_file_uploads"
			);
			if (
				$maxFiles !== NULL && (
					$maxFiles < 2 || (
						$this->maxCount !== NULL && 
						$this->maxCount > $maxFiles
					)
				)
			) $this->throwConfigException(
				static::CONFIG_ERR_MAX_FILES_COUNT_LOWER
			);
		}

		if (
			$this->minCount !== NULL && 
			$this->maxCount &&
			$this->minCount > $this->maxCount
		) $this->throwConfigException(
			static::CONFIG_ERR_MISMATCH_MIN_MAX_COUNT
		);

		if (
			$this->minSize !== NULL && 
			$this->maxSize &&
			$this->minSize > $this->maxSize
		) $this->throwConfigException(
			static::CONFIG_ERR_MISMATCH_MIN_MAX_SIZE
		);
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
		$this->checkConfiguration();
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

	/**
	 * Throw an configuration exception by given error number.
	 * @param int   $errorNumber
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	protected function throwConfigException ($errorNumber, $errorMsgArgs = []) {
		$errorMessage = static::$configErrorMessages[$errorNumber];
		$this->throwNewInvalidArgumentException(
			$errorMessage
		);
	}
}
