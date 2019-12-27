<?php

require('../vendor/autoload.php');

$app = new App\Main;

$response = $app->run();

// send the response to the browser
(new Zend\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);