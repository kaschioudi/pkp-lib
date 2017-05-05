<?php 

/**
 * @file classes/services/SubmissionService.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionService
 * @ingroup services
 *
 * @brief Helper class that encapsulates submission business logic
 */

namespace App\Services;

use \DBResultRange;
use \Application;
use \DAOResultFactory;
use \DAORegistry;

import('lib.pkp.classes.db.DBResultRange');

class SubmissionService {
	
	/**
	 * Constructor
	 */
	public function __construct() {}
	
	/**
	 * Get submissions
	 *
	 * @param int $contextId
	 * @param $args array {
	 * 		@option string orderColumn
	 * 		@option string orderDirection
	 * 		@option int assignedTo
	 * 		@option int|array statuses
	 * 		@option string searchPhrase
	 * 		@option int count
	 * 		@option int page
	 * }
	 * 
	 * @return array
	 */
	public function retrieveSubmissionList($contextId, $args = array()) {

		$defaultArgs = array(
			'orderColumn' => 'id',
			'orderDirection' => 'DESC',
			'assignedTo' => null,
			'statuses' => null,
			'searchPhrase' => null,
			'count' => 10,
			'page' => 1,
		);

		$args = array_merge($defaultArgs, $args);

		$submissionListQO = with(new QueryBuilders\SubmissionListQueryBuilder($contextId))
			->orderBy($args['orderColumn'], $args['orderDirection'])
			->assignedTo($args['assignedTo'])
			->filterByStatus($args['statuses'])
			->searchPhrase($args['searchPhrase'])
			->get();

		$range = new DBResultRange($count, $page);

		$submissionDao = Application::getSubmissionDAO();
		$result = $submissionDao->retrieveRange($submissionListQO->toSql(), $submissionListQO->getBindings(), $range);
		$queryResults = new DAOResultFactory($result, $submissionDao, '_fromRow');

		$items = array();
		$submissions = $queryResults->toArray();
		foreach($submissions as $submission) {
			$items[] = $submission;
		}

		$data = array(
			'items' => $items,
			'maxItems' => (int) $queryResults->getCount(),
			'page' => $queryResults->getPage(),
			'pageCount' => $queryResults->getPageCount(),
		);

		return $data;
	}

	/**
	 * Helper function to create a submission object
	 *
	 * @param int $contextId
	 * @param int $submissionId
	 * @throws Exceptions\InvalidSubmissionException
	 *
	 * @return Submission
	 */
	protected function getSubmission($contextId, $submissionId) {
		$submissionDao = Application::getSubmissionDAO();
		$submission = $submissionDao->getById($submissionId, $contextId);
		if (!$submission) {
			throw new Exceptions\InvalidSubmissionException($contextId, $submissionId);
		}
		return $submission;
	}

	/**
	 * Retrieve all submission files
	 *
	 * @param int $contextId
	 * @param int $submissionId
	 * @param int $fileStage Limit to a specific file stage
	 * @throws Exceptions\InvalidSubmissionException
	 *
	 * @return array
	 */
	public function getFiles($contextId, $submissionId, $fileStage = null) {
		$submission = $this->getSubmission($contextId, $submissionId);
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFiles = $submissionFileDao->getLatestRevisions($submission->getId(), $fileStage);
		return $submissionFiles;
	}
	
	/**
	 * Retrieve participants for a specific stage
	 *
	 * @param int $contextId
	 * @param int $submissionId
	 * @param int $stageId
	 * @throws Exceptions\InvalidSubmissionException
	 *
	 * @return array
	 */
	public function getParticipantsByStage($contextId, $submissionId, $stageId) {
		$submission = $this->getSubmission($contextId, $submissionId);
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
		$stageAssignments = $stageAssignmentDao->getBySubmissionAndStageId(
			$submission->getId(),
			$stageId
		);
	
		// Make a list of the active (non-reviewer) user groups.
		$userGroupIds = array();
		while ($stageAssignment = $stageAssignments->next()) {
			$userGroupIds[] = $stageAssignment->getUserGroupId();
		}
	
		// Fetch the desired user groups as objects.
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$result = array();
		$userGroups = $userGroupDao->getUserGroupsByStage(
			$contextId,
			$stageId
		);
	
		$userStageAssignmentDao = DAORegistry::getDAO('UserStageAssignmentDAO'); /* @var $userStageAssignmentDao UserStageAssignmentDAO */
	
		while ($userGroup = $userGroups->next()) {
			if ($userGroup->getRoleId() == ROLE_ID_REVIEWER) continue;
			if (!in_array($userGroup->getId(), $userGroupIds)) continue;
			$roleId = $userGroup->getRoleId();
			$users = $userStageAssignmentDao->getUsersBySubmissionAndStageId(
				$submission->getId(),
				$stageId,
				$userGroup->getId()
			);
			while($user = $users->next()) {
				$result[] = array(
					'roleId' 		=> $userGroup->getRoleId(),
					'roleName'		=> $userGroup->getLocalizedName(),
					'userId'		=> $user->getId(),
					'userFullName'	=> $user->getFullName(),
				);
			}
		}
	
		return $result;
	}

	/**
	 * Retrieve galley list
	 *
	 * @param int $contextId
	 * @param int $submissionId
	 * @throws Exceptions\SubmissionStageException
	 *
	 * @return array
	 */
	public function getGalleys($contextId, $submissionId) {
		$data = array();
		$submission = $this->getSubmission($contextId, $submissionId);
		$stageId = (int) $submission->getStageId();
		if ($stageId !== WORKFLOW_STAGE_ID_PRODUCTION) {
			throw new Exceptions\SubmissionStageNotValidException($contextId, $submissionId);
		}
		$galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
		$galleys = $galleyDao->getBySubmissionId($submission->getId());
		while ($galley = $galleys->next()) {
			$data[] = array(
				'id'				=> $galley->getId(),
				'submissionId'		=> $galley->getSubmissionId(),
				'locale'			=> $galley->getLocale(),
				'label'				=> $galley->getLabel(),
				'seq'				=> $galley->getSequence(),
				'remoteUrl'			=> $galley->getremoteUrl(),
				'fileId'			=> $galley->getFileId(),
			);
		}

		return $data;
	}
}