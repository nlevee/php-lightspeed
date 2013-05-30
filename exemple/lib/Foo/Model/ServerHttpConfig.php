<?php
/**
 * @author: Nicolas Levée
 * @version 090120131813
 */

namespace Foo\Model;

use Lightspeed\Model\Action;

/**
 * Class ServerHttpConfigModel
 */
class ServerHttpConfig extends Action {

	/**
	 * @var string|array
	 */
	protected $_idAttribute = 'id';

	/**
	 * @var string
	 */
	protected $_nameAttribute = 'server_http_config';


	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $content;

}

