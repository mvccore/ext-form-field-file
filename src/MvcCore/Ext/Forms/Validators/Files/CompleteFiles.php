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

namespace MvcCore\Ext\Forms\Validators\Files;

/**
 * Responsibility: Complete uploaded files from request object.
 */
trait CompleteFiles
{
	/**
	 * Complete files array from global `$_FILES` stored in request object.
	 * @return bool|NULL
	 */
	protected function completeFiles () {
		
		$this->files = [];
		$filesFieldItems = $this->form
			->GetRequest()
			->GetFile($this->field->GetName());
		
		if (!$filesFieldItems) 
			return NULL;

		$this->completeFilesArray($filesFieldItems);
		
		$filesCount = count($this->files);
		
		if ($this->minCount !== NULL && $filesCount < $this->minCount) 
			return $this->handleUploadError(
				static::UPLOAD_ERR_MIN_FILES, [$this->minCount]
			);
		
		if ($this->maxCount !== NULL && $filesCount > $this->maxCount) 
			return $this->handleUploadError(
				static::UPLOAD_ERR_MAX_FILES, [$this->maxCount]
			);
		
		if ($filesCount > 0) 
			return TRUE;
		
		return $this->handleUploadError(UPLOAD_ERR_NO_FILE);
	}

	/**
	 * Complete local `$this->files` array from given 
	 * array from request object global `$_FILES`.
	 * @param array $filesFieldItems 
	 * @return void
	 */
	protected function completeFilesArray (array & $filesFieldItems) {
		if ($this->multiple) {
			foreach ($filesFieldItems['name'] as $index => $fileName) {
				$this->files[] = (object) [
					'name'			=> basename($fileName),
					'type'			=> $filesFieldItems['type'][$index],
					'tmpFullPath'	=> $filesFieldItems['tmp_name'][$index],
					'error'			=> $filesFieldItems['error'][$index],
					'size'			=> $filesFieldItems['size'][$index],
					//'extension' is completed later in `$this->validateSanitizeFileNameAndAddFileExt();`
				];
			}
		} else {
			$this->files[] = (object) [
				'name'			=> basename($filesFieldItems['name']),
				'type'			=> $filesFieldItems['type'],
				'tmpFullPath'	=> $filesFieldItems['tmp_name'],
				'error'			=> $filesFieldItems['error'],
				'size'			=> $filesFieldItems['size'],
				//'extension' is completed later in `$this->validateSanitizeFileNameAndAddFileExt();`
			];
		}
	}
}
