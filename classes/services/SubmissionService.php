<?php 

/**
 * @file classes/services/SubmissionService.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObject
 * @ingroup services
 *
 * @brief Helper class that encapsulates submission business logic
 */

namespace App\Services;

class SubmissionService {
	
	/**
	 * Constructor
	 */
	public function __construct() {}
	
	/**
	 * Submission service method
	 */
	public function makeItFly() {
		error_log("Alright.. let's make submission (#{$this->submissionId}) fly!!!!".PHP_EOL,3,'/tmp/pimple.out');
	}
	
	/**
	 * Another submission service method
	 * @param int $fileId ID of file to attach
	 */
	public function attachFile($submissionId, $fileId) {
		error_log("Attaching File (#{$fileId}) to submission (#{$submissionId}) !!!!".PHP_EOL,3,'/tmp/pimple.out');
	}
	
}