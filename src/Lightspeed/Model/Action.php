<?php
/**
 * @author: Nicolas Levée
 * @version 160120131215
 */
namespace Lightspeed\Model;

use Lightspeed\Http\Request;
use Lightspeed\UnexpectedValueException;

/**
 * Class Action
 * @package Lightspeed\Model
 */
abstract class Action implements \ArrayAccess {

	/**
	 * @var bool
	 */
	private $_dataLoaded = false;

	/**
	 * @var array index des propriété modifié dans le model
	 */
	private $_dataChanged = array();

	/**
	 * @var \ReflectionClass
	 */
	private $_reflectionClass;


	/**
	 * @var array tableau des properties ignoré
	 */
	protected $_ignoreProperties = array();

	/**
	 * @var string|array
	 */
	protected $_idAttribute = 'id';

	/**
	 * @var string
	 */
	protected $_nameAttribute = '';


	/**
	 * @param int|int[] $ident
	 * @throws UnexpectedValueException
	 */
	public function __construct($ident = null) {
		if (!$this->_nameAttribute)
			throw new UnexpectedValueException("Property '_nameAttribute' must be define");
		if (!$this->_idAttribute)
			throw new UnexpectedValueException("Property '_idAttribute' must be define");
		$this->_reflectionClass = new \ReflectionClass($this);
		if ($ident !== null) {
			$aListId = $this->getIdAttribute(true);
			if (!is_array($ident) && count($aListId) == 1)
				$ident = array($aListId[0] => $ident);
			foreach($aListId as $sFieldName)
				if (isset($ident[$sFieldName]))
					$this->__set($sFieldName, $ident[$sFieldName]);
		}
	}

	/**
	 * Fonction appeler pour le call, permet la remise a zero de l'id
	 */
	public function __clone() {
		foreach($this->getIdAttribute(true) as $sFieldName)
			unset($this[$sFieldName]);
	}

	/**
	 * met a jour une property du model de donnée
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		// uniquement les properties non privée
		if (!isset($this[$name])) {
			trigger_error("Property $name is not editable in " . __CLASS__, E_USER_NOTICE);
		} else {
			// stockage de l'action réalisé
			if ($this->_dataLoaded !== false && $this->{$name} != $value)
				$this->_dataChanged[] = $name;
			// assign la nouvelle valeur
			$this->{$name} = $value;
		}
	}

	/**
	 * Récupere la valeur d'une property du model de donnée
	 * @param string $name
	 * @return mixed|null
	 */
	public function __get($name) {
		if (!isset($this[$name])) {
			trigger_error("Property $name is not readable in " . __CLASS__, E_USER_NOTICE);
			return null;
		}
		// retour de la valeur
		return $this->{$name};
	}

	/**
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return in_array($offset, $this->getAccessProperties());
	}

	/**
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->__get($offset);
	}

	/**
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->__set($offset, $value);
	}

	/**
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->__set($offset, null);
	}

	/**
	 * Renvoi l'attribut 'id', si $bForceInArray est a true on renvoi forcément un tableau
	 * @param bool $bForceInArray
	 * @return string|array
	 */
	public function getIdAttribute($bForceInArray = false) {
		return $bForceInArray === true && !is_array($this->_idAttribute) ? array($this->_idAttribute) : $this->_idAttribute;
	}

	/**
	 * Renvoi l'attribut 'name'
	 * @return string
	 */
	public function getNameAttribute() {
		return $this->_nameAttribute;
	}

	/**
	 * Renvoi une copie de l'objet sous forme de tableau,
	 * si $bOnlyChange est a true on ne récupere que les donnée modifier
	 * @param bool $bOnlyChange
	 * @return array
	 */
	public function getArrayCopy($bOnlyChange = false) {
		$aProperties = $bOnlyChange ? $this->getChangedProperties() : $this->getAccessProperties();
		$aValues = array();
		foreach($aProperties as $sProperty) {
			$aValues[$sProperty] = $this->__get($sProperty);
		}
		return $aValues;
	}

	/**
	 * Renvoi la listes des properties modifiable par le client
	 * @return array
	 */
	public function getAccessProperties() {
		$aProperties = $this->_reflectionClass->getProperties(\ReflectionProperty::IS_PROTECTED);
		$aIgnore = array_merge($this->_ignoreProperties, array(
			'_idAttribute', '_nameAttribute', '_ignoreProperties'
		));
		return array_filter(array_map(function($item) {
			return $item->getName();
		}, $aProperties), function($item) use ($aIgnore) {
			return !in_array($item, $aIgnore);
		});
	}

	/**
	 * Renvoi le tableau de property du model qui ont été modifié
	 * @return array
	 */
	public function getChangedProperties() {
		return $this->_dataChanged = array_unique($this->_dataChanged, SORT_REGULAR);
	}


	/**
	 * Charge les données depuis le POST/PUT, on ne prend que les donnée correspodante a $aPostField
	 * les valeur de $aPostField sont des regex pour la selection des champs
	 * Si la clé de $aPostField est une chaine on prend sa valeur a la place de la valeur du champs POST
	 * Le tableau $aBind fait correspondre la clé de request au champs dans la base si
	 * non rempli on utilise la clé dans la request
	 * Les champs sont ensuite transformé en CamelCase pour le set des valeur
	 * @param Request $oRequest
	 * @param array $aPostField
	 * @param array $aBind
	 * @param array $aExclude
	 */
	public function loadFromRequest(Request $oRequest, array $aPostField, array $aBind = array(), array $aExclude = array()) {
		$aPostFullData = $oRequest->getInputParams($aExclude);
		$this->loadFromArray($aPostFullData, $aPostField, $aBind);
	}

	/**
	 * Charge les données depuis un tableau, on ne prend que les donnée correspodante au valeur de $aArrayData
	 * 	les valeur de $aArrayData sont des regex pour la selection des champs
	 * Si la clé de $aArrayData est une chaine on prend sa valeur a la place de la valeur du champs
	 * Le tableau $aBind fait correspondre la clé de request au champs dans la base si
	 * non rempli on utilise la clé dans $aArrayData
	 * Si $aPostField est vide on suppose que les données sont dans $aArrayData
	 * @param array $aArrayData
	 * @param array $aPostField
	 * @param array $aBind
	 */
	public function loadFromArray(array $aArrayData, array $aPostField = array(), array $aBind = array()){
		$aData = array();
		if (empty($aPostField))
			$aData = $aArrayData;
		// récuperation des données depuis le post
		foreach($aPostField as $sFieldKey=>$sFieldName){
			if (!is_numeric($sFieldKey)){
				$aData[$sFieldKey] = $sFieldName;
			}else{
				$sCountField = count($aData);
				foreach($aArrayData as $sPostName=>$sPostValue){
					if (preg_match('#^'.$sFieldName.'$#i', $sPostName)){
						if (isset($aBind[$sPostName]))
							$sPostName = $aBind[$sPostName];
						$aData[$sPostName] = $sPostValue ?: null;
					}
				}
				if ($sCountField == count($aData))
					$aData[$sFieldName] = null;
			}
		}
		unset($aPostField);
		// insertion dans les champs des valeurs
		foreach($aData as $sFieldName => $sFieldValue){
			if ($sFieldValue !== null && isset($this[$sFieldName])){
				call_user_func_array(array($this, '__set'), array($sFieldName, $sFieldValue) );
			}
		}
		unset($aData);
	}

}