<?php

/**
 * @file classes/services/FileService.php
*
* Copyright (c) 2014-2017 Simon Fraser University
* Copyright (c) 2000-2017 John Willinsky
* Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
*
* @class FileService
* @ingroup services
*
* @brief Helper class that encapsulates file business logic
*/

namespace App\Services;

class FileService {

	/** @var int $fileId File ID */
	protected $fileId = null;

	/**
	 * Constructor
	 * @param int $fileId
	 */
	public function __construct($fileId) {
		$this->fileId = $fileId;
	}

	/**
	 * File service method
	 */
	public function rename($name) {
		error_log("Getting ready to rename file (#{$this->fileId}) as {$name}".PHP_EOL,3,'/tmp/pimple.out');
	}

}