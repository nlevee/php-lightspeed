<?php
/**
 * @author: Nicolas Levée
 * @version 210520131715
 */

namespace Lightspeed;

use Lightspeed\Http\Request;

/**
 * Class Controller
 * @package Lightspeed
 */
class Controller {
	/**
	 * @var App
	 */
	protected $application;

	/**
	 * @var Http\Request
	 */
	protected $request;


	/**
	 * @param App $application
	 * @param Request $request
	 */
	public function __construct(App $application, Request $request) {
		$this->request = $request;
		$this->application = $application;
	}

}