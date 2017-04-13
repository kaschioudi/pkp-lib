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
 * @brief Query builder base class
 */

namespace App\Services\QueryBuilders;

use Illuminate\Database\Capsule\Manager as Capsule;
use \Config;

abstract class BaseQueryBuilder {

	/** @var object capsule  */
	protected $capsule = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->bootstrap();
	}

	/**
	 * bootstrap query builder
	 */
	protected function bootstrap() {
		$capsule = new Capsule;
		$capsule->addConnection(array(
				'driver'    => Config::getVar('database', 'driver'),
				'host'      => Config::getVar('database', 'host'),
				'database'  => Config::getVar('database', 'name'),
				'username'  => Config::getVar('database', 'username'),
				'password'  => Config::getVar('database', 'password'),
				'charset'   => Config::getVar('i18n', 'connection_charset'),
		));
		$capsule->setAsGlobal();
	}
}
