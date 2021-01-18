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

interface IBombScanner {

	/**
	 * Return `TRUE` if given first 4 file 
	 * magic bytes belongs to the file type.
	 * @param string $firstFourBytes
	 * @return bool
	 */
	public static function MatchMagicBytes ($firstFourBytes);
	
	/**
	 * Return `TRUE`, if bomb validator validates archive file.
	 * @return bool
	 */
	public static function IsArchive();
	
	/**
	 * Return if required PHP extension(s) for validation is/are installed.
	 * @return bool
	 */
	public static function IsSupported();
	
	/**
	 * Return error message when required extension(s) is/are not installed.
	 * @return string
	 */
	public static function GetNotSupportedError();
	
	/**
	 * Create and instance of uploaded file or file extracted 
	 * from any uploaded archove or sub-archive.
	 * @param \MvcCore\Ext\Forms\Validators\Files $validator
	 * @param \SplFileObject $spl
	 * @return void
	 */
	public function __construct (\MvcCore\Ext\Forms\Validators\IFiles $validator, \SplFileObject $spl);
	
	/**
	 * Open archive and return success if archive is OK.
	 * For iage bombs - open and check the image.
	 * @return bool
	 */
	public function Open ();
	
	/**
	 * Return any error thrown when openning an archive or checking an image.
	 * @return string
	 */
	public function GetError ();
	
	/**
	 * Return files size by `\SplFileObject` instance.
	 * @return int
	 */
	public function GetCompressedSize ();
	
	/**
	 * Close the archive pointer.
	 * @return void
	 */
	public function Close ();
	
	/**
	 * Move into next entry in archive.
	 * @return boolean
	 */
	public function Move ();
	
	/**
	 * Return archive entry size (in bytes).
	 * @return int
	 */
	public function GetEntrySize ();
	
	/**
	 * Return archive entry name (relative path).
	 * @return string
	 */
	public function GetEntryName ();
	
	/**
	 * Extract archive entry into given full path.
	 * @param string $destinationFullPath
	 * @return string|NULL
	 */
	public function ExtractEntry ($destinationFullPath);
}
