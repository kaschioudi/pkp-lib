<?php 

/**
 * @file classes/security/authorization/internal/ApiAuthorizationMiddleware.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApiAuthorizationMiddleware
 * @ingroup security_authorization
 *
 * @brief Slim middleware which enforces authorization policies
 */

class ApiAuthorizationMiddleware {
	
	/** @var APIHandler $handler Reference to api handler */
	protected $_handler = null;
	
	/**
	 * Constructor
	 * 
	 * @param APIHandler $handler
	 */
	public function __construct(APIHandler $handler) {
		$this->_handler = $handler;
	}
	
	/**
	 * Handles authorization 
	 * @param SlimRequest $slimRequest
	 * 
	 * @return boolean|string
	 */
	protected function _authorize($slimRequest) {
		// share SlimRequest with Handler
		$this->_handler->setSlimRequest($slimRequest);
		$request = $this->_handler->getRequest();
		$args = array($slimRequest);
		if ($this->_handler->authorize($request, $args, $this->_handler->getRoleAssignments())) {
			$this->_handler->validate($request, $args);
			$this->_handler->initialize($request, $args);
			return TRUE;
		} else {
			$authorizationMessage = $this->_handler->getLastAuthorizationMessage();
			if ($authorizationMessage == '') $authorizationMessage = 'user.authorization.accessDenied';
			$router = $request->getRouter();
			$result = $router->handleAuthorizationFailure($request, $authorizationMessage);
			if (is_string($result)) return $result;
			elseif (is_a($result, 'JSONMessage')) return $result->getString();
		}
	}
	
	/**
	 * Middleware invokable function
	 * 
	 * @param SlimRequest $request request
	 * @param SlimResponse $response response
	 * @param callable $next Next middleware
	 * @return boolean|string|unknown
	 */
	public function __invoke($request, $response, $next) {
		$result = $this->_authorize($request);
		if ($result !== TRUE) {
			return $result;
		}
		
		$response = $next($request, $response);
		return $response;
	}
}