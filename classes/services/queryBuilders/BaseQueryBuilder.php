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

use \Config;

abstract class BaseQueryBuilder {

	/** @var array connection configuration */
	protected $config = null;

	/** @var object connection  */
	protected $dbconn = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$driver = Config::getVar('database', 'driver');
		
		$this->config = array (
			'driver'    => $driver,
			'host'      => Config::getVar('database', 'host'),
			'database'  => Config::getVar('database', 'name'),
			'username'  => Config::getVar('database', 'username'),
			'password'  => Config::getVar('database', 'password'),
			'charset'   => Config::getVar('i18n', 'connection_charset'),
		);

		$this->dbconn = new \Pixie\Connection($driver, $this->config, 'QB');
	}
}
