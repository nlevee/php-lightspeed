<?php
/**
 * @author: Nicolas Levée
 * @version 170620131707
 */

namespace Lightspeed\Formater;


use Lightspeed\Formater;
use Lightspeed\Http;
use Lightspeed\Http\Request;

class String implements FormaterInterface {

	/**
	 * Renvoi le content-type a renvoyé au client dans le header, peut être null
	 * @return string
	 */
	public function getContentType() {
		return null;
	}


	/**
	 * Converti la valeur $content en string dans le format nommé
	 * @param mixed $content
	 * @param Http\Request $request
	 * @return string
	 */
	public function convert($content, Request $request) {
		$content = !is_array($content) ? array($content) : $content;
		return implode("\n", array_filter(array_map(function($content){
			if (is_string($content) || (is_object($content) && method_exists($content, '__toString')))
				return (string) $content;
			trigger_error("Impossible de transformé le contenu en chaine.", E_USER_WARNING);
		}, $content)));
	}
}