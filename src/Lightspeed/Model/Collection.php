<?php
/**
 * @author: Nicolas Levée
 * @version 210220131636
 */
namespace Lightspeed\Model;

use Lightspeed\InvalidArgumentException;

/**
 * Class Collection
 * @package Lightspeed\Arch\Model
 */
class Collection extends \ArrayIterator {

	/**
	 * @param Action[] $aListOfModel
	 * @param int $iFlags
	 * @throws InvalidArgumentException
	 */
	public function __construct($aListOfModel = array(), $iFlags = 0) {
		$bHasWrongType = count(array_filter($aListOfModel, function($item) {
			return !is_object($item) || !is_subclass_of($item, '\\Lightspeed\\Model\\Action');
		})) > 0;
		if (count($aListOfModel) > 0 && $bHasWrongType === true)
			throw new InvalidArgumentException("\$aListOfModel must be an array of \\Lightspeed\\Model\\Action");
		unset($bHasWrongType);
		parent::__construct($aListOfModel, $iFlags);
	}

	/**
	 * Ajoute un model a la collection
	 * @param Action $value
	 * @throws \Lightspeed\InvalidArgumentException
	 */
	public function append($value) {
		if (!is_object($value) || !is_subclass_of($value, '\\Lightspeed\\Model\\Action'))
			throw new InvalidArgumentException("value must be a \\Lightspeed\\Model\\Action");
		parent::append($value);
	}

	/**
	 * Renvoi un tableau des elements de la collection
	 * @return array
	 */
	public function getArrayCopy() {
		$aDataToReturn = array();
		foreach($this as $sKey=>$oValue)
			$aDataToReturn[$sKey] = $oValue->getArrayCopy();
		unset($sKey, $oValue);
		return $aDataToReturn;
	}

}