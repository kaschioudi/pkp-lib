<?php 

/**
 * @file classes/repositories/SubmissionRepositoryInterface.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @interface SubmissionRepositoryInterface
 * @ingroup repositories
 *
 * @brief Submission repository interface
 */

namespace PKP\Repositories;

use \Journal;
use \User;
use \Submission;

interface SubmissionRepositoryInterface {

	/**
	 * Create a submission
	 *
	 * @param Journal $journal
	 * @param User $user
	 * @param array $submissionData
	 * 		$submissionData['sectionId'] int
	 * 		$submissionData['locale'] string
	 * 		$submissionData['authorUserGroupId'] int
	 * 		$submissionData['commentsToEditor'] string
	 *
	 * @return Submission
	 */
	public function create(Journal $journal, User $user, $submissionData);

	/**
	 * Update submission
	 * @param Submission $submission
	 * @param array $submissionData
	 * 		$submissionData['step'] int
	 * 		$submissionData['language'] string
	 * 		$submissionData['locale'] string
	 * 		$submissionData['commentsToEditor'] string
	 */
	public function update(Submission $submission, $user, $submissionData);

// 	public function updateMetadata();
// 	public function delete();
// 	public function validate();
}