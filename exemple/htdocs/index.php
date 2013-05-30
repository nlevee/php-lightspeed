<?php
// affiche les erreurs
ini_set('display_errors', true);

// définition des constante
define("BASE_PATH", realpath(__DIR__ . '/../') );
define("APP_PATH", BASE_PATH . "/app" );
define("RESOURCES_PATH", APP_PATH . "/Resources" );

// include path
set_include_path( get_include_path() .
	PATH_SEPARATOR . APP_PATH .
	PATH_SEPARATOR . BASE_PATH . "/lib" .
	PATH_SEPARATOR . BASE_PATH . "/../src" );

// demarrage du loader
require_once 'Lightspeed/Autoload.php';
\Lightspeed\Autoload::register();

// demarrage de l'application
$app = new \Lightspeed\App();

// ajout du systeme de vue
$app->shareAs('engine', new \Foo\Share\MustacheEngine, array(
	'loader' => new \Mustache_Loader_FilesystemLoader(RESOURCES_PATH . '/views', array(
		'extension' => '.ms'
	)),
	'partials_loader' => new \Mustache_Loader_FilesystemLoader(RESOURCES_PATH . '/partials', array(
		'extension' => '.ms'
	)),
	'cache' => '/tmp/cache/mustache',
));

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