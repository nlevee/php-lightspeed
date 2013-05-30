<?php
/**
 * @author: Nicolas Levée
 * @version 300520131652
 */

namespace Lightspeed;

/**
 * Permet a un service d'être partagé via l'application
 * Class Share
 * @package Lightspeed
 */
interface Share {

	/**
	 * Creation de l'instance du service
	 * @param array $options
	 * @return mixed
	 */
	public function getInstance(array $options = array());

}