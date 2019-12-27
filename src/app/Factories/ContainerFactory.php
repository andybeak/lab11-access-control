<?php

namespace App\Factories;

use App\Factories\RouterFactory;
use Casbin\Enforcer;
use Pimple\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use App\Middleware\AuthorisationMiddleware;

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

        // include a response
        $container['response'] = function() {
            return new Response();
        };

        // include Casbin authorization service
        $container['casbin'] = function() {
            $configPath = __DIR__ . '/../auth/';
            return new Enforcer($configPath . 'model.conf', $configPath . 'policy.csv');
        };

        // add authorization middleware
        $container['AuthorizationMiddleware'] = function($container) {
            return (new AuthorisationMiddleware())->setAuthService($container['casbin']);
        };

        // add the router
        $container['router'] = function($container) {
            $router = RouterFactory::makeRouter();
            $router->middleware($container['AuthorizationMiddleware']);
            return $router;
        };


        return $container;
    }
}