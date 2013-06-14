<?php
/**
 * @author: Nicolas Levée
 * @version 140620131730
 */

namespace Lightspeed;


interface Formater {

	/**
	 * Converti la valeur $content en string dans le format nommé
	 * @param mixed $content
	 * @return string
	 */
	public function convert($content);

}