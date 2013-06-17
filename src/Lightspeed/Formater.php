<?php
/**
 * @author: Nicolas Levée
 * @version 140620131730
 */

namespace Lightspeed;

use Lightspeed\Http\Request;

/**
 * Class Formater
 * @package Lightspeed
 */
interface Formater {

	/**
	 * Converti la valeur $content en string dans le format nommé
	 * @param mixed $content
	 * @param Http\Request $request
	 * @return string
	 */
	public function convert($content, Request $request);

}