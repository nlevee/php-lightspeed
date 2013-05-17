<?php
// affiche les erreurs
ini_set('display_errors', true);

// définition des constante
define("BASE_PATH", realpath(__DIR__ . '/../') );

// include path
set_include_path( get_include_path() .
	PATH_SEPARATOR . BASE_PATH."/../src/" );

// demarrage du loader
require_once 'Lightspeed/Autoload.php';
\Lightspeed\Autoload::register();

// demarrage de l'application
$app = new \Lightspeed\App( new \Lightspeed\Http\Request() );
$app->dispatch(new \Lightspeed\Http\Response());