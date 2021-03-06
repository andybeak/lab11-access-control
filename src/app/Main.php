<?php

namespace App;

use App\Exceptions\BadHTTPMethodException;
use App\Exceptions\NotAuthenticatedException;
use App\Exceptions\NotAuthorizedException;
use App\Factories\ContainerFactory;
use Psr\Http\Message\ResponseInterface;

class Main
{
    /**
     * @var \Pimple\Container
     */
    private $container;

    /**
     * Main constructor.
     */
    public function __construct()
    {
        $this->container = ContainerFactory::makeContainer();
    }

    /**
     * @return ResponseInterface
     */
    public function run(): ResponseInterface
    {
        $router = $this->container['router'];
        $request = $this->container['request'];
        $response = $this->container['response'];
        try {
            $response = $router->dispatch($request);
        } catch (NotAuthenticatedException $e) {
            $response = $response->withStatus(401, $e->getMessage());
            $response->getBody()->write($e->getMessage());
        } catch (NotAuthorizedException $e) {
            $response = $response->withStatus(403, $e->getMessage());
            $response->getBody()->write($e->getMessage());
        } finally {
            return $response;
        }
    }
}