<?php
/**
 * @author: Nicolas Levée
 * @version 160520131307
 */

namespace Lightspeed\Http;

/**
 * Class Headers
 * @package Lightspeed\Http
 */
class Headers implements \ArrayAccess,\IteratorAggregate,\Countable {

	/**
	 * @var array
	 */
	private $headers = array();


	/**
	 * Charge les données depuis $aLoadData elimine les prefix HTTP_|- et X_|-
	 * et stocke les clé/valeur nettoyées
	 * @param array $aLoadData
	 */
	public function __construct(array $aLoadData = array()) {
		// nettoyage des clé
		foreach($aLoadData as $key => $value){
			if (($new_key = preg_replace("@^(HTTP|X)(_|-)@", '', $key)) != $key) {
				$this->headers[$this->normalizeKey($new_key)] = $value;
			}
		}
	}

	/**
	 * Normalise le formatage des clé
	 * @param string $keyname
	 * @return string
	 */
	protected function normalizeKey($keyname) {
		return str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $keyname))));
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->headers);
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new \ArrayIterator($this->headers ?: array());
	}

	/**
	 * Whether a offset exists
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->headers[$this->normalizeKey($offset)]);
	}

	/**
	 * Offset to retrieve
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		$offset = $this->normalizeKey($offset);
		if (isset($this->headers[$offset]))
			return $this->headers[$offset];
		return null;
	}

	/**
	 * Offset to set
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->headers[$this->normalizeKey($offset)] = $value;
	}

	/**
	 * Offset to unset
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->headers[$this->normalizeKey($offset)]);
	}
}