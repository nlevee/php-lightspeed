<?php
// affiche les erreurs
ini_set('display_errors', true);

// définition des constante
define("BASE_PATH", realpath(__DIR__ . '/../') );

// include path
set_include_path( get_include_path() .
	PATH_SEPARATOR . BASE_PATH . "/" .
	PATH_SEPARATOR . BASE_PATH . "/Foo/" .
	PATH_SEPARATOR . BASE_PATH . "/../src/" );

// demarrage du loader
require_once 'Lightspeed/Autoload.php';
\Lightspeed\Autoload::register();

// demarrage de l'application
$app = new \Lightspeed\App();

// Front Controller
$controller = new \Lightspeed\Middleware\Controller();
$app->prepend($controller);

// Routeur
$router = new \Lightspeed\Middleware\Router();
$router->get("/article", function($request) {
	$request->setParam('action', 'articles');
});
$router->get("/.*", function($request) {
	$request->setParam('action', 'test');
});
$app->prepend($router);

// ecoute
$app->listen();