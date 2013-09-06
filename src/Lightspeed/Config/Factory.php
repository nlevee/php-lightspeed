<?php
/**
 * @author: Nicolas Levée
 * @version 190820131753
 */

namespace Lightspeed\Config;

use Lightspeed\DomainException;
use Lightspeed\UnexpectedValueException;

class Factory {

	/**
	 * Charge un fichier comme fichier de configuration
	 * si $sCurrentEnv on merge les donnée de la clé default et $sCurrentEnv directement
	 * @param string $sFileName
	 * @param null|string $sCurrentEnv
	 * @param string $sBaseConfigSection
	 * @throws \Lightspeed\DomainException
	 * @throws \Lightspeed\UnexpectedValueException
	 * @return Item
	 */
	public static function ReadFile($sFileName, $sCurrentEnv = null, $sBaseConfigSection = 'default') {
		// verification du fichier de config
		if (!is_file($sFileName) && is_readable($sFileName))
			throw new DomainException("File '$sFileName' doesn't exist or isn't readable");
		$sSplit = '.';
		switch (pathinfo($sFileName, PATHINFO_EXTENSION)) {
			case 'php':
				$aDataConfig = require $sFileName;
				break;
			case 'ini':
				$aDataConfig = parse_ini_file($sFileName, true);
				$sSplit = ':';
				break;
			default:
				throw new UnexpectedValueException("File '$sFileName' isn't recognize");
				break;
		}
		$oConf = new Item($aDataConfig, $sSplit);
		// tentative de merge avec l'env courant
		if (!empty($sCurrentEnv)) {
			try {
				$oConf = $oConf->mergeOffset($sBaseConfigSection, $sCurrentEnv);
			} catch (\Exception $e) { }
		}
		$oConf = $oConf[$sBaseConfigSection];
		// renvoi des données
		return $oConf;
	}

}