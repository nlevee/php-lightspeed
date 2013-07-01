<?php
/**
 * @author: Nicolas Levée
 * @version 160520131306
 */

namespace Lightspeed\Http;
use Lightspeed\ParamsAccess;

/**
 * Class Request
 * @package Lightspeed\Http
 */
class Request {

	/**
	 * Nom de la variable a tester pour l'ajax
	 * @var string
	 */
	public static $AJAX_HEADER_NAME = 'X-Requested-With';

	/**
	 * Valeur de la variable a tester pour l'ajax
	 * @var string
	 */
	public static $AJAX_HEADER_VALUE = 'XMLHttpRequest';

	/**
	 * @var array
	 */
	private $contentTypeCallback = array();


	/**
	 * @var string
	 */
	protected $basepath;

	/**
	 * @var Headers
	 */
	protected $headers;

	/**
	 * @var ParamsAccess
	 */
	protected $params;

	/**
	 * @var ParamsAccess
	 */
	protected $input_params;

	/**
	 * @var string
	 */
	protected $input;


	/**
	 * @param null|string $basepath
	 */
	public function __construct($basepath = null) {
		$this->basepath = $basepath ?: getenv('LIGHTSPEED_BASEPATH') ?: null;
		$this->headers = new Headers($_SERVER);
		//Input stream (readable one time only; not available for mutipart/form-data requests)
		if (($rawInput = @file_get_contents('php://input'))) {
			if ($this->getContentType("json")) {
				$this->input_params = new ParamsAccess(@json_decode($rawInput));
			} else {
				parse_str($rawInput, $aDataInput);
				$this->input_params = new ParamsAccess($aDataInput);
				unset($aDataInput);
			}
		}
		// lecture des params
		$this->input = $rawInput;
		$this->params = new ParamsAccess($_GET);
	}


	/**
	 * Verification si la requete est une requete ajax
	 * on utilise self::$AJAX_NAME et self::$AJAX_VALUE pour le check
	 * @return bool
	 */
	public function isXMLHttpRequest() {
		return strtolower(self::$AJAX_HEADER_VALUE) == strtolower($this->getHeaders(self::$AJAX_HEADER_NAME));
	}

	/**
	 * Renvoi la valeur string de l'input php php://input
	 * @return string
	 */
	public function getRowInput() {
		return $this->input;
	}

	/**
	 * Récuperation du param $name dans le body (input),
	 * si $name est un tableau on récupere les param des valeur de $name
	 * @param string|array $name
	 * @param null $default
	 * @return array|null|string
	 */
	public function getInputParam($name, $default = null) {
		return $this->input_params->getParam($name, $default);
	}

	/**
	 * Renvoi le tableau complet de properties du body (input) en excluant les clé issue d'$excludeKeys
	 * @param array $excludeKeys
	 * @return array
	 */
	public function getInputParams(array $excludeKeys = array()) {
		return $this->input_params->getParams($excludeKeys);
	}

	/**
	 * Ajoute un ou plusieurs parametres a la request,
	 * si $name est un tableau il est ajouter sous forme clé=>valeur
	 * @param string|array $name
	 * @param null|string $value
	 */
	public function setParam($name, $value = null) {
		$this->params->setParam($name, $value);
	}

	/**
	 * Récuperation du param $name dans la request,
	 * si $name est un tableau on récupere les param des valeur de $name
	 * @param string|array $name
	 * @param null $default
	 * @return array|null|string
	 */
	public function getParam($name, $default = null) {
		return $this->params->getParam($name, $default);
	}

	/**
	 * Renvoi le tableau complet de properties de la query en excluant les clé issue d'$excludeKeys
	 * @param array $excludeKeys
	 * @return array
	 */
	public function getParams(array $excludeKeys = array()) {
		return $this->params->getParams($excludeKeys);
	}

	/**
	 * Renvoi la methode d'appel de la requete
	 * @return string
	 */
	public function getMethod() {
		return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
	}

	/**
	 * Verifie le contenu du header HTTP ACCEPT
	 * @param string $sType ('html', 'json', 'application/json', ...)
	 * @return bool
	 */
	public function getAccept($sType) {
		return 1 === preg_match('@(' . $sType . ',?)|\*/\*@', $this->getHeaders('accept'));
	}

	/**
	 * @return string
	 */
	public function getUri() {
		list($path, ) = explode('?', $_SERVER['REQUEST_URI']);
		if (null !== $this->basepath)
			return '/' . preg_replace('@^'.$this->basepath.'@', '', $path);
		return $path;
	}

	/**
	 * Get Content-Length
	 * @return int
	 */
	public function getContentLength() {
		return $this->getHeaders('CONTENT_LENGTH') ?: 0;
	}

	/**
	 * Get Referer
	 * @return string|null
	 */
	public function getReferer() {
		return $this->headers('referer');
	}

	/**
	 * Verifi le type de contenu envoyé dans la requète
	 * En remplissant la variable $callbackTest on inject un nouveau test, exemple:
	 *     $this->getContentType('is jpeg', function( $contentType ){
	 *         return $contentType == 'image/jpeg';
	 *     });
	 *     $this->getContentType( 'is jpeg' ); => true/false
	 * @param string $sType
	 * @param callable $callbackTest
	 * @return bool
	 */
	public function getContentType($sType, $callbackTest = null) {
		if ($callbackTest !== null && is_callable($callbackTest))
			$this->contentTypeCallback[$sType] = $callbackTest;
		else {
			$sContentType = $this->getHeaders('content-type');
			if (isset($this->contentTypeCallback[$sType]))
				return call_user_func($this->contentTypeCallback[$sType], $sContentType);
			else
				return 1 === preg_match('@(' . $sType . ';)@', $sContentType);
		}
		return false;
	}

	/**
	 * Renvoi la valeur de la clé $key du header ou l'objet Headers de la request
	 * @param null|string $key
	 * @return Headers
	 */
	public function getHeaders($key = null) {
		if ($key !== null)
			return $this->headers[$key];
		return $this->headers;
	}

}