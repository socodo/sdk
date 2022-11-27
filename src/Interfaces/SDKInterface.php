<?php

namespace Socodo\SDK\Interfaces;

use Socodo\Router\Interfaces\RouteCollectionInterface;

interface SDKInterface
{
    /**
     * Set SDK config.
     *
     * @param SDKConfigInterface $config
     * @return void
     */
    public function setConfig (SDKConfigInterface $config): void;

    /**
     * Set route collection.
     *
     * @param RouteCollectionInterface $collection
     * @return void
     */
    public function setRouteCollection (RouteCollectionInterface $collection): void;

    /**
     * Compile to SDK project.
     *
     * @param string $dirPath
     * @return void
     */
    public function compile (string $dirPath): void;
}