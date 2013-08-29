<?php
/**
 * @author: Nicolas Levée
 * @version 260820131217
 */

namespace Lightspeed\Model\Handler;

use Lightspeed\Config\Item;
use Lightspeed\UnexpectedValueException;

/**
 * Class Factory
 * @package Lightspeed\Model\Handler
 */
class Factory {

	/**
	 * Renvoi une instance de mongo
	 * @param Item $oMongoConf
	 * @throws \MongoConnectionException
	 * @throws \Lightspeed\UnexpectedValueException
	 * @return MongoDB
	 */
	public static function getMongoInstance(Item $oMongoConf) {
		try {
			// Récuperation d'un Handler
			if (!isset($oMongoConf->server) || empty($oMongoConf->server) || !isset($oMongoConf->name) || empty($oMongoConf->name))
				throw new UnexpectedValueException('La configuration du serveur mongodb n\'est pas complète.');
			$oMongoCli = new \MongoClient($oMongoConf->server, isset($oMongoConf->opts) ? $oMongoConf->opts->getArrayCopy() : array());
			return new MongoDB($oMongoCli, $oMongoConf->name);
		} catch(\MongoConnectionException $e) {
			error_log((string) $e);
			throw $e;
		}
	}

	/**
	 * Renvoi une instance de handler de bd
	 * @param Item $oHandlerConfig
	 * @return Action
	 * @throws \Lightspeed\UnexpectedValueException
	 */
	public static function getInstance(Item $oHandlerConfig) {
		if (isset($oHandlerConfig->server) && preg_match("@^mongodb://.+$@i", $oHandlerConfig->server))
			return self::getMongoInstance($oHandlerConfig);
		throw new UnexpectedValueException('Impossible de reconnaitre le type de handler');
	}

}