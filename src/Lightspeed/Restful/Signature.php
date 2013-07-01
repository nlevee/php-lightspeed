<?php
/**
 * @author: Nicolas Levée
 * @version 120620131126
 */

namespace Lightspeed\Restful;


interface Signature {

	/**
	 * @param string $value_to_sign
	 * @param string $pass_phrase
	 * @return mixed|string
	 */
	public function sign($value_to_sign, $pass_phrase = null);

}