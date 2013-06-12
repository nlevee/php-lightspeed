<?php
/**
 * @author: Nicolas Levée
 * @version 160520131907
 */

namespace Lightspeed;
use Lightspeed\Http\Request;
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
	 * @var Request
	 */
	protected $request;

	/**
	 * @var App
	 */
	protected $next;


	/**
	 * Assign l'application au middleware
	 * @param App $app
	 */
	public function setApplication(App &$app) {
		$this->application = $app;
		$this->request = $this->application->getRequestHandler();
	}

	/**
	 * Assign le prochain middleware à executer
	 * @param Middleware|mixed $middleware
	 * @throws InvalidArgumentException
	 */
	public function setNext($middleware) {
		if (!method_exists($middleware, 'call'))
			throw new InvalidArgumentException("The middleware must have a method 'call'");
		$this->next = $middleware;
	}


	/**
	 * Arrete la suite d'appel de middleware
	 * @param Response $response
	 */
	protected function stop(Response &$response) {
		$this->application->call($response);
	}

	/**
	 * Appel la fonction de middleware suivante
	 * @param Response $response
	 */
	protected function next(Response &$response) {
		$this->next->call($response);
	}


	/**
	 * Cette fonction est a définir dans le middleware pour l'execution de l'action principal du middleware
	 *
	 * @param Response $response
	 * @return mixed
	 */
	abstract public function call(Response &$response);

}