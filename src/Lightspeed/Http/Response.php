<?php
/**
 * @author: Nicolas Levée
 * @version 160520131307
 */

namespace Lightspeed\Http;
use Lightspeed\Formater;

/**
 * Class Response
 * @package Lightspeed\Http
 */
class Response {

	/**
	 * @var array HTTP response codes and messages
	 */
	protected static $messages = array(
		//Informational 1xx
		100 => '100 Continue',
		101 => '101 Switching Protocols',
		//Successful 2xx
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',
		//Redirection 3xx
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',
		//Client Error 4xx
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',
		422 => '422 Unprocessable Entity',
		423 => '423 Locked',
		//Server Error 5xx
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported'
	);


	/**
	 * @var int|bool|null
	 */
	protected $ttl = null;

	/**
	 * @var array
	 */
	protected $body = array();

	/**
	 * @var int
	 */
	protected $statusCode = 200;

	/**
	 * @var Headers
	 */
	protected $headers;

	/**
	 * @var Formater[]
	 */
	protected $formater = array();


	/**
	 *
	 */
	public function __construct() {
		$this->headers = new Headers();
		$this->addFormater("text", new Formater\String());
	}


	/**
	 * Met en place l'expire de la page de la manière suivante :
	 *     si null on ne prend pas en charge
	 *     si false on envoi un décache de la page
	 *     si un chiffre on met en cache $expireTTL secondes
	 * @param int|null|bool $expireTTL
	 */
	public function expireTTL($expireTTL) {
		unset($this->headers["Expires"], $this->headers["Cache-Control"]);
		if (is_numeric($expireTTL)) {
			$this->headers["Expires"] = $this->_getGMDate($expireTTL);
			$this->headers["Cache-Control"] = "public, max-age=" . $expireTTL;
		} elseif ($expireTTL === false) {
			$this->headers["Expires"] = $this->_getGMDate(-3600);
			$this->headers["Cache-Control"] = "no-store, no-cache, must-revalidate, post-check=0, pre-check=0";
		}
	}

	/**
	 * Défini le type de document dont il s'agit et son charset si spécifié
	 * @param string $mime_type
	 * @param string|null $charset
	 */
	public function setContentType($mime_type, $charset = null) {
		$this->headers["Content-type"] = $mime_type . ($charset !== null ? '; charset=' . $charset : '');
	}

	/**
	 * Envoi un header redirect vers l'url $uri en mode 301 si $permanent=true ou 302
	 * @param string $uri
	 * @param bool $permanent
	 */
	public function redirect($uri, $permanent = false) {
		$this->setStatus($permanent === true ? 301 : 302);
		$this->headers["Location"] = $uri;
	}

	/**
	 * Défini le header pour le created (Status 201 + Location: $uri)
	 * @param string $uri
	 */
	public function created($uri) {
		$this->setStatus(201);
		$this->headers["Location"] = $uri;
	}

	/**
	 * Renvoi dans la reponse un header 405 avec un Allow: $aMethodAllowed
	 * ou 404 si vraiement introuvable
	 * @param $aMethodAllowed
	 */
	public function notFound(array $aMethodAllowed = array()) {
		if (!empty($aMethodAllowed)){
			$this->setStatus(405);
			$this->headers['Allow'] = implode(', ', $aMethodAllowed);
		} else {
			$this->setStatus(404);
		}
	}

	/**
	 * On ajoute un formater au début de la pile
	 * @param string $type
	 * @param Formater $formater
	 */
	public function addFormater($type, Formater $formater) {
		array_unshift($this->formater, array($type, $formater));
	}

	/**
	 * Met a jour le code de status de la réponse
	 * @param int $statusCode
	 */
	public function setStatus($statusCode) {
		$this->statusCode = (int)$statusCode;
	}

	/**
	 * Renvoi le status courant
	 * @return int
	 */
	public function getStatus() {
		return $this->statusCode;
	}

	/**
	 * Ajoute de la data au body de la reponse
	 * @param string $content
	 * @param bool $replace
	 */
	public function setBody($content, $replace = false) {
		if (!empty($content)) {
			$content = is_array($content) ? $content : array($content);
			if ($replace === false)
				$this->body = array_merge($this->body, $content);
			else
				$this->body = $content;
		}
	}

	/**
	 * Envoi sur la sortie standard la réponse
	 * Envoi les header s'il n'ont pas encore été envoyé sinon envoi une notice
	 * @param Request $req
	 */
	public function flush(Request $req) {
		// formatage du body selon le request content
		foreach ($this->formater as $object) {
			if ($req->getAccept($object[0])) {
				$sConvertBody = $object[1]->convert($this->body, $req);
				$this->setContentType($object[1]->getContentType());
				break;
			}
		}
		if (!headers_sent()) {
			// prise en charge du contenu vide
			if (empty($sConvertBody) && ob_get_length() == 0 && $this->statusCode == 200)
				$this->statusCode = 204;
			// prise en charge des redirections, on vide le body car inutil
			if ($this->statusCode >= 301 && $this->statusCode <= 304)
				$sConvertBody = '';
			// gestion du code d'erreur
			$GLOBALS['http_response_code'] = $this->statusCode;
			if (($message = self::getMessageStatus($this->statusCode)) !== NULL) {
				if (strpos(PHP_SAPI, 'cgi') === 0) {
					header('Status: ' . $message, false);
				} else {
					$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
					header($protocol . ' ' . $message, false);
				}
			}
			// envoi des headers défini
			foreach ($this->headers as $sHeaderKey=>$sHeaderValue)
				header($sHeaderKey . ": " . $sHeaderValue, false);
		} else
			trigger_error('Header already sent!', E_USER_NOTICE);
		if (isset($sConvertBody))
			echo $sConvertBody;
		else trigger_error("Any filter for format response", E_USER_WARNING);
	}

	/**
	 * Calcul le temps GMT pour le ttl
	 * @param int $expireTTL
	 * @return null|string
	 */
	protected function _getGMDate($expireTTL) {
		if (is_numeric($expireTTL)) {
			$sExpiresDate = gmdate(DATE_RFC1123, time() + $expireTTL);
			return substr($sExpiresDate, 0, -5) . 'GMT';
		}
		return null;
	}


	/**
	 * Renvoi le message associé a un code erreur ou null si introuvable
	 * @param int $code
	 * @return null|string
	 */
	public static function getMessageStatus($code) {
		if (isset(self::$messages[$code]))
			return self::$messages[$code];
		return null;
	}

}