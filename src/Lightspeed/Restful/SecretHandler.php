<?php
/**
 * @author: Nicolas Levée
 * @version 010720131638
 */

namespace Lightspeed\Restful;


/**
 * Class SecretHandler
 * @package Lightspeed\Restful
 */
interface SecretHandler {

	/**
	 * Permet de renvoyé la clé privé d'uin client via sa $sClientKey
	 * @param string $sClientKey
	 * @return mixed
	 */
	public function getSecretKey($sClientKey);

}