<?php
/**
 * @author: Nicolas Levée
 * @version 120620131126
 */

namespace Lightspeed\Rest;


interface Signature {

	/**
	 * @param string $value
	 * @return string
	 */
	public function sign($value);

}