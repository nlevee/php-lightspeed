<?php
/**
 * @author: Nicolas Levée
 * @version 140620131730
 */

namespace Lightspeed\Formater;

use Lightspeed\Http\Request;

/**
 * Class Formater
 * @package Lightspeed
 */
interface FormaterInterface {

	/**
	 * Renvoi le content-type a renvoyé au client dans le header, peut être null
	 * @return string
	 */
	public function getContentType();

	/**
	 * Converti la valeur $content en string dans le format nommé
	 * @param mixed $content
	 * @param \Lightspeed\Http\Request $request
	 * @return string
	 */
	public function convert($content, Request $request);

}