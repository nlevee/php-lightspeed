<?php
/**
 * @author: Nicolas Levée
 * @version 120620131126
 */

namespace Lightspeed\Restful\Signature;

use Lightspeed\InvalidArgumentException;
use Lightspeed\Restful\Signature;

/**
 * Class SHA256
 * @package Lightspeed\Restful\Signature
 */
class SHA256 implements Signature {

	/**
	 * @param string $value_to_sign
	 * @param string|null $pass_phrase
	 * @throws \Lightspeed\InvalidArgumentException
	 * @return mixed|string
	 */
	public function sign($value_to_sign, $pass_phrase = null) {
		if (is_null($pass_phrase))
			throw new InvalidArgumentException('$pass_phrase is required');
		return base64_encode(hash_hmac("sha256", $value_to_sign, $pass_phrase));
	}

}