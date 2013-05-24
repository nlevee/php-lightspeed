<?php
/**
 * @author: Nicolas Levée
 * @version 240520131627
 */

class PDOHandler extends \PDO {

	/**
	 * @var array
	 */
	private static $_instance = array();

	/**
	 * @param string $sName
	 * @return PDO
	 */
	public static function getInstance($sName = 'default') {
		if (!isset(self::$_instance[$sName]))
			self::$_instance[$sName] = new self();
		return self::$_instance[$sName];
	}

	/**
	 *
	 */
	public function __construct() {
		parent::__construct("mysql:dbname=nlevee;host=gary.idobs", "nlevee", "devine", array(
			self::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			self::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			self::ATTR_EMULATE_PREPARES => false
		));
		$this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
	}
}