<?php
/**
 * @author: Nicolas Levée
 * @version 210520131645
 */

namespace Lightspeed\Middleware;

use Lightspeed\Http\Response;
use Lightspeed\Middleware;
use Lightspeed\Route;

/**
 * Class Router
 * @package Lightspeed\Middleware
 */
class Router extends Middleware implements \Countable{

	/**
	 * @var array
	 */
	private $routes = array('ANY' => array());

	/**
	 * @var array
	 */
	private $filters = array();


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
	 * Renvoi la liste des methodes pour lesquel la route passe
	 * @return array
	 */
	public function getMatchMethods() {
		$aMethodList = array();
		$sRequestUri = $this->request->getUri();
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
	public function call(Response &$response) {
		// ajout de la route global si existe
		$aApplyRoute = $this->routes['ANY'];
		$method = $this->request->getMethod();
		if (isset($this->routes[$method]))
			$aApplyRoute = array_merge($this->routes[$method], $aApplyRoute);
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
					call_user_func_array($callback, array(&$this->request, &$response));
					$response->setBody(ob_get_clean());
				}
				break;
			}
			// aucun match dans les
			if (!isset($params) || !$params) {
				$response->notFound($this->getMatchMethods());
				$this->application->call($response);
			}
			unset($oRoute);
		}else{
			$response->notFound($this->getMatchMethods());
			$this->application->call($response);
		}
	}

}