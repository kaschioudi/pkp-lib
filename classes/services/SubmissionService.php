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

use \DBResultRange;
use \Application;
use \DAOResultFactory;

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
	 * @param string $orderColumn
	 * @param string $orderDirection
	 * @param int $count
	 * @param int $page
	 * @param int $assignedTo
	 * @param int|array $statuses
	 * @param string $searchPhrase
	 *
	 * @return array
	 */
	public function retrieveSubmissionList($contextId, $orderColumn = 'id', $orderDirection = 'DESC', $count = 10, $page = 1, 
			$assignedTo = null, $statuses = null, $searchPhrase = null) {

		$submissionListQB = new QueryBuilders\SubmissionListQueryBuilder($contextId);
		$submissionListQB->orderBy($orderColumn, $orderDirection);

		if (!is_null(($assignedTo))) {
			$submissionListQB->assignedTo($assignedTo);
		}

		if (!is_null($statuses)) {
			$submissionListQB->filterByStatus($statuses);
		}

		if (!is_null($searchPhrase)) {
			$submissionListQB->searchPhrase($searchPhrase);
		}

		$submissionListQO = $submissionListQB->get();
		$range = new DBResultRange($count, $page);

		$submissionDao = Application::getSubmissionDAO();
		$result = $submissionDao->retrieveRange($submissionListQO->getSql(), $submissionListQO->getBindings(), $range);
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