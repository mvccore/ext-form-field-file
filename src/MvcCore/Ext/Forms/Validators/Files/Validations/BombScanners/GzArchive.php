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

class GzArchive implements \MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner {

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
	 * @var resource|FALSE
	 */
	protected $gz = NULL;

	/**
	 * @var \PharData|FALSE
	 */
	protected $phar = FALSE;

	/**
	 * @var string|NULL
	 */
	protected $pharFullPath = NULL;

	/**
	 * @var \PharFileInfo[]
	 */
	protected $files = [];

	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * @param  string $firstFourBytes
	 * @return bool
	 */
	static function MatchMagicBytes ($firstFourBytes) {
		return substr($firstFourBytes, 0, 3) === "\x1f\x8b\x08";
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
		return TRUE;
	}
	
	/**
	 * @return string
	 */
	static function GetNotSupportedError () {
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
		$this->fullPath = str_replace('\\', '/', $this->spl->getRealPath());
	}

	/**
	 * @return bool
	 */
	public function Open () {
		$this->gz = gzopen($this->fullPath, 'r');
		if ($this->gz === FALSE) return FALSE;

		$tmpDir = $this->validator->GetUploadsTmpDir();
		$entryNameBase = preg_replace('#[^A-Za-z0-9_\.]#', '', basename($this->fullPath));
		$itemDir = $tmpDir . '/' . $entryNameBase . '.' . uniqid();
		$itemDirCreated = mkdir($itemDir, 0600);
		if (!$itemDirCreated) return FALSE;

		$this->pharFullPath = $itemDir . '/' . $entryNameBase;
		$writePointer = fopen($this->pharFullPath, 'w');
		while (!gzeof($this->gz)) {
			$uncompressed = gzread($this->gz, 131072);
			fwrite($writePointer, $uncompressed, strlen($uncompressed));
		}
		fclose($writePointer);

		$equalFiles = $this->filesAreEqual($this->fullPath, $this->pharFullPath);
		if ($equalFiles) {
			unlink($this->pharFullPath);
			$this->pharFullPath = NULL;
			return FALSE;
		}

		try {
			$this->phar = new \PharData($this->pharFullPath, 
				\Phar::CURRENT_AS_FILEINFO | 
				\Phar::KEY_AS_FILENAME | 
				\Phar::SKIP_DOTS
			);
			$pharFiles = new \RecursiveIteratorIterator($this->phar, \RecursiveIteratorIterator::LEAVES_ONLY);
			/** @var \PharFileInfo $item */
			foreach ($pharFiles as $item) 
				$this->files[] = $item;
			$this->index = -1;
		} catch (\Throwable $e) {
		}
		
		return TRUE;
	}

	/**
	 * @return string
	 */
	public function GetError () {
		return "Uploaded file is inconsistent GZ archive (`{1}`).";
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
	public function Close () {
		gzclose($this->gz);
		if ($this->phar !== NULL) {
			unset($this->phar);
			unlink($this->pharFullPath);
			@rmdir(dirname($this->pharFullPath));
		}
	}

	/**
	 * @return bool
	 */
	public function Move () {
		if ($this->phar === NULL) {
			if ($this->index === 0) {
				$this->index = 1;
				return TRUE;
			}
			return FALSE;
		} else {
			$this->index++;
			if ($this->index < count($this->files)) 
				return TRUE;
			return FALSE;
		}
	}

	/**
	 * @return int
	 */
	public function GetEntrySize () {
		if ($this->phar === NULL) {
			$isize = 0;
			$this->spl->rewind();
			$this->spl->fseek(-4, SEEK_END);
			$isizeBinnary = (string) $this->spl->fread(4);
			// Gzip footer:
			$isizeUnpacked = unpack("V",$isizeBinnary);
			if (isset($isizeUnpacked[1]))
				$isize = $isizeUnpacked[1];
			return $isize;
		} else {
			return $this->files[$this->index]->getSize();
		}
	}

	/**
	 * @return string|NULL
	 */
	public function GetEntryName () {
		if ($this->phar === NULL) {
			return basename($this->fullPath);
		} else {
			$pharItem = $this->files[$this->index];
			//x($pharItem->getFilename());
			return $pharItem->getFilename();
		}
	}

	/**
	 * @param  string $destinationFullPath
	 * @return string|NULL
	 */
	public function ExtractEntry ($destinationFullPath) {
		if ($this->phar === NULL) {
			return NULL;// already extracted
		} else {
			$pharItem = $this->files[$this->index];
			$pharEntryFullPath = str_replace('\\', '/', $pharItem->getPathname());
			$readPointer = fopen($pharEntryFullPath, 'r');
			if (!$readPointer) 
				return NULL;
			$writePointer = fopen($destinationFullPath, 'w');
			while (!feof($readPointer))
				fwrite($writePointer, fread($readPointer, 131072));
			fclose($readPointer);
			fclose($writePointer);
			return $destinationFullPath;
		}
	}

	/**
	 * @param  string $a 
	 * @param  string $b 
	 * @return bool
	 */
	protected function filesAreEqual ($a, $b) {
		// Check if filesize is different
		if (filesize($a) !== filesize($b))
			return FALSE;
		// Check if content is different
		$ah = fopen($a, 'rb');
		$bh = fopen($b, 'rb');
		$result = true;
		while (!feof($ah)) {
			if (fread($ah, 131072) != fread($bh, 131072)) {
				$result = false;
				break;
			}
		}
		fclose($ah);
		fclose($bh);
		return $result;
	}
}