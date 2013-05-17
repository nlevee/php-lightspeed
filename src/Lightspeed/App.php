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
 * Class App
 * @package Lightspeed
 */
class App {

	/**
	 * @var array
	 */
	private $routes = array('ANY' => array());

	/**
	 * @var array
	 */
	private $filters = array();


	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var array
	 */
	protected $middlewares = array();

	/**
	 * @var string
	 */
	protected $method;


	/**
	 * @param Request $request
	 */
	public function __construct(Request &$request) {
		$this->request = $request;
		$this->method = $request->getMethod();
	}


	/**
	 * Ajoute une route sur la methode post
	 * @param string $route
	 * @param mixed $callback
	 * @param array $filters
	 * @param array $query
	 */
	public function post($route, $callback, array $filters = array(), array $query = array()) {
		$this->add('post', $route, $callback, $filters, $query);
	}

	/**
	 * Ajoute une route sur la methode get
	 * @param string $route
	 * @param mixed $callback
	 * @param array $filters
	 * @param array $query
	 */
	public function get($route, $callback, array $filters = array(), array $query = array()) {
		$this->add('get', $route, $callback, $filters, $query);
	}

	/**
	 * Ajoute une route sur toutes les methodes
	 * @param string $route
	 * @param mixed $callback
	 * @param array $filters
	 * @param array $query
	 */
	public function any($route, $callback, array $filters = array(), array $query = array()) {
		$this->add('any', $route, $callback, $filters, $query);
	}

	/**
	 * Ajoute une route sur la ou les methode $meth
	 * @param string|array $meth peut être GET, POST, ... ou 'ANY' pour n'importe laquel
	 * @param string $route
	 * @param mixed $callback
	 * @param array $filters
	 * @param array $query
	 */
	public function add($meth, $route, $callback, array $filters = array(), array $query = array()) {
		if (!is_array($meth))
			$meth = array($meth);
		foreach ($meth as $methName) {
			$methName = strtoupper($methName);
			if (!isset($this->routes[$methName]))
				$this->routes[$methName] = array();
			$this->routes[$methName][] = array($route, $callback, $filters, $query);
		}
	}

	/**
	 * Purge les route entré pour la methode $meth ou toute les routes si
	 * $meth est empty si $meth est un tableau de method on purge les route de chaque methode
	 * @param null|string|array $meth peut être GET, POST, ... ou 'ANY' pour n'importe laquel
	 */
	public function purgeRoute($meth = null) {
		if (empty($meth)) {
			$meth = array_keys($this->routes);
		}
		if (!is_array($meth))
			$meth = array($meth);
		foreach ($meth as $methName) {
			$methName = strtoupper($methName);
			$this->routes[$methName] = array();
		}
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
	 * Renvoi la liste des methodes pour lesquel la route passe
	 * @return array
	 */
	public function getMatchMethods() {
		$aMethodList = array();
		$sRequestUri = $this->request->getRequestUri();
		foreach($this->routes as $sMethodName => $aRouteList) {
			if (empty($aRouteList))
				continue;
			foreach($aRouteList as $aRouteData) {
				// récuperation des params de la route et creation de l'objet pour test
				list($pattern_uri, , $filters) = $aRouteData;
				$oRoute = new Route($pattern_uri, array_merge($this->filters, $filters));
				if (false === $oRoute->match($sRequestUri))
					continue;
				$aMethodList[] = $sMethodName;
			}
		}
		unset($sRequestUri, $aRouteList, $aRouteData, $oRoute);
		return $aMethodList;
	}

	/**
	 * Count routes implemented
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer
	 */
	public function count() {
		return count($this->routes);
	}

	/**
	 * @param Response $response
	 * @return bool
	 */
	public function applyRoute(Response &$response) {
		// ajout de la route global si existe
		$aApplyRoute = $this->routes['ANY'];
		if (isset($this->routes[$this->method]))
			$aApplyRoute = array_merge($this->routes[$this->method], $aApplyRoute);
		if (!empty($aApplyRoute)) {
			$sRequestUri = $this->request->getUri();
			foreach ($aApplyRoute as $route) {
				// récuperation des params de la route et creation de l'objet pour test
				list($pattern_uri, $callback, $filters, $query) = $route;
				$oRoute = new Route($pattern_uri, array_merge($this->filters, $filters));
				if (false === ($params = $oRoute->match($sRequestUri)))
					continue;
				// insert des params dans le request
				$this->request->setParam(array_merge($params, $query));
				// appel du callback si c'est possible
				if (is_callable($callback) === true) {
					ob_start();
					call_user_func_array($callback, array($this->request, $response));
					$response->setBody(ob_get_clean());
				}
				return true;
			}
			unset($oRoute);
		}
		return false;
	}

	/**
	 * lecture de la requete
	 * @param Response $response
	 */
	public function listen(Response &$response) {
		// demarrage des middlewares
		if (!empty($this->middlewares))
			$this->middlewares[0]->call($response);
		// lancement du routage
		if (!$this->applyRoute($response)) {
			// renvoi false si aucune regle n'est passé
			$response->setStatus(405);
		}
		// echo
		$response->display();
	}

}