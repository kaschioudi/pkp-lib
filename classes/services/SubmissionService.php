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

		$submissionListQB = new QueryBuilders\SubmissionListQueryBuilder($contextId);
		$submissionListQB
			->orderBy($orderColumn, $orderDirection)
			->assignedTo($args['assignedTo'])
			->filterByStatus($args['statuses'])
			->searchPhrase($args['searchPhrase']);

		$submissionListQO = $submissionListQB->get();
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
	
}