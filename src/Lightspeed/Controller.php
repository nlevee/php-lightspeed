<?php
/**
 * @author: Nicolas Levée
 * @version 210520131715
 */

namespace Lightspeed;

use Lightspeed\Engine\EngineInterface;
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
	 * @var null|EngineInterface
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
		if (is_object($this->engine) && !is_a($this->engine, '\\Lightspeed\\Engine\\EngineInterface')){
			trigger_error("share `engine` must implement \\Lightspeed\\Engine\\EngineInterface", E_USER_WARNING);
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
		try {
			return $this->_handleFunction($method, $params[0]);
		} catch(\Exception $e) {
			return $this->_handleException($e, $params[0]);
		}
	}


	/**
	 * Fonction d'initialisation du controller
	 * peux être étendu par la class enfant
	 */
	public function init() {

	}

	/**
	 * Fonction d'appel des fonction interne
	 * @param string $sMethodOriginName
	 * @param Response $response
	 * @throws InvalidArgumentException
	 * @throws BadMethodCallException
	 * @return mixed
	 */
	protected function _handleFunction($sMethodOriginName, Response &$response) {
		$sMethodName = $sMethodOriginName . 'Action';
		if (true !== method_exists($this, $sMethodName))
			throw new BadMethodCallException('The request method "' . $sMethodName . '" does not exist in Controller\\' . __CLASS__);
		// execution de la method
		$aDataSet = $this->$sMethodName($response);
		if ($this->engine !== null) {
			if (!is_a($this->engine, '\\Lightspeed\\Engine\\EngineInterface'))
				throw new InvalidArgumentException("share `engine` must implement \\Lightspeed\\Engine\\EngineInterface", E_USER_WARNING);
			$aClassNameExplode = explode('\\', get_class($this));
			$response->setContentType('text/html', 'utf-8');
			return $this->engine->render(strtolower(end($aClassNameExplode).'/'.$sMethodOriginName), $aDataSet);
		}
		return $aDataSet;
	}

	/**
	 * Fonction appelé pour la prise en charge des erreur des actions
	 * @param \Exception $e
	 * @param Response $response
	 * @return string
	 */
	protected function _handleException(\Exception $e, Response $response) {
		$response->setStatus(500);
		return (string) $e;
	}

}