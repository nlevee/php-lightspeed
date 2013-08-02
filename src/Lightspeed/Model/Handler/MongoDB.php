<?php
/**
 * @author: Nicolas Levée
 * @version 280620131145
 */

namespace Lightspeed\Model\Handler;

use Lightspeed\Model\Action;
use Lightspeed\Model\Handler;

/**
 * Class MongoDB
 * @package Lightspeed\Model\Handler
 */
class MongoDB extends Handler\Action {

	/**
	 * @var \MongoClient
	 */
	protected $_mongodb;

	/**
	 * @var string
	 */
	protected $_dbname;


	/**
	 * @param \MongoClient $oMongoCli
	 * @param string $dbname
	 */
	public function __construct(\MongoClient $oMongoCli, $dbname) {
		$this->_mongodb = $oMongoCli;
		$this->_dbname = $dbname;
	}

	/**
	 * @param Action $oModelObject
	 * @return \MongoCollection
	 */
	public function getCollection(Action &$oModelObject) {
		return $this->_mongodb->selectDB($this->_dbname)->selectCollection($oModelObject->getNameAttribute());
	}

	/**
	 * Charge les données du model dans l'objet Model
	 * @param Action $oModelObject
	 * @param array $aExcludeFields
	 * @return bool
	 */
	public function fetchInto(Action &$oModelObject, array $aExcludeFields = array()) {
		$where = array();
		foreach($oModelObject->getIdAttribute(true) as $sFieldName)
			$where[$sFieldName] = $oModelObject[$sFieldName];
		$where = array_filter($where);
		if (!empty($aExcludeFields)) {
			$aExcludeFields = array_combine($aExcludeFields, array_fill(0, count($aExcludeFields), 0));
		}
		if (empty($where) || !($dataset = $this->getCollection($oModelObject)->findOne($where, $aExcludeFields)))
			return false;
		$oModelObject->loadFromArray($dataset);
		return true;
	}

	/**
	 * Sauvegarde les données d'un model du systeme de gestion,
	 * Si le model existe on le met a jour sinon on le crée
	 * @param Action $oModelObject
	 * @return bool
	 */
	public function saveFrom(Action &$oModelObject) {
		$where = array();
		foreach($oModelObject->getIdAttribute(true) as $sFieldName)
			$where[$sFieldName] = $oModelObject[$sFieldName];
		$where = array_filter($where);
		$aDataToInsert = array_filter($oModelObject->getArrayCopy());
		if (!empty($where)) {
			$bResult = $this->getCollection($oModelObject)->update($where, array(
				'$set' => array_diff_key($aDataToInsert, $where)
			), array('upsert' => true));
		} else
			$bResult = $this->getCollection($oModelObject)->save($aDataToInsert);
		if (isset($bResult['ok']) && $bResult['ok'] == 1) {
			$oModelObject->loadFromArray($aDataToInsert);
			return true;
		}
		return false;
	}

	/**
	 * Supprime les donnée d'un model du systeme de gestion
	 * @param Action $oModelObject
	 * @return bool
	 */
	public function removeFrom(Action &$oModelObject) {
		$where = array();
		foreach($oModelObject->getIdAttribute(true) as $sFieldName)
			$where[$sFieldName] = $oModelObject[$sFieldName];
		$where = array_filter($where);
		if (empty($where) || !$this->exist($oModelObject))
			return false;
		$bResult = $this->getCollection($oModelObject)->remove($where);
		if (isset($bResult['ok']) && $bResult['ok'] == 1) {
			unset($oModelObject);
			return true;
		}
		return false;
	}

	/**
	 * Verification de l'existance des données du model dans le systeme de gestion
	 * @param Action $oModelObject
	 * @return bool
	 */
	public function exist(Action $oModelObject) {
		$where = array();
		foreach($oModelObject->getIdAttribute(true) as $sFieldName)
			$where[$sFieldName] = $oModelObject[$sFieldName];
		$where = array_filter($where);
		if (empty($where) || !($dataset = $this->getCollection($oModelObject)->findOne($where)))
			return false;
		return true;
	}

	/**
	 * On recupere une liste simple d'une partie ou de la totalité des element du systeme
	 * @param Action $oModelObject
	 * @param int $limit
	 * @param int $offset
	 * @param array $aExcludeFields
	 * @return Action[]
	 */
	public function fetchSetInto(Action $oModelObject, $limit = -1, $offset = 0, array $aExcludeFields = array()) {
		$oCollection = $this->getCollection($oModelObject)->find()->skip($offset);
		if ($limit > 0)
			$oCollection = $oCollection->limit($limit);
		return array_map(function($item) use ($oModelObject){
			$oNewModel = clone $oModelObject;
			$oNewModel->loadFromArray($item);
			return $oNewModel;
		}, iterator_to_array($oCollection));
	}

	/**
	 * Renvoi le nombre de ligne d'une collection de row
	 * @param Action $oModelObject
	 * @return int
	 */
	public function getRowCount(Action $oModelObject) {
		return $this->getCollection($oModelObject)->count();
	}
}