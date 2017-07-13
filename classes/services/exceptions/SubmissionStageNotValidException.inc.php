<?php 
/**
 * @file classes/services/exceptions/SubmissionStageNotValidException.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionStageNotValidException
 * @ingroup services_exceptions
 *
 * @brief Invalid submission stage exception class
 */

namespace PKP\Services\Exceptions;

class SubmissionStageNotValidException extends ServiceException {

	/** @var int|null Submission ID */
	protected $_submissionId = null;

	/**
	 * Constructor
	 *
	 * @param $contextId int Context ID
	 * @param $submissionId int Submission ID
	 */
	public function __construct ($contextId, $submissionId) {
		$this->_submissionId = $submissionId;
		parent::__construct($contextId, "Invalid submission stage");
	}

	/**
	 * Return the submission ID
	 *
	 * @return int
	 */
	public function getSubmissionId() {
		return $this->_submissionId;
	}
}