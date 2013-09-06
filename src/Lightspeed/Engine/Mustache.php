<?php
/**
 * @author: Nicolas Levée
 * @version 290820131654
 */

namespace Lightspeed\Engine;

use Lightspeed\Config\Item;

/**
 * Class Mustache
 * @package Lightspeed\Engine
 */
class Mustache extends \Mustache_Engine
	implements EngineInterface {

	/**
	 * $opts => array(
	 * 		opts => array(all contructor's options),
	 *		template => array(
	 *			path => '/directory/to/template',
	 * 			ext => '.extention'
	 *		)
	 * )
	 * @param Item $opts
	 */
	public function __construct(Item $opts) {
		parent::__construct($opts['opts']->getArrayCopy());
		// assign du loader de template
		if (isset($opts->template->path)) {
			$this->setLoader(
				new \Mustache_Loader_FilesystemLoader($opts->template->path, array(
					'extension' => isset($opts->template->ext) ? $opts->template->ext : '.tpl'
				))
			);
		}
	}

}