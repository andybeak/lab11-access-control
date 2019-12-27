<?php

namespace App\Factories;

use Pimple\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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

        return $container;
    }
}