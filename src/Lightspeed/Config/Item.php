<?php
/**
 * @author: Nicolas Levée
 * @version 190820131750
 */

namespace Lightspeed\Config;

use Lightspeed\OutOfBoundsException;

/**
 *
 */
class Item implements \ArrayAccess, \Countable {

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @var string
	 */
	protected $_spliter = '.';


	/**
	 * Charge un tableau de donnée comme item, si des clé ont un séprateur $split
	 * On découpe la pour créé des tableaux
	 * @param array $aConfigSet
	 * @param string $split
	 */
	public function __construct(array $aConfigSet = array(), $split = '.') {

		$this->_data = $aConfigSet;
		if (!empty($split) && is_string($split))
			$this->_spliter = $split;

		// parcour pour le calcul des déscendant
		foreach ($this->_data as $key => $data) {
			if (strpos($key, $this->_spliter) !== false) {
				$aKeys = explode($split, $key);
				$sFirstPart = array_shift($aKeys);
				$this->_data[$sFirstPart][implode($split, $aKeys)] = $data;
				unset($this->_data[$key]);
			}
		}
	}

	/**
	 * Acces aux offset en mode objet
	 * @param string $offset
	 * @return Item|mixed
	 */
	public function __get($offset){
		return $this->offsetGet($offset);
	}

	/**
	 * Assigne une valeur a une clé de l'item en mode objet
	 * @param string $offset
	 * @param string|array $value
	 */
	public function __set($offset, $value){
		$this->offsetSet($offset, $value);
	}

	/**
	 * Verifi l'existance d'une clé dans l'item
	 * @param string $offset
	 * @return bool
	 */
	public function __isset($offset){
		return $this->offsetExists($offset);
	}


	/**
	 * Retourne une copie du tableau
	 * @return array
	 */
	public function getArrayCopy()
	{
		return $this->_data;
	}

	/**
	 * Mix les données des offset $offsetDest dans $offsetSrc
	 * @param string $offsetSrc
	 * @param string $offsetDest
	 * @throws \Lightspeed\OutOfBoundsException
	 * @return Item
	 */
	public function mergeOffset($offsetSrc, $offsetDest)
	{
		if (!isset($this->_data[$offsetSrc]))
			throw new OutOfBoundsException("Offset source '$offsetSrc' doesn't exist.");
		if (!isset($this->_data[$offsetDest]))
			throw new OutOfBoundsException("Offset destination '$offsetDest' doesn't exist.");
		$this->_data[$offsetSrc] = array_merge($this->_data[$offsetSrc], $this->_data[$offsetDest]);
		return $this;
	}

	/**
	 * Mix les donnée entre l'item courant et $oConfigItem
	 * @param Item $oConfigItem
	 * @return Item
	 */
	public function mergeWithItem(Item $oConfigItem)
	{
		$this->_data = array_merge($this->_data, $oConfigItem->getArrayCopy());
		return $this;
	}


	/**
	 * Renvoi le nombre de clé dans l'item
	 * @return int
	 */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * Verifi l'existance d'une clé dans l'item
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->_data[$offset]);
	}

	/**
	 * Renvoi la valeur de l'offset, si l'offset n'existe un WARNING est envoyé
	 * @param string $offset
	 * @return mixed|Item
	 */
	public function offsetGet($offset) {
		if (!isset($this->_data[$offset])){
			trigger_error("Offset '$offset' doesn't exist.", E_USER_WARNING);
			return null;
		}
		return is_array($this->_data[$offset]) ? new self($this->_data[$offset]) : $this->_data[$offset];
	}

	/**
	 * Assigne une valeur a une clé de l'item
	 * @param string $offset
	 * @param string|array $value
	 */
	public function offsetSet($offset, $value) {
		$this->_data[$offset] = $value;
	}

	/**
	 * Supprime un offset de l'item
	 * @param string $offset
	 */
	public function offsetUnset($offset) {
		if (isset($this->_data[$offset]))
			unset($this->_data[$offset]);
	}

}