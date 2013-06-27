<?php
/**
 * @author: Nicolas Levée
 * @version 140620131719
 */

namespace Lightspeed\Formater;

use Lightspeed\Formater;
use Lightspeed\Http\Request;

/**
 * Class Json
 * @package Lightspeed\Formater
 */
class Json implements Formater {

	/**
	 * Renvoi le content-type a renvoyé au client dans le header, peut être null
	 * @return string
	 */
	public function getContentType() {
		return "application/json";
	}

	/**
	 * Converti la valeur $content en string dans le format json
	 * @param mixed $content
	 * @param \Lightspeed\Http\Request $request
	 * @return string
	 */
	public function convert($content, Request $request) {
		return !empty($content) ? json_encode($content) : '';
	}

}