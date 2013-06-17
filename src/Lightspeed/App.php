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
	 * @var Share[]
	 */
	protected $shared_service = array();

	/**
	 * @var array[]
	 */
	protected $shared_service_opts = array();


	/**
	 * @param Request $request
	 */
	public function __construct(Request &$request = null) {
		$this->request = $request ?: new Request();
		$this->middlewares[0] = $this;
	}


	/**
	 * Renvoi l'instance request utilisé dans le dispatcher
	 * @return Request
	 */
	public function getRequestHandler() {
		return $this->request;
	}

	/**
	 * Ajoute un service au referentiel de partage de l'application
	 * Le service ne sera instancier qu'a l'appel de ce meme service via getShare(service_name)
	 * @param string $service_name
	 * @param Share $oShare
	 * @param array $options
	 */
	public function shareAs($service_name, Share $oShare, array $options = array()) {
		$this->shared_service[$service_name] = $oShare;
		$this->shared_service_opts[$service_name] = $options;
	}

	/**
	 * @param $service_name
	 * @return null
	 */
	public function getShare($service_name) {
		if(isset($this->shared_service[$service_name]))
			return $this->shared_service[$service_name]->getInstance($this->shared_service_opts[$service_name]);
		return null;
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
		$response->flush($this->request);
	}
}