<?php 

/**
 * @file classes/services/queryBuilders/BaseQueryBuilder.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObject
 * @ingroup services
 *
 * @brief Submission list Query builder
 */

namespace App\Services\QueryBuilders;

use \QB;

class SubmissionListQueryBuilder extends BaseQueryBuilder {

	/** @var int Context ID */
	protected $contextId = null;

	/** @var string order by column */
	protected $orderColumn = 's.date_submitted';

	/** @var string order by direction */
	protected $orderDirection = 'DESC';

	/** @var array list of statuses */
	protected $statuses = null;

	/** @var int user ID */
	protected $assigneeId = null;

	/** @var string search phrase */
	protected $searchPhrase = null;

	/**
	 * Constructor
	 * 
	 * @param int $contextId context ID
	 */
	public function __construct($contextId) {
		parent::__construct();
		$this->contextId = $contextId;
	}

	/**
	 * Set result order column and direction
	 * 
	 * @param string $column
	 * @param string $direction
	 * 
	 * @return \App\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function orderBy($column, $direction = 'DESC') {
		if ($column === 'id') {
			$column = 'submission_id';
		}
		elseif ($column === 'lastModified') {
			$column = 'last_modified';
		}
		$this->orderColumn = "s.{$column}";
		$this->orderDirection = $direction;
		return $this;
	}

	/**
	 * Set statuses filter
	 * 
	 * @param int|array $statuses
	 * 
	 * @return \App\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function filterByStatus($statuses) {
		if (!is_array($statuses)) {
			$statuses = array($statuses);
		}
		$this->statuses = $statuses;
		return $this;
	}

	/**
	 * Limit results to a specific user's submissions
	 * 
	 * @param int $assigneeId
	 * 
	 * @return \App\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function assignedTo($assigneeId) {
		$this->assigneeId = (int) $assigneeId;
		return $this;
	}

	/**
	 * Filter by entries not assigned 
	 * 
	 * @return \App\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function notAssigned() {
		$this->assigneeId = -1;
		return $this;
	}

	/**
	 * Set query search phrase
	 * 
	 * @param string $phrase
	 * 
	 * @return \App\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function searchPhrase($phrase) {
		$this->searchPhrase = $phrase;
		return $this;
	}

	/**
	 * Execute query builder
	 * 
	 * @return object Query object
	 */
	public function get() {
		$q = QB::table(array('submissions' => 's'))
				->select('s.*')
				->where('s.context_id','=',$this->contextId)
				->orderBy($this->orderColumn, $this->orderDirection)
				->groupBy('s.submission_id');

		// statuses
		if (!is_null($this->statuses)) {
			if (in_array(STATUS_PUBLISHED, $this->statuses)) {
				$q->select('ps.date_published')
					->leftJoin(array('published_submissions', 'ps'),'ps.submission_id','=','s.submission_id')
					->groupBy('ps.date_published');
			}
			$q->whereIn('s.status', $this->statuses);
		}

		// assigned to
		if (!is_null($this->assigneeId) && ($this->assigneeId !== -1)) {
			$assigneeId = $this->assigneeId;
			// Stage assignments
			$q->leftJoin(array('stage_assignments','sa'), function($table) use ($assigneeId) {
				$table->on('s.submission_id', '=', 'sa.submission_id');
				$table->on('sa.user_id', '=', QB::raw($assigneeId));
			});
			// sa2 to prevent dupes
			$q->leftJoin(array('stage_assignments','sa2'), function($table) use ($assigneeId) {
				$table->on('s.submission_id', '=', 'sa2.submission_id');
				$table->on('sa.user_id', '=', QB::raw($assigneeId));
				$table->on('sa2.stage_assignment_id', '>', 'sa.stage_assignment_id');
			});
		}
		elseif ($this->assigneeId === -1) {
			$sub = QB::table('stage_assignments')
					->select(QB::Raw('count(stage_assignments.stage_assignment_id)'))
					->leftJoin('user_groups','stage_assignments.user_group_id','=','user_groups.user_group_id')
					->where('stage_assignments.submission_id','=','s.submission_id')
					->whereIn('user_groups.role_id', array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR));
	
			$q->whereNotNull('s.date_submitted');
			$q->where(QB::raw(QB::subQuery($sub) . ' = 0'));
		}

		// search phrase
		if (!is_null($this->searchPhrase)) {
			$words = explode(' ', $this->searchPhrase);
			if (count($words)) {
				$q->leftJoin(array('submission_settings','ss'),'s.submission_id','=','ss.submission_id')
					->leftJoin(array('authors','au'),'s.submission_id','=','au.submission_id');

				foreach ($words as $word) {
					$q->where(function($q) use ($word)  {
						$q->where(function($q) use ($word) {
							$q->where('ss.setting_name', 'title');
							$q->where('ss.setting_value', 'LIKE', "%{$word}%");
						});
						$q->orWhere(function($q) use ($word) {
							$q->where('au.first_name', 'LIKE', "%{$word}%");
							$q->orWhere('au.middle_name', 'LIKE', "%{$word}%");
							$q->orWhere('au.last_name', 'LIKE', "%{$word}%");
						});
					});
				}
	
			}
		}

		return $q->getQuery();
	}
}
