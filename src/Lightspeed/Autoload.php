<?php
/**
 * @author: Nicolas Levée
 * @version 160520131826
 */

namespace Lightspeed;

/**
 * Class Autoload
 * @package Lightspeed
 */
class Autoload {

	/**
	 * Enregistre la classe comme gestionnaire de chargement
	 * @static
	 */
	public static function register() {
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	/**
	 * Permet le chargement des classes
	 * les _ et \ sont converti en / les dossiers doivent correspondre
	 * a cette nomanclature
	 * @static
	 * @param string $sClassName
	 * @throws DomainException
	 */
	public static function autoload($sClassName) {
		// mise en forme du nom de classe
		$sClassName = preg_replace('@_|/+@', DIRECTORY_SEPARATOR, strtr($sClassName, '\\', DIRECTORY_SEPARATOR));
		if (!empty($sClassName)) {
			if (preg_match("@Exception$@", $sClassName)) {
				$aPartClass = explode("/", $sClassName);
				array_pop($aPartClass);
				array_push($aPartClass, "Exception");
				$sClassName = implode("/",  $aPartClass);
			}
			$aIncludePaths = explode(PATH_SEPARATOR, get_include_path());
			foreach($aIncludePaths as $sPath) {
				$sFileName = $sPath . '/' . $sClassName . '.php';
				if (file_exists($sFileName))
					break;
			}
			require $sFileName;
		}
	}

}