<?php
/**
 * @author: Nicolas Levée
 * @version 300520131814
 */

namespace Lightspeed;

/**
 * Class Engine
 * @package Lightspeed
 */
interface Engine {

	/**
	 * Effectue un rendu du template $template avec le $context
	 * @param mixed $template
	 * @param array $context
	 * @return string
	 */
	public function render($template, $context = array());

}