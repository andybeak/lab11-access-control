<?php

namespace App;

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
        return $router->dispatch($request);
    }

}