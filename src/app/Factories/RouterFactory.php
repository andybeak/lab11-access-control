<?php

namespace App\Factories;

use League\Route\Router;

class RouterFactory
{
    public static function makeRouter(): Router
    {
        $router = new Router();

        $router->map('GET', '/articles', 'App\Controllers\ArticlesController::showArticle');
        $router->map('POST', '/articles', 'App\Controllers\ArticlesController::createArticle');

        return $router;
    }
}