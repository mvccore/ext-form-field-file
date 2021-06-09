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
 *                 with type `file`. `File` field has it's own validator 
 *                 `Files` to check everything necessary for uploaded 
 *                 files and check files by `accept` attribute rules by 
 *                 magic bytes.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
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
	const VERSION = '5.1.7';

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
	 *             files by `accept` attribute rules by magic bytes.
	 * @var \string[]|\MvcCore\Ext\Forms\Validator[]
	 */
	protected $validators = ['Files'];


	/**
	 * Create new form `<input type="file">` control instance.
	 * 
	 * @param  array        $cfg
	 * Config array with public properties and it's
	 * values which you want to configure, presented
	 * in camel case properties names syntax.
	 * 
	 * @param  string       $name 
	 * Form field specific name, used to identify submitted value.
	 * This value is required for all form fields.
	 * @param  string       $type 
	 * Fixed field order number, null by default.
	 * @param  int          $fieldOrder
	 * Form field type, used in `<input type="...">` attribute value.
	 * Every typed field has it's own string value, but base field type 
	 * `\MvcCore\Ext\Forms\Field` has `NULL`.
	 * @param  string|array $value 
	 * Form field value. It could be string or array, int or float, it depends
	 * on field implementation. Default value is `NULL`.
	 * @param  string       $title 
	 * Field title, global HTML attribute, optional.
	 * @param  string       $translate 
	 * Boolean flag about field visible texts and error messages translation.
	 * This flag is automatically assigned from `$field->form->GetTranslate();` 
	 * flag in `$field->Init();` method.
	 * @param  string       $translateTitle 
	 * Boolean to translate title text, `TRUE` by default.
	 * @param  array        $cssClasses 
	 * Form field HTML element css classes strings.
	 * Default value is an empty array to not render HTML `class` attribute.
	 * @param  array        $controlAttrs 
	 * Collection with field HTML element additional attributes by array keys/values.
	 * Do not use system attributes as: `id`, `name`, `value`, `readonly`, `disabled`, `class` ...
	 * Those attributes has it's own configurable properties by setter methods or by constructor config array.
	 * HTML field elements are meant: `<input>, <button>, <select>, <textarea> ...`. 
	 * Default value is an empty array to not render any additional attributes.
	 * @param  array        $validators 
	 * List of predefined validator classes ending names or validator instances.
	 * Keys are validators ending names and values are validators ending names or instances.
	 * Validator class must exist in any validators namespace(s) configured by default:
	 * - `array('\MvcCore\Ext\Forms\Validators\');`
	 * Or it could exist in any other validators namespaces, configured by method(s):
	 * - `\MvcCore\Ext\Form::AddValidatorsNamespaces(...);`
	 * - `\MvcCore\Ext\Form::SetValidatorsNamespaces(...);`
	 * Every given validator class (ending name) or given validator instance has to 
	 * implement interface  `\MvcCore\Ext\Forms\IValidator` or it could be extended 
	 * from base  abstract validator class: `\MvcCore\Ext\Forms\Validator`.
	 * Every typed field has it's own predefined validators, but you can define any
	 * validator you want and replace them.
	 * 
	 * @param  string       $accessKey
	 * The access key global attribute provides a hint for generating
	 * a keyboard shortcut for the current element. The attribute 
	 * value must consist of a single printable character (which 
	 * includes accented and other characters that can be generated 
	 * by the keyboard).
	 * @param  bool         $autoFocus
	 * This Boolean attribute lets you specify that a form control should have input
	 * focus when the page loads. Only one form-associated element in a document can
	 * have this attribute specified. 
	 * @param  bool         $disabled
	 * Form field attribute `disabled`, determination if field value will be 
	 * possible to change by user and if user will be graphically informed about it 
	 * by default browser behaviour or not. Default value is `FALSE`. 
	 * This flag is also used for sure for submit checking. But if any field is 
	 * marked as disabled, browsers always don't send any value under this field name
	 * in submit. If field is configured as disabled, no value sent under field name 
	 * from user will be accepted in submit process and value for this field will 
	 * be used by server side form initialization. 
	 * Disabled attribute has more power than required. If disabled is true and
	 * required is true and if there is no or invalid submitted value, there is no 
	 * required error and it's used value from server side assigned by 
	 * `$form->SetValues();` or from session.
	 * @param  bool         $readOnly
	 * Form field attribute `readonly`, determination if field value will be 
	 * possible to read only or if value will be possible to change by user. 
	 * Default value is `FALSE`. This flag is also used for submit checking. 
	 * If any field is marked as read only, browsers always send value in submit.
	 * If field is configured as read only, no value sent under field name 
	 * from user will be accepted in submit process and value for this field 
	 * will be used by server side form initialization. 
	 * Readonly attribute has more power than required. If readonly is true and
	 * required is true and if there is invalid submitted value, there is no required 
	 * error and it's used value from server side assigned by 
	 * `$form->SetValues();` or from session.
	 * @param  bool         $required
	 * Form field attribute `required`, determination
	 * if control will be required to complete any value by user.
	 * This flag is also used for submit checking. Default value is `NULL`
	 * to not require any field value. If form has configured it's property
	 * `$form->GetDefaultRequired()` to `TRUE` and this value is `NULL`, field
	 * will be automatically required by default form configuration.
	 * @param  int|string   $tabIndex
	 * An integer attribute indicating if the element can take input focus (is focusable), 
	 * if it should participate to sequential keyboard navigation, and if so, at what 
	 * position. You can set `auto` string value to get next form tab-index value automatically. 
	 * Tab-index for every field in form is better to index from value `1` or automatically and 
	 * moved to specific higher value by place, where is form currently rendered by form 
	 * instance method `$form->SetBaseTabIndex()` to move tab-index for each field into 
	 * final values. Tab-index can takes several values:
	 * - a negative value means that the element should be focusable, but should not be 
	 *   reachable via sequential keyboard navigation;
	 * - 0 means that the element should be focusable and reachable via sequential 
	 *   keyboard navigation, but its relative order is defined by the platform convention;
	 * - a positive value means that the element should be focusable and reachable via 
	 *   sequential keyboard navigation; the order in which the elements are focused is 
	 *   the increasing value of the tab-index. If several elements share the same tab-index, 
	 *   their relative order follows their relative positions in the document.
	 * 
	 * @param  string       $label
	 * Control label visible text. If field form has configured any translator, translation 
	 * will be processed automatically before rendering process. Default value is `NULL`.
	 * @param  bool         $translateLabel
	 * Boolean to translate label text, `TRUE` by default.
	 * @param  string       $labelSide
	 * Label side from rendered field - location where label will be rendered.
	 * By default `$this->labelSide` is configured to `left`.
	 * If you want to reconfigure it to different side,
	 * the only possible value is `right`.
	 * You can use constants:
	 * - `\MvcCore\Ext\Forms\IField::LABEL_SIDE_LEFT`
	 * - `\MvcCore\Ext\Forms\IField::LABEL_SIDE_RIGHT`
	 * @param  int          $renderMode
	 * Rendering mode flag how to render field and it's label.
	 * Default value is `normal` to render label and field, label 
	 * first or field first by another property `$field->labelSide = 'left' | 'right';`.
	 * But if you want to render label around field or if you don't want
	 * to render any label, you can change this with constants (values):
	 * - `\MvcCore\Ext\Form::FIELD_RENDER_MODE_NORMAL`       - `<label /><input />`
	 * - `\MvcCore\Ext\Form::FIELD_RENDER_MODE_LABEL_AROUND` - `<label><input /></label>`
	 * - `\MvcCore\Ext\Form::FIELD_RENDER_MODE_NO_LABEL`     - `<input />`
	 * @param  array        $labelAttrs
	 * Collection with `<label>` HTML element additional attributes by array keys/values.
	 * Do not use system attributes as: `id`,`for` or `class`, those attributes has it's own 
	 * configurable properties by setter methods or by constructor config array. Label `class` 
	 * attribute has always the same css classes as it's field automatically. 
	 * Default value is an empty array to not render any additional attributes.
	 * 
	 * @param  bool         $multiple
	 * If control is `<input>` with `type` as `file` or `email`,
	 * this Boolean attribute indicates whether the user can enter 
	 * more than one value.
	 * If control is `<input>` with `type` as `range`, there are 
	 * rendered two connected sliders (range controls) as one control
	 * to simulate range from and range to. Result value will be array.
	 * If control is `<select>`, this Boolean attribute indicates 
	 * that multiple options can be selected in the list. When 
	 * multiple is specified, most browsers will show a scrolling 
	 * list box inst
	 * 
	 * @param  \string[]    $accept
	 * List of allowed file mimetypes or file extensions. 
	 * All defined file mimetypes are checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mimetype.
	 * All defined file extensions are translated internally on server side into mimetypes,
	 * then checked with `finfo` PHP extension and checked by
	 * allowed file extensions for defined mimetype.
	 * Example: `$this->accept = ['image/*', 'audio/mp3', '.docx'];`
	 * @param  string       $capture
	 * Boolean attribute indicates that capture of media directly from the 
	 * device's sensors using a media capture mechanism is preferred, 
	 * such as a webcam or microphone. This HTML attribute is used on mobile devices.
	 * @param  string       $allowedFileNameChars
	 * Allowed file name characters and characters groups for submit regular expression.
	 * All regular expression special characters will be escaped by `addcslashes()` 
	 * function to create proper regular expression pattern to keep only characters 
	 * and characters groups presented in this variable. If there are not defined any 
	 * characters, there is used in submit filename sanitization PHP constant: 
	 * `static::ALLOWED_FILE_NAME_CHARS_DEFAULT`;
	 * @param  int          $minCount
	 * Minimum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param  int          $maxCount
	 * Maximum uploaded files count. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-count="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param  int          $minSize
	 * Minimum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-min-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param  int          $maxSize
	 * Maximum uploaded file size for one uploaded item in bytes. `NULL` by default.
	 * This attribute is not HTML5, it's rendered as `data-max-size="..."`.
	 * Attribute is not used on client side by default, but you can do it, it's
	 * only checked if attribute is not `NULL` in submit processing.
	 * @param  int          $archiveMaxItems
	 * Maximum number of allowed files count inside 
	 * single uploaded archive file. If uploaded archive 
	 * has more files inside than this number, it's 
	 * proclaimed as archive bomb and it's not uploaded.
	 * Default value is `1000`.
	 * @param  int          $archiveMaxLevels
	 * Maximum number of allowed ZIP archive levels inside.
	 * If uploaded archive contains another zip archive and
	 * those archive another and another, this is maximum
	 * level for nested ZIP archives. If Archive contains 
	 * more levels than this, it's proclaimed as archive 
	 * bomb and it's not uploaded. Default value is `3`.
	 * @param  float        $archiveMaxCompressPercentage
	 * Maximum archive compression percentage.
	 * If archive file has lower percentage size
	 * than all archive file items together, 
	 * it's proclaimed as archive bomb and it's 
	 * not uploaded. Default value is `10000`.
	 * @param  int          $pngImageMaxWidthHeight
	 * PNG image maximum width or maximum height.
	 * PNG images use ZIP compression and that's why 
	 * those images could be used as ZIP bombs.
	 * This limit helps to prevent file bombs 
	 * based on PNG images.
	 * @param  \string[]    $bombScanners
	 * Bomb scanner classes to scan uploaded files for file bombs.
	 * All classes in this list must implement interface:
	 * `\MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner`.
	 * 
	 * @param  string       $wrapper
	 * Html code wrapper, wrapper has to contain replacement in string 
	 * form: `{control}`. Around this substring you can wrap any HTML 
	 * code you want. Default wrapper values is: `'{control}'`.
	 * 
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function __construct(
		array $cfg = [],

		$name = NULL,
		$type = NULL,
		$fieldOrder = NULL,
		$value = NULL,
		$title = NULL,
		$translate = NULL,
		$translateTitle = NULL,
		array $cssClasses = [],
		array $controlAttrs = [],
		array $validators = [],

		$accessKey = NULL,
		$autoFocus = NULL,
		$disabled = NULL,
		$readOnly = NULL,
		$required = NULL,
		$tabIndex = NULL,

		$label = NULL,
		$translateLabel = TRUE,
		$labelSide = NULL,
		$renderMode = NULL,
		array $labelAttrs = [],
		
		$multiple = NULL,

		array $accept = [],
		$capture = NULL,
		$allowedFileNameChars = NULL,
		$minCount = NULL,
		$maxCount = NULL,
		$minSize = NULL,
		$maxSize = NULL,
		$archiveMaxItems = NULL,
		$archiveMaxLevels = NULL,
		$archiveMaxCompressPercentage = NULL,
		$pngImageMaxWidthHeight = NULL,
		array $bombScanners = [],

		$wrapper = NULL
	){
		$this->consolidateCfg($cfg, func_get_args(), func_num_args());
		parent::__construct($cfg);
	}

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
	 * @param  \MvcCore\Ext\Form $form
	 * @throws \InvalidArgumentException
	 * @return \MvcCore\Ext\Forms\Fields\Select
	 */
	public function SetForm (\MvcCore\Ext\IForm $form) {
		/** @var \MvcCore\Ext\Forms\Field $this */
		if ($this->form !== NULL) return $this;
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
				static::CONFIG_ERR_WRONG_FORM_ENCTYPE,
				[$multipartFormEnctype]
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
	 * @param  array $fieldPropsDefaultValidValues 
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
		/** @var \stdClass $templates */
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
	 * @param  int   $errorNumber
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	protected function throwConfigException ($errorNumber, $errorMsgArgs = []) {
		$errorMessage = static::$configErrorMessages[$errorNumber];
		$formViewClass = $this->form->GetViewClass();
		$errorMessage = $formViewClass::Format($errorMessage, $errorMsgArgs);
		$this->throwNewInvalidArgumentException(
			$errorMessage
		);
	}
}
