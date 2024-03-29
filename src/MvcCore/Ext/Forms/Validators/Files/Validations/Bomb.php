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

namespace MvcCore\Ext\Forms\Validators\Files\Validations;

use \MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner;

/**
 * Responsibility: Detect if uploaded file is not ZIP or PNG bomb.
 * @mixin \MvcCore\Ext\Forms\Validators\Files
 */
trait Bomb {

	/**
	 * Try to detect archive bomb if necessary.
	 * @param  string $tmpFullPath
	 * @param  string $uploadedFileName
	 * @param  int $level
	 * @return bool|NULL
	 */
	protected function validateBomb (& $file) {
		$recursiveInfo = (object) [
			'uploadedFileName'	=> $file->name,
			'entriesTotalCount'	=> 0,
			'entriesTotalSize'	=> 0,
			'allEntriesMaxSize'	=> NULL,
		];
		$oldMask = umask(0);
		$result = $this->validateBombRecursive(
			$file->tmpFullPath, $recursiveInfo
		);
		umask($oldMask);
		if (!$result->success) {
			$this->field->AddValidationError(
				isset($result->message)
					? $result->message
					: static::GetErrorMessage(static::UPLOAD_ERR_FILE_BOMB), 
				[$file->name]
			);
			return $this->removeAllTmpFiles();
		}
		return TRUE;
	}

	/**
	 * Try to detect file bomb recursively.
	 * @param  string    $fullPath 
	 * @param  \stdClass $recursiveInfo 
	 * @param  int       $level 
	 * @return \stdClass
	 */
	protected function validateBombRecursive (
		$fullPath, \stdClass & $recursiveInfo, $level = 0
	) {
		$possibleBombAdapter = $this->validateBombGetPossibleType($fullPath);
		if ($possibleBombAdapter === NULL)
			return (object) [
				'success' => TRUE // `TRUE` means no bomb
			];
		return $this->validateBombRecursiveArchive(
			$possibleBombAdapter, $recursiveInfo, $level
		);
	}
	
	/**
	 * Get possible file bomb type by first magic bytes.
	 * @param  string $fullPath 
	 * @return \MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner|NULL
	 */
	protected function validateBombGetPossibleType ($fullPath) {
		if ($this->bombScanners === NULL) return NULL;
		$spl = new \SplFileObject($fullPath);
		$spl->rewind();
		$firstFourBytes = $spl->fread(4);
		/** @var \MvcCore\Ext\Forms\Validators\Files\Validations\IBombScanner $bombScannerClass */
		foreach ($this->bombScanners as $bombScannerClass) 
			if ($bombScannerClass::MatchMagicBytes($firstFourBytes)) 
				return new $bombScannerClass($this, $spl);
		return NULL;
	}

	/**
	 * Try to detect ZIP bomb recursively.
	 * @param  IBombScanner $adapter 
	 * @param  \stdClass    $recursiveInfo 
	 * @param  int          $level 
	 * @return \stdClass
	 */
	protected function validateBombRecursiveArchive (
		IBombScanner $adapter, \stdClass & $recursiveInfo, $level
	) {
		if (!$adapter::IsSupported()) 
			return (object) [
				'success'	=> FALSE, // `FALSE` means bomb
				'message'	=> $adapter::GetNotSupportedError()
			];
		
		/**
		 * Open archive or return upload error 
		 * if opening is not possible.
		 */
		if (!$adapter->Open())
			return (object) [
				'success'	=> FALSE, // `FALSE` means bomb
				'message'	=> $adapter->GetError()
			];

		if (!$adapter::IsArchive()) 
			return (object) [
				'success'	=> TRUE, // PNG images are OK, `TRUE` means no bomb
				'message'	=> NULL,
			];

		// Complete archive uncompressed size:
		$compressedSize = $adapter->GetCompressedSize();

		// Complete ZIP file size from all ZIP archive items together:
		if ($level === 0)
			$recursiveInfo->allEntriesMaxSize = (
				$compressedSize * (100.0 / floatval($this->archiveMaxCompressPercentage))
			);

		/**
		 * Complete all nested items size recursivelly.
		 * If there is detected any ZIP bomb aspect, 
		 * stop the recursion.
		 */
		$bombDetection = $this->validateBombRecursiveArchiveEntriesSize(
			$adapter, $recursiveInfo, $level
		);

		// If any bomb type detected:
		if (
			$bombDetection->tooHighExpansion || 
			$bombDetection->tooManyLevels ||
			$bombDetection->tooManyFiles
		) {
			$msg = NULL;
			if ($bombDetection->tooHighExpansion) 
				$msg = static::GetErrorMessage(static::UPLOAD_ERR_FILE_BOMB_TOO_HIGH_COMPRESSION);
			if ($bombDetection->tooManyLevels) 
				$msg = static::GetErrorMessage(static::UPLOAD_ERR_FILE_BOMB_TOO_MANY_LEVELS);
			if ($bombDetection->tooManyFiles) 
				$msg = static::GetErrorMessage(static::UPLOAD_ERR_FILE_BOMB_TOO_MANY_FILES);
			return (object) [ // `FALSE` means bomb
				'success'	=> FALSE,
				'message'	=> $msg,
			];
		}

		// If all entries are emty files - it's not a bomb.
		if ($recursiveInfo->entriesTotalSize === 0) 
			return (object) [
				'success'	=> TRUE, // `TRUE` means no bomb
				'message'	=> NULL,
			];

		/**
		 * Archive is not bomb if it's compress 
		 * percentage is larger than 10% by default.
		 * If compress percentage is lower - it's 
		 * probably an archive bomb!
		 */
		$compressPercentage = (
			$compressedSize / $recursiveInfo->entriesTotalSize
		) * 100.0;
		$result = (
			$compressPercentage > $this->archiveMaxCompressPercentage
		);

		return (object) [
			'success'	=> $result, // `TRUE` means no bomb, `FALSE` means bomb
			'message'	=> !$result 
				? static::GetErrorMessage(static::UPLOAD_ERR_FILE_BOMB_TOO_HIGH_COMPRESSION) 
				: NULL,
		];
	}

	/**
	 * Complete all nested items size recursivelly.
	 * If there is detected any ZIP bomb aspect, 
	 * stop the recursion.
	 * @param  IBombScanner $adapter
	 * @param  \stdClass    $recursiveInfo 
	 * @param  int          $level 
	 * @return \stdClass
	 */
	protected function validateBombRecursiveArchiveEntriesSize (
		IBombScanner $adapter, \stdClass & $recursiveInfo, $level
	) {
		$bombDetection = (object) [
			'tooHighExpansion'	=> FALSE,
			'tooManyFiles'		=> FALSE,
			'tooManyLevels'		=> FALSE,
		];
		while ($adapter->Move()) {
			$recursiveInfo->entriesTotalCount++;
			if ($recursiveInfo->entriesTotalCount > $this->archiveMaxItems) {
				$bombDetection->tooManyFiles = TRUE;
				break;
			}
			$entrySize = $adapter->GetEntrySize();
			if ($recursiveInfo->entriesTotalSize + $entrySize > $recursiveInfo->allEntriesMaxSize) {
				$bombDetection->tooHighExpansion = TRUE;
				break;
			}
			$entryName = $adapter->GetEntryName();
			if ($entryName === NULL) continue;
			$entryNameBase = preg_replace('#[^A-Za-z0-9_\.]#', '', basename($entryName));
			$itemDir = $this->uploadsTmpDir . '/' . $entryNameBase . '.' . uniqid();
			$itemDirCreated = mkdir($itemDir, 0600);
			$itemFullPath = $itemDir . '/' . $entryNameBase;
			if ($itemDirCreated) {
				$extractedItemFullPath = $adapter->ExtractEntry($itemFullPath);
				if ($extractedItemFullPath === NULL) {
					$recursiveInfo->entriesTotalSize += $entrySize;
				} else {
					$recursiveInfo->entriesTotalSize += filesize($extractedItemFullPath);
					$possibleBombAdapter = $this->validateBombGetPossibleType($extractedItemFullPath);
					$bombRecursionResult = (object) [
						'success'	=> TRUE,// `TRUE` means no bomb
					];
					if ($possibleBombAdapter !== NULL) {
						if ($level + 1 === $this->archiveMaxLevels) {
							$bombDetection->tooManyLevels = TRUE;
							$this->removeItemWithDir($extractedItemFullPath);
							break;
						}
						$bombRecursionResult = $this->validateBombRecursiveArchive(
							$possibleBombAdapter, $recursiveInfo, $level + 1
						);
					}
					$this->removeItemWithDir($extractedItemFullPath);
					if (!$bombRecursionResult->success) {
						$bombDetection->tooManyLevels = TRUE;
						break;
					}
				}
			}
		}
		// Close archive object before any return.
		$adapter->Close();
		return $bombDetection;
	}

	/**
	 * @param  string $fullPath 
	 * @return void
	 */
	protected function removeItemWithDir ($itemFullPath) {
		unlink($itemFullPath);
		clearstatcache(TRUE, $itemFullPath);
		$lastSlashPos = mb_strrpos($itemFullPath, '/');
		if ($lastSlashPos === FALSE) return;
		$dirFullPath = mb_substr($itemFullPath, 0, $lastSlashPos);
		$dirRemoved = @rmdir($dirFullPath);
		if (!$dirRemoved && function_exists('shell_exec')) {
			$cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
				? "rmdir /Q /S \"{$dirFullPath}\""
				: "rm -rf \"{$dirFullPath}\"";
			shell_exec($cmd);
		}
	}
}
