<?php
/**
 * @author: Nicolas Levée
 * @version 160520131907
 */

namespace Lightspeed;
use Lightspeed\Http\Response;

/**
 * Class Middleware
 * @package Lightspeed
 */
abstract class Middleware {

	/**
	 * @var App
	 */
	protected $application;

	/**
	 * @var App
	 */
	protected $next;


	/**
	 * Assign l'application au middleware
	 * @param App $app
	 */
	public function setApplication(App $app) {
		$this->application = $app;
	}

	/**
	 * Assign le prochain middleware à executer
	 * @param Middleware $middleware
	 */
	public function setNext(Middleware $middleware) {
		$this->next = $middleware;
	}


	/**
	 * Cette fonction est a définir dans le middleware pour l'execution de l'action principal du middleware
	 *
	 * @param Response $response
	 * @return mixed
	 */
	abstract public function call(Response &$response);

}