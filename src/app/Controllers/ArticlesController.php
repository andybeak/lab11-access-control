<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticlesController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function showArticle(ServerRequestInterface $request) : ResponseInterface
    {
        die(__METHOD__);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function createArticle(ServerRequestInterface $request) : ResponseInterface
    {
        die(__METHOD__);
    }
}