<?php
/**
 * @author: Nicolas Levée
 * @version 210520131715
 */

namespace Lightspeed;

use Lightspeed\Http\Request;
use Lightspeed\Http\Response;

/**
 * Class Controller
 * @package Lightspeed
 */
abstract class Controller {

	/**
	 * Nom du partage pour l'engine
	 */
	const SHARE_ENGINE = 'engine';


	/**
	 * @var App
	 */
	protected $application;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var null|Engine
	 */
	protected $engine;


	/**
	 * @param App $application
	 * @param Request $request
	 */
	final public function __construct(App $application, Request $request) {
		$this->request = $request;
		$this->application = $application;
		$this->engine = $application->getShare(self::SHARE_ENGINE);
		if (is_object($this->engine) && !is_a($this->engine, '\\Lightspeed\\Engine')){
			trigger_error("share `engine` must implement \\Lightspeed\\Engine", E_USER_WARNING);
			$this->engine = null;
		}
		$this->init();
	}

	/**
	 * Permet de lancer les actions en fournissant les bon paramètres.
	 * @param string $method
	 * @param array $params
	 * @throws InvalidArgumentException
	 * @return mixed
	 */
	final public function __call($method, array $params) {
		// execution
		return $this->_handleFunction($method, $params[0]);
	}


	/**
	 * Fonction d'initialisation du controller
	 * peux être étendu par la class enfant
	 */
	public function init() {

	}

	/**
	 * Fonction d'appel des fonction interne
	 * @param string $sMethodName
	 * @param Response $response
	 * @throws BadMethodCallException
	 * @return mixed
	 */
	protected function _handleFunction($sMethodName, Response &$response) {
		$sMethodName .= 'Action';
		if (true !== method_exists($this, $sMethodName))
			throw new BadMethodCallException('The request method "' . $sMethodName . '" does not exist in Controller\\' . __CLASS__);
		// execution de la method
		return $this->$sMethodName($response);
	}

}