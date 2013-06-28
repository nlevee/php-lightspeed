<?php
/**
 * @author: Nicolas Levée
 * @version 160120131216
 */
namespace Lightspeed\Model\Handler;

use Lightspeed\InvalidArgumentException;
use Lightspeed\Model\Action;

class PDO extends Action {


	/**
	 * @var null|\PDOStatement charge la requete préparé du loadInto
	 */
	private $_stmtLoadInto = null;


	/**
	 * @var string nom de la table
	 */
	protected $_tableName;

	/**
	 * @var array nom de la/les colonnes primaire
	 */
	protected $_primaryCols;

	/**
	 * @var \PDO
	 */
	protected $_pdo;


	/**
	 * @param \PDO $oPdoInstance
	 */
	public function __construct(\PDO $oPdoInstance) {
		$this->_pdo = $oPdoInstance;
	}


	/**
	 * Generation d'une portion de Where
	 * @param Action $oModelObject
	 * @param array $aCols
	 * @param array $aDataExecute
	 * @param string $sGlue par default 'AND'
	 * @return array
	 */
	protected function _generateWhereString(Action $oModelObject, array $aCols, array &$aDataExecute, $sGlue = 'AND') {
		// fabrication du Where
		$aWhereString = array();
		foreach($aCols as $sColName) {
			$aDataExecute[] = $oModelObject->{$sColName};
			$aWhereString[] = ' `' . preg_replace("@\.@", "`.`", $sColName) . '` = ? ';
		}
		$aDataExecute = array_filter($aDataExecute);
		return '(' . implode(trim($sGlue), $aWhereString) . ')';
	}

	/**
	 * Renvoi le nom de la table lié au model protégé pour SQL
	 * @param Action $oModelObject
	 * @return string
	 */
	protected function _getSqlTableName(Action $oModelObject) {
		return '`' . preg_replace("@\.@", "`.`", $oModelObject->getNameAttribute()) . '`';
	}

	/**
	 * Fabrique la reqete le load si besoin et renvoi les donnée a envoyé pour la requete
	 * @param Action $oModelObject
	 * @return array
	 */
	protected function _makeSqlLoadInto(Action $oModelObject) {
		// fabrication du Where
		$aDataExecute = array();
		$sWhere = $this->_generateWhereString($oModelObject, array_keys($this->_getPrimaryCols($oModelObject)), $aDataExecute);
		// requete au serveur
		if (null === $this->_stmtLoadInto) {
			$sSqlLoadInto = "SELECT * FROM ".$this->_getSqlTableName($oModelObject)." WHERE " . $sWhere;
			$this->_stmtLoadInto = $this->_pdo->prepare($sSqlLoadInto);
			$this->_stmtLoadInto->setFetchMode(\PDO::FETCH_INTO, $oModelObject);
		}
		return $aDataExecute;
	}

	/**
	 * Renvoi la liste des colonne primaire, si c'est un tableau on veux
	 *      array ( 'ColName' => true/false : autoInc )
	 * Par default est a true si c'est un string
	 * @param Action $oModelObject
	 * @throws InvalidArgumentException
	 * @return array|string
	 */
	protected function _getPrimaryCols(Action $oModelObject) {
		$mPrimaryCol = $oModelObject->getIdAttribute();
		$mPrimaryCol = is_array($mPrimaryCol) ? $mPrimaryCol : array($mPrimaryCol => true);
		// verification des données entré
		foreach($mPrimaryCol as $sColName => $sAutoInc) {
			if (!is_string($sColName) || !is_bool($sAutoInc))
				throw new InvalidArgumentException("Le tableau doit être de la forme : array ( 'ColName' => true/false : autoInc )");
		}
		return $mPrimaryCol;
	}


	/**
	 * @param Action $oModelObject
	 * @param int $limit
	 * @param int $offset
	 * @return Action[]
	 */
	public function fetchSetInto(Action $oModelObject, $limit = -1, $offset = 0) {
		// si la limite est supperieur a 0 on la place
		$sqllimit = $limit > 0 ? ' LIMIT ' . $offset . ', ' . $limit : '';
		// requete
		$stmt = $this->_pdo->query("SELECT * FROM ".$this->_getSqlTableName($oModelObject).$sqllimit);
		$stmt->setFetchMode(\PDO::FETCH_CLASS, get_class($oModelObject));
		return $stmt->fetchAll();
	}

	/**
	 * Charge les données du model dans l'objet Model
	 * @param Action $oModelObject
	 * @return Action|bool
	 */
	public function fetchInto(Action &$oModelObject) {
		// fabrication de la requete
		$aDataExecute = $this->_makeSqlLoadInto($oModelObject);
		if (!empty($aDataExecute) && $this->_stmtLoadInto->execute($aDataExecute)) {
			$this->_stmtLoadInto->fetch();
			$this->_stmtLoadInto->closeCursor();
			return $this->_stmtLoadInto->rowCount() !== 0;
		} else {
			\trigger_error("Aucune donnée en paramètre", E_USER_WARNING);
			return false;
		}
	}

	/**
	 * @param Action $oModelObject
	 * @throws LogicException
	 * @return bool
	 */
	public function saveFrom(Action &$oModelObject) {
		$aPropertiesModif = $oModelObject->getChangedProperties();
		if (empty($aPropertiesModif))
			return true;
		$sSql = "";
		foreach ($this->_getPrimaryCols($oModelObject) as $sColName => $sAutoInc) {
			// si la colonne est en auto inc et qu'elle n'est pas renseigné, c'est un insert!
			// si la colonne est en manuel ou qu'elle est renseigné on est en update
			if ($sAutoInc === true && !$oModelObject->{$sColName}) {
				$sSql = $sSql ?: "INSERT INTO ";
			} else {
				$sSql = $sSql ?: "UPDATE FROM ";
				// on est en manuel il faut donc envoyé la valeur dans les colonnes a modif
				if (!empty($oModelObject->{$sColName}) && !in_array($sColName, $aPropertiesModif))
					$aPropertiesModif[] = $sColName;
			}
		}

		// si les valeur de primary col sont fourni :
		$sSql .= $this->_getSqlTableName($oModelObject);

		// récuperation de la connection et requete
		//$stmt = $this->_pdo->prepare("");
	}

	/**
	 * Supprime les donnée d'un model du systeme de gestion
	 * @param Action $oModelObject
	 */
	public function removeFrom(Action &$oModelObject) {
		// TODO: Implement removeFrom() method.
	}

	/**
	 * Verification de l'existance des données du model dans le systeme de gestion
	 * @param Action $oModelObject
	 * @return bool
	 */
	public function exist(Action &$oModelObject) {
		// fabrication de la requete
		$aDataExecute = $this->_makeSqlLoadInto($oModelObject);
		// verification des données présente en base
		if (!empty($aDataExecute) && $this->_stmtLoadInto->execute($aDataExecute))
			return $this->_stmtLoadInto->rowCount() !== 0;
		return false;
	}

	/**
	 * Renvoi le nombre de ligne d'une collection de row
	 * @param Action $oModelObject
	 * @return int
	 */
	public function getRowCount(Action $oModelObject) {
		// requete
		$stmt = $this->_pdo->query("SELECT * FROM ".$this->_getSqlTableName($oModelObject));
		return $stmt->rowCount();
	}
}
