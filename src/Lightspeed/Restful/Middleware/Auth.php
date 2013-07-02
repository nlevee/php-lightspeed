<?php
/**
 * @author: Nicolas Levée
 * @version 010720131449
 */

namespace Lightspeed\Restful\Middleware;


use Lightspeed\Http\Response;
use Lightspeed\Middleware;
use Lightspeed\Restful\SecretHandler;
use Lightspeed\Restful\Signature;

/**
 * Class Auth
 * @package Lightspeed\Restful\Middleware
 */
class Auth extends Middleware {

	/**
	 * @var string
	 */
	private $_headerClientKey;

	/**
	 * @var string
	 */
	private $_headerSign;

	/**
	 * @var Signature
	 */
	private $_signMethod;

	/**
	 * @var SecretHandler
	 */
	private $_passHandler;

	/**
	 * @var array|bool
	 */
	private $_stampCheck = false;

	/**
	 * @var bool
	 */
	private $_required = true;


	/**
	 * @param string $sHeaderClientKey
	 * @param string $sHeaderSignature
	 * @param Signature $oSignMethod
	 * @param SecretHandler $handler
	 */
	public function __construct($sHeaderClientKey, $sHeaderSignature, Signature $oSignMethod, SecretHandler $handler) {
		$this->_headerClientKey = $sHeaderClientKey;
		$this->_headerSign = $sHeaderSignature;
		$this->_signMethod = $oSignMethod;
		$this->_passHandler = $handler;
	}

	/**
	 * Permet de mettre optionnel ou pas l'authentification
	 * par default c'est obligatoire
	 * @param bool $bool
	 */
	public function required($bool) {
		$this->_required = (bool) $bool;
	}

	/**
	 * Ajoute un check de stamp sur la requete d'un maxi de $ttl,
	 * si $ttl est non numeriq on annule le check
	 * @param int|bool $ttl
	 * @param string $sParamName default &{timestamp}=...
	 */
	public function setTimstampCheckTTL($ttl, $sParamName = 'timestamp') {
		$this->_stampCheck = false;
		if (is_numeric($ttl))
			$this->_stampCheck = array($sParamName, $ttl);
	}

	/**
	 * Permet la detection de la sécurité et le decodage de la clé si existante
	 * Si pas de sécurité alors que le flag secure est a true : 401
	 * @param Response $response
	 * @return mixed
	 */
	public function call(Response &$response) {
		// on récupere les données de requetes
		if ($this->request->getMethod() == 'GET')
			$aFullParams = $this->request->getParams();
		else
			$aFullParams = $this->request->getInputParams();
		$this->request->setParam('auth_verified', 'no');
		// premier check du timestamp si demandé
		if ($this->_stampCheck !== false) {
			list($param_name, $ttl) = $this->_stampCheck;
			if ((int) $this->request->getParam($param_name, 0) < gmmktime() - $ttl)
				return $this->_failed($response);
			if (!isset($aFullParams[$param_name]))
				$aFullParams[$param_name] = $this->request->getParam($param_name, 0);
		}
		// comparaison des données avec la clé
		$sSecretKey = $this->_passHandler->getSecretKey($this->request->getHeaders($this->_headerClientKey));
		if ($sSecretKey) {
			$sHashServer = $this->_signMethod->sign(http_build_query($aFullParams), $sSecretKey);
			if (!$sHashServer || $sHashServer !== $this->request->getHeaders($this->_headerSign))
				return $this->_failed($response);
			$this->request->setParam('auth_verified', 'ok');
		} else
			return $this->_failed($response);
		$this->next($response);
	}


	/**
	 * Echec!
	 * @param Response $response
	 */
	private function _failed(Response &$response) {
		if ($this->_required === true) {
			$response->setStatus(401);
			$this->stop($response);
		} else
			$this->next($response);
	}
}