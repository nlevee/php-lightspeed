<?php
/**
 * @author: Nicolas Levée
 * @version 160520131306
 */

namespace Lightspeed\Http;

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
	private $params = array();

	/**
	 * @var array
	 */
	private $contentTypeCallback = array();

	/**
	 * @var Headers
	 */
	private $headers;


	/**
	 *
	 */
	public function __construct() {
		$this->headers = new Headers($_SERVER);
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
	 * Ajoute un ou plusieurs parametres a la request,
	 * si $name est un tableau il est ajouter sous forme clé=>valeur
	 * @param string|array $name
	 * @param null|string $value
	 */
	public function setParam($name, $value = null) {
		if (is_array($name))
			$this->params = array_replace($this->params, $name);
		else
			$this->params[$name] = $value;
	}

	/**
	 * Récuperation du param $name dans la request
	 * @param string $name
	 * @param string|null $default
	 * @return mixed
	 */
	public function getParam($name, $default = null) {
		if (isset($this->params[$name]))
			return $this->params[$name];
		if (isset($_REQUEST[$name]))
			return $_REQUEST[$name];
		return $default;
	}

	/**
	 * Renvoi le tableau complet de properties en excluant les clé issue d'$excludeKeys
	 * @param array $excludeKeys
	 * @return array
	 */
	public function getParams(array $excludeKeys = array()) {
		return array_filter(array_replace($_REQUEST, $this->params), function($value) use ($excludeKeys){
			return !in_array($value, $excludeKeys);
		});
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
		return 1 === preg_match('@(' . $sType . ',)|\*/\*@', $this->getHeaders('accept'));
	}

	/**
	 * @return string
	 */
	public function getUri() {
		return $_SERVER['SCRIPT_NAME'];
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