<?php
/**
 * @file controllers/list/submissions/SubmissionsListHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListHandler
 * @ingroup classes_controllers_list
 *
 * @brief Instantiates and manages a UI component to list submissions.
 */
import('lib.pkp.controllers.list.ListHandler');
import('lib.pkp.classes.db.DBResultRange');
import('lib.pkp.classes.submission.Submission');

class SubmissionsListHandler extends ListHandler {

	/**
	 * Count of items to retrieve in initial page/request
	 *
	 * @param int
	 */
	public $_count = 20;

	/**
	 * Query parameters to pass with every GET request
	 *
	 * @param array
	 */
	public $_getParams = array();

	/**
	 * API endpoint path
	 *
	 * Used to generate URLs to API endpoints for this component.
	 *
	 * @param string
	 */
	public $_apiPath = '_submissions';

	/**
	 * Initialize the handler with config parameters
	 *
	 * @param array $args Configuration params
	 */
	public function init( $args = array() ) {
		parent::init($args);

		$this->_count = isset($args['count']) ? (int) $args['count'] : $this->_count;
		$this->_getParams = isset($args['getParams']) ? $args['getParams'] : $this->_getParams;
	}

	/**
	 * Retrieve the configuration data to be used when initializing this
	 * handler on the frontend
	 *
	 * @return array Configuration data
	 */
	public function getConfig() {

		$request = Application::getRequest();

		$config = array();

		if ($this->_lazyLoad) {
			$config['lazyLoad'] = true;
		} else {
			$config['collection'] = $this->getItems();
		}

		// URL to add a new submission
		$config['addUrl'] = $request->getDispatcher()->url(
			$request,
			ROUTE_PAGE,
			null,
			'submission',
			'wizard'
		);

		// URL to view info center for a submission
		$config['infoUrl'] = $request->getDispatcher()->url(
			$request,
			ROUTE_COMPONENT,
			null,
			'informationCenter.SubmissionInformationCenterHandler',
			'viewInformationCenter',
			null,
			array('submissionId' => '__id__')
		);

		$config['apiPath'] = $this->_apiPath;

		$config['count'] = $this->_count;
		$config['page'] = 1;

		$config['getParams'] = $this->_getParams;

		// Load grid localisation files
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_GRID);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);

		$config['i18n'] = array(
			'id' => __('common.id'),
			'title' => __($this->_title),
			'add' => __('submission.submit.newSubmissionSingle'),
			'search' => __('common.search'),
			'clearSearch' => __('submission.list.clearSearch'),
			'itemCount' => __('submission.list.count'),
			'itemsOfTotal' => __('submission.list.itemsOfTotal'),
			'loadMore' => __('grid.action.moreItems'),
			'loading' => __('common.loading'),
			'incomplete' => __('submissions.incomplete'),
			'delete' => __('common.delete'),
			'infoCenter' => __('submission.list.infoCenter'),
			'yes' => __('common.yes'),
			'no' => __('common.no'),
			'deleting' => __('common.deleting'),
			'confirmDelete' => __('submission.list.confirmDelete'),
			'responseDue' => __('submission.list.responseDue'),
			'reviewDue' => __('submission.list.reviewDue'),
			'filter' => __('submission.list.filter'),
			'filterRemove' => __('submission.list.filterRemove'),
			'itemOrdererUp' => __('submission.list.itemOrdererUp'),
			'itemOrdererDown' => __('submission.list.itemOrdererDown'),
			'reviewsCompleted' => __('submission.list.reviewsCompleted'),
			'filesPrepared' => __('submission.list.filesPrepared'),
			'discussions' => __('submission.list.discussions'),
			'incompleteSubmissionNotice' => __('submission.list.incompleteSubmissionNotice'),
		);

		// Attach a CSRF token for post requests
		$config['csrfToken'] = $request->getSession()->getCSRFToken();

		return $config;
	}

	/**
	 * Helper function to retrieve items
	 *
	 * @return array Items requested
	 */
	public function getItems() {

		$context = Application::getRequest()->getContext();
		$contextId = $context ? $context->getId() : 0;

		$params = array_merge(
			array(
				'count' => $this->_count,
				'offset' => 0,
			),
			$this->_getParams
		);

		import('classes.core.ServicesContainer');
		return ServicesContainer::instance()
				->get('submission')
				->getSubmissionList($contextId, $params);
	}
}
