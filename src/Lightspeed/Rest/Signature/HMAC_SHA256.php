<?php
/**
 * @author: Nicolas Levée
 * @version 120620131126
 */

namespace Lightspeed\Rest\Signature;

use Lightspeed\Rest\Signature;

/**
 * Class HMAC_SHA256
 * @package Lightspeed\Rest\Signature
 */
class HMAC_SHA256 implements Signature {

	/**
	 * @var string
	 */
	private $secret_key;

	/**
	 * @param string $sSecretKey
	 */
	public function __construct($sSecretKey) {
		$this->secret_key = $sSecretKey;
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function sign($value) {
		return base64_encode(hash_hmac("sha256", $value, $this->secret_key));
	}

}