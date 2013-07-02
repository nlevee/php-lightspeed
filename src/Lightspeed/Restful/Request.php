<?php
/**
 * @author: Nicolas Levée
 * @version 120620131128
 */

namespace Lightspeed\Restful;

use Lightspeed\Http\Headers;
use Lightspeed\ParamsAccess;

/**
 * Class Request
 * @package Lightspeed\Restful
 */
class Request {

	/**
	 *
	 */
	const METHOD_POST = 'POST';

	/**
	 *
	 */
	const METHOD_GET = 'GET';

	/**
	 *
	 */
	const METHOD_DELETE = 'DELETE';

	/**
	 *
	 */
	const METHOD_PUT = 'PUT';

	/**
	 *
	 */
	const METHOD_HEADERS = 'HEADERS';


	/**
	 * @var Headers
	 */
	public $headers;

	/**
	 * @var ParamsAccess
	 */
	public $params;


	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var string
	 */
	private $uri;


	/**
	 * @param string $method
	 * @param string $uri
	 */
	public function __construct($method, $uri) {
		$this->params = new ParamsAccess();
		$this->headers = new Headers();
		$this->method = $method;
		$this->uri = $uri;
	}

	/**
	 * Ajoute des header spécifique a la signature du requete Restful :
	 * X-AuthClient, X-AuthPass, X-AuthMethod
	 * @param string $client_public_key
	 * @param Signature $signature
	 */
	public function setSignature($client_public_key, Signature $signature) {
		$value = $this->method . ':' . $this->uri . "?" . $this->params->getParams();
		$this->headers['X-AuthClient'] = $client_public_key;
		$this->headers['X-AuthPass'] = $signature->sign($value);
	}

	/**
	 * Lance la requetes comme défini
	 * @return mixed
	 */
	public function run() {

	}

}