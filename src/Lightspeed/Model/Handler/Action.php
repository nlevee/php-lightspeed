<?php
/**
 * @author: Nicolas Levée
 * @version 160120131215
 */

namespace Lightspeed\Model\Handler;

/**
 * Class Action
 * @package Lightspeed\Model\Handler
 */
abstract class Action {

	/**
	 * Charge les données du model dans l'objet Model
	 * @param Action $oModelObject
	 * @return bool
	 */
	abstract public function fetchInto(Action &$oModelObject);

	/**
	 * Sauvegarde les données d'un model du systeme de gestion,
	 * Si le model existe on le met a jour sinon on le crée
	 * @param Action $oModelObject
	 * @return bool
	 */
	abstract public function saveFrom(Action &$oModelObject);

	/**
	 * Supprime les donnée d'un model du systeme de gestion
	 * @param Action $oModelObject
	 * @return bool
	 */
	abstract public function removeFrom(Action &$oModelObject);

	/**
	 * Verification de l'existance des données du model dans le systeme de gestion
	 * @param Action $oModelObject
	 * @return bool
	 */
	abstract public function exist(Action &$oModelObject);

	/**
	 * On recupere une liste simple d'une partie ou de la totalité des element du systeme
	 * @param Action $oModelObject
	 * @param int $offset
	 * @param int $limit
	 * @return Action[]
	 */
	abstract public function fetchSetInto(Action $oModelObject, $limit = -1, $offset = 0);


	/**
	 * Mise a jour des données d'un model dans le systeme de gestion
	 * si le model n'existe pas deja on lance une notice et rien n'est fait
	 * @param Action $oModelObject
	 * @return bool
	 */
	public function updateFrom(Action &$oModelObject) {
		if ($this->exist($oModelObject) === true)
			return $this->saveFrom($oModelObject);
		return false;
	}

	/**
	 * Ajoute les données d'un model au systeme de gestion si le model n'existe pas deja
	 * si elle existe on ne fait rien et on envoi une notice
	 * @param Action $oModelObject
	 * @return bool
	 */
	public function addFrom(Action &$oModelObject) {
		if ($this->exist($oModelObject) === false)
			return $this->saveFrom($oModelObject);
		return false;
	}

}