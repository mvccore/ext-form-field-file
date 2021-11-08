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

namespace MvcCore\Ext\Forms\Validators\Files\Validations\BombScanners;

class ZipArchive implements \MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner {

	const ERR_NO_ZIP_EXT = "System extension to detect dangerous ZIP archives is not installed.";
	
	/**
	 * @var array
	 */
	protected static $zipErrors = [
		\ZipArchive::ER_INCONS	=> "Uploaded file is inconsistent ZIP archive (`{1}`).",
		\ZipArchive::ER_MEMORY	=> "Uploaded file caused memory allocation failure (`{1}`).",
		\ZipArchive::ER_NOZIP	=> "Uploaded file has archive header but it's not an archive (`{1}`).",
		\ZipArchive::ER_OPEN	=> "Uploaded file archive is not possible to open (`{1}`).",
		\ZipArchive::ER_READ	=> "Uploaded file archive is not possible to read (`{1}`).",
		\ZipArchive::ER_SEEK	=> "Uploaded file archive is not possible to seek (`{1}`).",
	];

	/**
	 * @var \MvcCore\Ext\Forms\Validators\Files
	 */
	protected $validator = NULL;

	/**
	 * @var \SplFileObject
	 */
	protected $spl = NULL;

	/**
	 * @var string
	 */
	protected $fullPath = NULL;

	/**
	 * @var \ZipArchive
	 */
	protected $zip = NULL;

	/**
	 * @var bool|int
	 */
	protected $openResult = NULL;

	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * @var array
	 */
	protected $entry = NULL;
	
	/**
	 * @param  string $firstFourBytes
	 * @return bool
	 */
	public static function MatchMagicBytes ($firstFourBytes) {
		return $firstFourBytes === "PK\3\4";
	}

	/**
	 * @return bool
	 */
	public static function IsArchive () {
		return TRUE; 
	}
	
	/**
	 * @return bool
	 */
	public static function IsSupported () {
		return extension_loaded('zip'); 
	}
	
	/**
	 * @return string
	 */
	public static function GetNotSupportedError () {
		return static::ERR_NO_ZIP_EXT;
	}

	/**
	 * @param  \MvcCore\Ext\Forms\Validators\Files $validator
	 * @param  \SplFileObject $spl
	 * @return void
	 */
	public function __construct (\MvcCore\Ext\Forms\Validators\IFiles $validator, \SplFileObject $spl) {
		$this->validator = $validator;
		$this->spl = $spl;
		$this->fullPath = str_replace('\\', '/', $this->spl->getRealPath());
		$this->zip = new \ZipArchive();
	}

	/**
	 * @return bool
	 */
	public function Open () {
		$this->openResult = $this->zip->open(
			$this->fullPath, \ZipArchive::CHECKCONS
		);
		$this->index = 0;
		return $this->openResult === TRUE;
	}

	/**
	 * @return string
	 */
	public function GetError () {
		if ($this->openResult === TRUE) return 0;
		return static::$zipErrors[$this->openResult];
	}

	/**
	 * @return int
	 */
	public function GetCompressedSize () {
		return $this->spl->getSize();
	}

	/**
	 * @return void
	 */
	public function Close() {
		$this->zip->close();
	}

	/**
	 * @return bool
	 */
	public function Move() {
		$moved = ($entry = $this->zip->statIndex($this->index++));
		if ($moved) {
			$this->entry = $entry;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @return int
	 */
	public function GetEntrySize () {
		return $this->entry['size'];
	}

	/**
	 * @return string
	 */
	public function GetEntryName () {
		return $this->entry['name'];
	}

	/**
	 * @param  string $destinationFullPath
	 * @return string|NULL
	 */
	public function ExtractEntry ($destinationFullPath) {
		$entryName = $this->entry['name'];
		$readPointer = $this->zip->getStream($entryName);
		// https://www.php.net/manual/en/wrappers.compression.php
		//$fullPath = $this->fullPath;
		//$readPointer = fopen("zip://{$fullPath}#{$entryName}", 'r');
		if (!$readPointer) 
			return NULL;
		$writePointer = fopen($destinationFullPath, 'w');
		while (!feof($readPointer))
			fwrite($writePointer, fread($readPointer, 1048576)); // 1 MB
		fclose($readPointer);
		fclose($writePointer);
		return $destinationFullPath;
	}
}
