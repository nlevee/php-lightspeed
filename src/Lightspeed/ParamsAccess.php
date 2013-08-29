<?php
/**
 * @author: Nicolas Levée
 * @version 110620131827
 */

namespace Lightspeed;

/**
 * Class ArrayAccess
 * @package Lightspeed
 */
class ParamsAccess implements \ArrayAccess,\Countable {

	/**
	 * @var array
	 */
	private $params;

	/**
	 * @param array $database
	 */
	public function __construct(array $database = array()) {
		$this->params = $database;
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->params);
	}

	/**
	 * Ajoute un ou plusieurs parametres,
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
	 * Récuperation du param $name ,
	 * si $name est un tableau on récupere les param des valeur de $name
	 * @param string|array $name
	 * @param string|null $default
	 * @return array|null|string
	 */
	public function getParam($name, $default = null) {
		if (is_array($name)) {
			return array_merge(
			// fabrication du tableau de valeurs par default
				$default ?: array_combine($name, array_fill(0, count($name), null)),
				// tableau des donnée retourné exp des valeur null
				array_filter(array_combine($name, array_map(array($this, 'getParam'), $name)))
			);
		} else {
			if (isset($this->params[$name]))
				return $this->params[$name];
		}
		return $default;
	}

	/**
	 * Renvoi le tableau complet de properties en excluant les clé issue d'$excludeKeys
	 * @param array $excludeKeys
	 * @return array
	 */
	public function getParams(array $excludeKeys = array()) {
		return array_diff_key( $this->params, array_flip( $excludeKeys ) );
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->params[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->getParam($offset);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value) {
		$this->setParam($offset, $value);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset) {
		unset($this->params[$offset]);
	}
}