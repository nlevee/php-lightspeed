<?php
/**
 * Class d'encryptage en Rijndael
 */
namespace Lightspeed\Crypt;

class Rijndael implements CryptInterface {

	/**
	 * Cryptage RIJDANEL d'une chaine
	 * @param string $text
	 * @param string $secret_key
	 * @return string
	 */
	public function encrypt($text, $secret_key) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secret_key, $text, MCRYPT_MODE_ECB, $iv)));
	}

	/**
	 * Décryptage RIJDANEL d'une chaine
	 * @param string $text
	 * @param string $secret_key
	 * @return string
	 */
	public function decrypt($text, $secret_key) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		//I used trim to remove trailing spaces
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secret_key, base64_decode($text), MCRYPT_MODE_ECB, $iv));
	}

}
