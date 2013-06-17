<?php
/**
 * @author: Nicolas Levée
 * @version 140620131722
 */

namespace Lightspeed\Formater;

use Lightspeed\Http\Request;
use Lightspeed\InvalidArgumentException;

/**
 * Class Jsonp
 * @package Lightspeed\Formater
 */
class Jsonp extends Json {

	/**
	 * Converti la valeur $content en string dans le format jsonp, on attend un paramètre dans la request pour le callback
	 * @param mixed $content
	 * @param \Lightspeed\Http\Request $request
	 * @throws \Lightspeed\InvalidArgumentException
	 * @return string
	 */
	public function convert($content, Request $request) {
		$callback = $request->getParam('callback');
		if (!$callback)
			throw new InvalidArgumentException("parameter 'callback' must be define.");
		return "$callback(" . parent::convert($content, $request) . ");";
	}

}