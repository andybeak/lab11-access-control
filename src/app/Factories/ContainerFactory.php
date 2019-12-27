<?php

namespace App\Factories;

use Pimple\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use App\Factories\RouterFactory;

class ContainerFactory
{
    /**
     * @return Container
     */
    public static function makeContainer(): Container
    {
        // construct DI container
        $container = new Container();

        // add logger
        $container['logger'] = function () {
            // Create the logger
            $logger = new Logger('default');
            // Now add some handlers
            $logger->pushHandler(new StreamHandler(
                    __DIR__ . '/../log/' . date('Y-m-d') . '.log',
                    Logger::DEBUG)
            );
            return $logger;
        };

        // build the request into the container
        $container['request'] = function() {
            return ServerRequestFactory::fromGlobals(
                $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
            );
        };

        // add the router
        $container['router'] = function() {
            return RouterFactory::makeRouter();
        };

        // include a response
        $container['response'] = function() {
            return new Response();
        };

        return $container;
    }
}