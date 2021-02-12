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

class PngImage implements \MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner {

	const ERR_INVALID_PNG_FILE = "Uploaded file is inconsistent PNG image (`{1}`).";

	const ERR_INVALID_SIZES = "Uploaded file has invalid sizes (`{1}`).";
	
	/**
	 * @var \MvcCore\Ext\Forms\Validators\Files
	 */
	protected $validator = NULL;

	/**
	 * @var \SplFileObject
	 */
	protected $spl = NULL;

	/**
	 * @var bool
	 */
	protected $ihdrFound = FALSE;

	/**
	 * @var bool
	 */
	protected $invalidSizes = FALSE;
	
	/**
	 * @param  string $firstFourBytes
	 * @return bool
	 */
	public static function MatchMagicBytes ($firstFourBytes) {
		return $firstFourBytes === chr(0x89) . "PNG";
	}

	/**
	 * @return bool
	 */
	public static function IsArchive () {
		return FALSE; 
	}
	
    /**
	 * @return bool
	 */
	public static function IsSupported () {
		return TRUE;
	}
	
	/**
	 * @return string
	 */
	public static function GetNotSupportedError () {
		return '';
	}

	/**
	 * @param  \MvcCore\Ext\Forms\Validators\Files $validator
	 * @param  \SplFileObject $spl
	 * @return void
	 */
	public function __construct (\MvcCore\Ext\Forms\Validators\IFiles $validator, \SplFileObject $spl) {
		$this->validator = $validator;
		$this->spl = $spl;
	}

	/**
	 * @return bool
	 */
	public function Open () {
		$this->spl->rewind();
		$this->spl->fread(12);
		$ihdrBytes = (string) $this->spl->fread(4);
		
		$this->ihdrFound = $ihdrBytes === 'IHDR';
		
		if (!$this->ihdrFound) 
			return FALSE;
		
		// PNG stores width and height integers in big-endian
		$widthBinnary = (string) $this->spl->fread(4);
		$heightBinnary = (string) $this->spl->fread(4);
		$widthUnpacked = unpack('N', $widthBinnary);
		$heightUnpacked = unpack('N', $heightBinnary);
		$width = isset($widthUnpacked[1])
			? $widthUnpacked[1]
			: 0;
		$height = isset($heightUnpacked[1])
			? $heightUnpacked[1]
			: 0;
		
		$maxPngSize = $this->validator->GetPngImageMaxWidthHeight();
		$this->invalidSizes = ($width > $maxPngSize || $height > $maxPngSize);

		if ($this->invalidSizes)
			return FALSE;

		return TRUE;
	}

	/**
	 * @return int
	 */
	public function GetCompressedSize () {
		return $this->spl->getSize();
	}

	/**
	 * @return string
	 */
	public function GetError () {
		if (!$this->ihdrFound) 
			return static::ERR_INVALID_PNG_FILE;
		if ($this->invalidSizes)
			return static::ERR_INVALID_SIZES;
		return '';
	}

	/**
	 *
	 * @return void
	 */
	public function Close() {
		unset($this->spl);
	}

	/**
	 *
	 * @return boolean
	 */
	public function Move() {
		return FALSE;
	}

	/**
	 * @return int
	 */
	public function GetEntrySize () {
		return 0;
	}

	/**
	 * @return string
	 */
	public function GetEntryName () {
		return '';
	}

	/**
	 * @param  string $destinationFullPath
	 * @return string|NULL
	 */
	public function ExtractEntry ($destinationFullPath) {
		return NULL;
	}
}