<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view 
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Forms\Validators\Files\Validations\BombScanners;

/**
 * @todo Try to read rar entries with: https://github.com/selective-php/rar/blob/master/src/RarFileReader.php
 */
class RarArchive implements \MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner {

	/**
	 * @param string $firstFourBytes
	 * @return bool
	 */
	static function MatchMagicBytes ($firstFourBytes) {
		return $firstFourBytes === "\x52\x61\x72\x21"; // Rar!
	}
	
	/**
	 * @return bool
	 */
	static function IsArchive () {
		return TRUE;
	}
	
	/**
	 * @return bool
	 */
	static function IsSupported () {
		
	}

	/**
	 * @return string
	 */
	static function GetNotSupportedError () {
		
	}

	/**
	 * @param \MvcCore\Ext\Forms\Validators\Files $validator 
	 * @param \SplFileObject $spl 
	 * @return void
	 */
	public function __construct (\MvcCore\Ext\Forms\Validators\IFiles $validator, \SplFileObject $spl) {
	}

	/**
	 * @return bool
	 */
	public function Open () {
	}

	/**
	 * @return string
	 */
	public function GetError () {
	}

	/**
	 * @return int
	 */
	public function GetCompressedSize () {
	}

	/**
	 * @return void
	 */
	public function Close () {
	}

	/**
	 * @return boolean
	 */
	public function Move () {
	}

	/**
	 * @return int
	 */
	public function GetEntrySize () {
	}

	/**
	 * @return string
	 */
	public function GetEntryName () {
	}

	/**
	 * @param string $destinationFullPath
	 * @return string|NULL
	 */
	public function ExtractEntry ($destinationFullPath) {
		return NULL;
	}
}