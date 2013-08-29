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
	 * @var Middleware
	 */
	protected $middlewares_start;

	/**
	 * @var Middleware
	 */
	protected $middlewares_prev;

	/**
	 * @var mixed[]
	 */
	protected $shared_service = array();


	/**
	 * @param Request $request
	 */
	public function __construct(Request &$request = null) {
		$this->request = $request ?: new Request();
		$this->middlewares_start = &$this;
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
	 * @param mixed $oShare
	 */
	public function shareAs($service_name, $oShare) {
		$this->shared_service[$service_name] = $oShare;
	}

	/**
	 * @param $service_name
	 * @return null|mixed
	 */
	public function getShare($service_name) {
		if(isset($this->shared_service[$service_name]))
			return $this->shared_service[$service_name];
		return null;
	}

	/**
	 * Ajoute un middleware au lanceur
	 * @param Middleware $middleware
	 */
	public function append(Middleware $middleware) {
		// au premier middleware on l'ajoute le start
		if ($this->middlewares_start === $this)
			$this->middlewares_start = $middleware;
		// le suivant a lancé c'est toujours l'app courante
		$middleware->setApplication($this);
		$middleware->setNext($this);
		// on inject au précédent le next courant et on change le précédent pour le prochain
		if ($this->middlewares_prev)
			$this->middlewares_prev->setNext($middleware);
		$this->middlewares_prev = &$middleware;
	}

	/**
	 * lecture de la requete
	 * @param Response $response
	 */
	public function listen(Response &$response = null) {
		// demarrage des middlewares
		$response = $response ?: new Response();
		try {
			$this->middlewares_start->call($response);
		} catch(\Exception $e) {
			$response->setBody((string) $e);
			$response->setStatus(500);
			$response->flush($this->request);
		}
	}

	/**
	 * @param Response $response
	 */
	public function call(Response &$response) {
		// echo
		$response->flush($this->request);
	}
}