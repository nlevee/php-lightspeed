<?php
/**
 * @author: Nicolas Levée
 * @version 210820131622
 */

namespace Lightspeed\Middleware;

use Lightspeed\Formater\FormaterInterface;
use Lightspeed\Formater\String;
use Lightspeed\Http\Response;
use Lightspeed\Middleware;

/**
 * Class FormatOutput
 * @package Lightspeed\Middleware
 */
class FormatOutput extends Middleware {

	/**
	 * @var FormaterInterface[]
	 */
	protected $formater = array();


	/**
	 *
	 */
	public function __construct() {
		$this->addFormater("text", new String());
	}


	/**
	 * On ajoute un formater au début de la pile
	 * @param string $type
	 * @param \Lightspeed\Formater\FormaterInterface $formater
	 */
	public function addFormater($type, FormaterInterface $formater) {
		array_unshift($this->formater, array($type, $formater));
	}

	/**
	 * Cette fonction est a définir dans le middleware pour l'execution de l'action principal du middleware
	 * @param Response $response
	 * @return mixed
	 */
	public function call(Response &$response) {
		// formatage du body selon le request content
		foreach ($this->formater as $object) {
			if ($this->request->getAccept($object[0])) {
				$sConvertBody = $object[1]->convert($response->getBody(), $this->request);
				$response->setContentType($object[1]->getContentType());
				break;
			}
		}
		if (isset($sConvertBody))
			$response->setBody(trim($sConvertBody), true);
		$this->next($response);
	}
}