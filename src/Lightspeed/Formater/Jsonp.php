<?php
/**
 * @author: Nicolas Levée
 * @version 140620131722
 */

namespace Lightspeed\Formater;

use Lightspeed\InvalidArgumentException;

/**
 * Class Jsonp
 * @package Lightspeed\Formater
 */
class Jsonp extends Json {

	/**
	 * @var \Lightspeed\Http\Request
	 */
	protected $request;


	/**
	 * @param Request $request
	 */
	public function __construct(Request $request) {
		$this->request = $request;
	}


	/**
	 * Converti la valeur $content en string dans le format jsonp, on attend un paramètre dans la request pour le callback
	 * @param mixed $content
	 * @throws \Lightspeed\InvalidArgumentException
	 * @return string
	 */
	public function convert($content) {
		$callback = $this->request->getParam('callback');
		if (!$callback)
			throw new InvalidArgumentException("parameter 'callback' must be define.");
		return "$callback(" . parent::convert($content) . ");";
	}

}