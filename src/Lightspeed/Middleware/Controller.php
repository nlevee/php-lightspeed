<?php
/**
 * @author: Nicolas Levée
 * @version 210520131700
 */

namespace Lightspeed\Middleware;

use Lightspeed\Http\Response;
use Lightspeed\Middleware;

/**
 * Class Controller
 * @package Lightspeed\Middleware
 */
class Controller extends Middleware {

	/**
	 * @var string
	 */
	protected $default_action = 'index';

	/**
	 * @var string
	 */
	protected $default_controller = 'index';

	/**
	 * @var string
	 */
	protected $namespace = '';


	/**
	 * @var string
	 */
	public static $PARAM_CONTROLLER = 'controller';

	/**
	 * @var string
	 */
	public static $PARAM_ACTION = 'action';


	/**
	 * @param string $namespace
	 * @param string|null $default_controller
	 * @param string|null $default_action
	 */
	public function __construct($namespace = '', $default_controller = null, $default_action = null) {
		$this->namespace = $namespace;
		$this->default_action = $default_action ?: $this->default_action;
		$this->default_controller = $default_controller ?: $this->default_controller;
	}

	/**
	 * Permet le lancement d'une action $sActionName dans un controller $sControllerName
	 * @param string $sControllerName
	 * @param string $sActionName
	 * @param array $aParams
	 * @return mixed
	 */
	public function launch($sControllerName, $sActionName, array $aParams) {
		// creation de l'instance de controller
		$sControllerName = $this->namespace . "Controller\\" . ucfirst($sControllerName);
		// verification de l'extention
		$oController = new $sControllerName($this->application, $this->request);
		// lancemenent de l'action
		return call_user_func_array(array($oController, strtolower($sActionName)), $aParams);
	}

	/**
	 * Cette fonction est a définir dans le middleware pour l'execution de l'action principal du middleware
	 *
	 * @param Response $response
	 * @return mixed
	 */
	public function call(Response &$response) {
		list($action, $controller) = array_values($this->request->getParam(array(
			self::$PARAM_ACTION,
			self::$PARAM_CONTROLLER
		), array(
			self::$PARAM_ACTION => $this->default_action,
			self::$PARAM_CONTROLLER => $this->default_controller
		)));
		$response->setBody($this->launch($controller, $action, array($response)));
		$this->next->call($response);
	}
}