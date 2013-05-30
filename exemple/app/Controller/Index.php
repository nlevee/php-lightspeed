<?php
/**
 * @author: Nicolas Levée
 * @version 210520131720
 */

namespace Controller;

use Foo\Model\ServerHttpConfig;
use Foo\PDOHandler;
use Lightspeed\Controller;
use Lightspeed\Http\Response;
use Lightspeed\Model\Collection;
use Lightspeed\Model\Handler\PDO;

/**
 * Class Index
 * @package Controller
 */
class Index extends Controller {

	protected function testAction(Response $response) {
		// Récuperation d'un Model
		$oModel = new ServerHttpConfig();
		// Récuperation d'un Handler
		$oHandler = new PDO(PDOHandler::getInstance());
		// récuperation d'une collection
		$oCollection = new Collection($oHandler->fetchSetInto($oModel));
		// envoi de la réponse
		$response->setContentType('application/json');
		return json_encode($oCollection->getArrayCopy());
	}

	protected function articlesAction(Response $response) {
		// Récuperation d'un Model
		$oModel = new ServerHttpConfig();
		// Récuperation d'un Handler
		$oHandler = new PDO(PDOHandler::getInstance());
		// récuperation d'une collection
		$oCollection = new Collection($oHandler->fetchSetInto($oModel));
		// envoi de la réponse
		$m = new \Mustache_Engine;
		return $m->render('Hello, {{planet}}!', array('planet' => 'World'));
	}


}