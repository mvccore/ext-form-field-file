# MvcCore - Extension - Form - Field - File

[![Latest Stable Version](https://img.shields.io/badge/Stable-v5.1.4-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-form-field-file/releases)
[![License](https://img.shields.io/badge/License-BSD%203-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.4-brightgreen.svg?style=plastic)

MvcCore form extension with `input` type `file` and file(s) upload validation.

This upload has no backward compatible javascript or flash inside. It's strictly 
HTML5 with no additional info displaying. You can extend this field to do it.


## Installation
```shell
composer require mvccore/ext-form-field-file
```

## Fields And Default Validators
- `input:file`
	- `Files`
		- **configured by default**
		- validate submitted file or multiple files by checking:
			- build in PHP upload errors (max. POST size atc...)
			- if file is not any system file and if it is realy uploaded file (`is_uploaded_file()`, is_file()`, `filesize()`)
			- allowed characters in filename, this validator automaticly sanitize uploaded filename every time
			- `accept` attribute with allowed mime types by uploaded file(s) magic bytes
			  (or by extension in `accept` attribute, converted on server side to mime type to check magic bytes)
			- ZIP/TAR.GZ/PNG file bombs
			  
## Features
- always server side checked attributes `required`, `disabled` and `readonly`
- all HTML5 specific and global atributes (by [Mozilla Development Network Docs](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference))
- every field has it's build-in specific validator described above
- every build-in validator adds form error (when necessary) into session
  and than all errors are displayed/rendered and cleared from session on error page, 
  where user is redirected after submit
- any field is possible to render naturally or with custom template for specific field class/instance
- very extensible field classes - every field has public template methods:
	- `SetForm()`		- called immediatelly after field instance is added into form instance
	- `PreDispatch()`	- called immediatelly before any field instance rendering type
	- `Render()`		- called on every instance in form instance rendering process
		- submethods: `RenderNaturally()`, `RenderTemplate()`, `RenderControl()`, `RenderLabel()` ...
	- `Submit()`		- called on every instance when form is submitted

## Basic Example

```php
$form = (new \MvcCore\Ext\Form($controller))->SetId('demo');
...
$photos = new \MvcCore\Ext\Forms\Fields\Time([
	'name'		=> 'photos',
	'label'		=> 'Add your photos:',
	'accept'	=> 'image/*',
	'maxCount'	=> 5, // max. uploaded photos
	'maxSize'	=> 2097152, // max. 2 MB in binary for one item
]);
...
$form->AddFields($photos);
```

## TODO
- implement RAR file bombs detection and bz2 file bombs