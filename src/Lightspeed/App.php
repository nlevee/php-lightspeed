<?php
/**
 * @author: Nicolas Levée
 * @version 160520131305
 */

namespace Lightspeed;

/**
 *
 */
use Lightspeed\Http\Request;
use Lightspeed\Http\Response;

/**
 * Appel de classe Exception
 */
require_once "Exception.php";

/**
 * Class App
 * @package Lightspeed
 */
class App {

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var array
	 */
	protected $middlewares = array();


	/**
	 * @param Request $request
	 */
	public function __construct(Request &$request = null) {
		$this->request = $request ?: new Request();
		$this->middlewares[0] = $this;
	}


	/**
	 * Renvoi l'instance request utilisé dans le dispatcher
	 * @return \Lightspeed\Http\Request
	 */
	public function getRequestHandler() {
		return $this->request;
	}

	/**
	 * Ajoute un middleware au lanceur
	 * @param Middleware $middleware
	 */
	public function prepend(Middleware $middleware) {
		$middleware->setApplication($this);
		$middleware->setNext($this->middlewares[0]);
		array_unshift($this->middlewares, $middleware);
	}

	/**
	 * lecture de la requete
	 * @param Response $response
	 */
	public function listen(Response &$response = null) {
		// demarrage des middlewares
		$response = $response ?: new Response();
		$this->middlewares[0]->call($response);
	}

	/**
	 * @param Response $response
	 */
	public function call(Response &$response) {
		// echo
		$response->flush();
	}

}