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
	 * Converti la valeur $content en string dans le format json
	 * @param mixed $content
	 * @return string
	 */
	public function convert($content) {
		if (is_string($content)) {
			$content = array('message' => $content);
		}
		return json_encode($content);
	}

}