<?php
/**
 * @author: Nicolas Levée
 * @version 210820131546
 */

namespace Lightspeed\Formater;

use Lightspeed\Crypt\CryptInterface;
use Lightspeed\Formater;
use Lightspeed\Http;
use Lightspeed\Http\Request;

/**
 * Class Encrypt
 * @package Lightspeed\Formater
 */
class Encrypt implements FormaterInterface {

	/**
	 * @var CryptInterface
	 */
	private $_method;

	/**
	 * @var string
	 */
	private $_secret;

	/**
	 * @var \Lightspeed\FormaterInterface
	 */
	private $_formater;

	/**
	 * @param FormaterInterface $formater
	 * @param CryptInterface $method
	 * @param $sSecretKey
	 */
	public function __construct(FormaterInterface $formater, CryptInterface $method, $sSecretKey) {
		$this->_method = $method;
		$this->_secret = $sSecretKey;
		$this->_formater = $formater;
	}

	/**
	 * Converti la valeur $content en string dans le format nommé
	 * @param mixed $content
	 * @param Http\Request $request
	 * @return string
	 */
	public function convert($content, Request $request) {
		$content = $this->_formater->convert($content, $request);
		// decodage du contenu encrypté dans le input
		return $this->_method->encrypt($content, $this->_secret);
	}

	/**
	 * Renvoi le content-type a renvoyé au client dans le header, peut être null
	 * @return string
	 */
	public function getContentType() {
		return 'text/plain';
	}
}