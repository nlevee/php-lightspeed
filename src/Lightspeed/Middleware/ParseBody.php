<?php
/**
 * @author: Nicolas Levée
 * @version 280620131516
 */

namespace Lightspeed\Middleware;

use Lightspeed\Http\Response;
use Lightspeed\Middleware;

class ParseBody extends Middleware {


	/**
	 * Cette fonction est a définir dans le middleware pour l'execution de l'action principal du middleware
	 * @param Response $response
	 * @return mixed
	 */
	public function call(Response &$response) {

		$this->next->call($response);
	}

}