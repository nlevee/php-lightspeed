<?php
/**
 * @author: Nicolas Levée
 * @version 210820131516
 */

namespace Lightspeed\Crypt;


/**
 * Class CryptInterface
 * @package Lightspeed\Crypt
 */
interface CryptInterface {

	/**
	 * Cryptage d'une chaine
	 * @param string $text
	 * @param string $secret_key
	 * @return string
	 */
	public function encrypt($text, $secret_key);

	/**
	 * Décryptage d'une chaine
	 * @param string $text
	 * @param string $secret_key
	 * @return string
	 */
	public function decrypt($text, $secret_key);

}