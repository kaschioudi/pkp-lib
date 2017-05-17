<?php 

/**
 * @file classes/services/OJSServiceProvider.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSServiceProvider
 * @ingroup services
 *
 * @brief Utility class to package all OJS services
 */

namespace App\Services;

require_once(dirname(__FILE__) . '/../../lib/vendor/pimple/pimple/src/Pimple/Container.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/pimple/pimple/src/Pimple/ServiceProviderInterface.php');

use \Pimple\Container;

class OJSServiceProvider implements \Pimple\ServiceProviderInterface {
	
	/**
	 * Registers services 
	 * @param Pimple\Container $pimple
	 */
	public function register(Container $pimple) {
		
		// Submission service
		$pimple['submission'] = function() {
			return new SubmissionService();
		};
		
		// Issue service
		$pimple['issue'] = function() {
			return new IssueService();
		};

		// File service
		$pimple['file'] = $pimple->protect(function($fileId) {
			return new FileService($fileId);
		});
	}
}