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

namespace MvcCore\Ext\Forms\Validators\Files;

/**
 * @mixin \MvcCore\Ext\Forms\Validators\Files
 */
trait ConfigGettersSetters {

	/**
	 * @inheritDocs
	 * @return callable|NULL
	 */
	public function GetAllowedFileNameCharsHandler () {
		return $this->allowedFileNameCharsHandler;
	}

	/**
	 * @inheritDocs
	 * @param  callable|NULL $allowedFileNameCharsHandler
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetAllowedFileNameCharsHandler ($allowedFileNameCharsHandler) {
		$this->allowedFileNameCharsHandler = $allowedFileNameCharsHandler;
		return $this;
	}


	/**
	 * @inheritDocs
	 * @return string|NULL
	 */
	public function GetAllowedFileNameChars () {
		return $this->allowedFileNameChars;
	}

	/**
	 * @inheritDocs
	 * @param  string|NULL $allowedFileNameChars
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetAllowedFileNameChars ($allowedFileNameChars) {
		$this->allowedFileNameChars = $allowedFileNameChars;
		return $this;
	}


	/**
	 * @inheritDocs
	 * @param  int $archiveMaxItems Default `1000`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxItems ($archiveMaxItems = 1000) {
		$this->archiveMaxItems = $archiveMaxItems;
		return $this;
	}
	
	/**
	 * @inheritDocs
	 * @return int
	 */
	public function GetArchiveMaxItems () {
		return $this->archiveMaxItems;
	}


	/**
	 * @inheritDocs
	 * @param  int $archiveMaxLevels Default `3`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxLevels ($archiveMaxLevels = 3) {
		$this->archiveMaxLevels = $archiveMaxLevels;
		return $this;
	}
	
	/**
	 * @inheritDocs
	 * @return int
	 */
	public function GetArchiveMaxLevels () {
		return $this->archiveMaxLevels;
	}


	/**
	 * @inheritDocs
	 * @param  float $archiveMaxCompressPercentage Default `5.0`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetArchiveMaxCompressPercentage ($archiveMaxCompressPercentage = 5.0) {
		$this->archiveMaxCompressPercentage = $archiveMaxCompressPercentage;
		return $this;
	}
	
	/**
	 * @inheritDocs
	 * @return float
	 */
	public function GetArchiveMaxCompressPercentage () {
		return $this->archiveMaxCompressPercentage;
	}


	/**
	 * @inheritDocs
	 * @param  int $pngImageMaxWidthHeight Default `10000`.
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetPngImageMaxWidthHeight ($pngImageMaxWidthHeight = 10000) {
		$this->pngImageMaxWidthHeight = $pngImageMaxWidthHeight;
		return $this;
	}
	
	/**
	 * @inheritDocs
	 * @return int
	 */
	public function GetPngImageMaxWidthHeight () {
		return $this->pngImageMaxWidthHeight;
	}


	/**
	 * @inheritDocs
	 * @param  \string[] $bombScannerClasses,...
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function AddBombScanners () {
		$args = func_get_args();
		$bombScannerClasses = (count($args) === 1 && is_array($args))
			? $args[0]
			: $args;
		if ($this->bombScanners === null)
			$this->bombScanners = [];
		foreach ($bombScannerClasses as $bombScannerClass)
			if (!in_array($bombScannerClass, $this->bombScanners, TRUE))
				$this->bombScanners[] = $bombScannerClass;
		return $this;
	}

	/**
	 * @inheritDocs
	 * @return \string[]|NULL
	 */
	public function GetBombScanners () {
		return $this->bombScanners;
	}
	
	/**
	 * @inheritDocs
	 * @param  \string[] $bombScannerClasses
	 * @return \MvcCore\Ext\Forms\Validators\Files
	 */
	public function SetBombScanners () {
		$args = func_get_args();
		if (count($args) === 1 && is_array($args[0])) {
			$this->bombScanners = $args[0];
		} else {
			$this->bombScanners = $args;
		}
		return $this;
	}

}