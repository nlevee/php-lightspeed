<?php
/**
 * @author: Nicolas Levée
 * @version 300520131548
 */

namespace Foo\Share;

use Lightspeed\Share;

/**
 * Class Mustache
 * @package Foo
 */
class MustacheEngine implements Share {

	/**
	 * @var \Mustache_Engine
	 */
	protected $_instance = null;

	/**
	 * Creation de l'instance du service
	 * @param array $options
	 * @return \Mustache_Engine
	 */
	public function getInstance(array $options = array()) {
		return $this->_instance = $this->_instance ?: new \Foo\Adapter\MustacheEngine($options);
	}
}