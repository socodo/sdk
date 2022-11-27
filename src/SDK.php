<?php

namespace Socodo\SDK;

use Socodo\Router\Interfaces\RouteCollectionInterface;
use Socodo\SDK\Exceptions\SDKResolutionException;
use Socodo\SDK\Files\FileAbstract;
use Socodo\SDK\Files\InterfaceTs;
use Socodo\SDK\Files\NamespaceTs;
use Socodo\SDK\Files\StaticRaw;
use Socodo\SDK\Files\StaticJson;
use Socodo\SDK\Interfaces\SDKConfigInterface;
use Socodo\SDK\Interfaces\SDKInterface;

class SDK implements SDKInterface
{
    /** @var ?SDKConfigInterface SDK config. */
    protected ?SDKConfigInterface $config;

    /** @var ?RouteCollectionInterface Route collection. */
    protected ?RouteCollectionInterface $collection;

    /**
     * Constructor.
     *
     * @param SDKConfigInterface|null $config
     * @param RouteCollectionInterface|null $collection
     */
    public function __construct (SDKConfigInterface $config = null, RouteCollectionInterface $collection = null)
    {
        $this->config = $config;
        $this->collection = $collection;
    }

    /**
     * Set SDK config.
     *
     * @param SDKConfigInterface $config
     * @return void
     */
    public function setConfig (SDKConfigInterface $config): void
    {
        $this->config = $config;
    }

    /**
     * Set route collection.
     *
     * @param RouteCollectionInterface $collection
     * @return void
     */
    public function setRouteCollection (RouteCollectionInterface $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * Compile to typescript project.
     *
     * @param string $dirPath
     * @return void
     */
    public function compile (string $dirPath): void
    {
        if (!isset($this->config) || !isset($this->collection))
        {
            throw new SDKResolutionException(static::class . '::compile() Cannot compile SDK before SDKConfig and RouteCollection has been set.');
        }

        $writer = new Writer($dirPath);
        $writer->clearBasePath();
        foreach ($this->getWritings() as $writing)
        {
            $writer->write($writing);
        }
    }

    /**
     * Get all writings.
     *
     * @return array<FileAbstract>
     */
    protected function getWritings (): array
    {
        $writings = [];
        $writings[] = new StaticRaw('/.gitignore');
        $writings[] = new StaticRaw('/src/client.ts');
        $writings[] = new StaticJson('/tsconfig.json');

        $overrides = $this->config->getMetadata('packageJsonOverrides') ?? [];
        $overrides = [
            'name' => $this->config->getName(),
            ...$overrides
        ];
        $packageJson = new StaticJson('/package.json');
        $packageJson->setOverrides($overrides);
        $writings[] = $packageJson;

        $overrides = $this->config->getMetadata('appConfigOverrides') ?? [];
        $overrides = [
            'baseUrl' => $this->config->getBaseUrl(),
            ...$overrides
        ];
        $appConfigJson = new StaticJson('/src/config.json');
        $appConfigJson->setOverrides($overrides);
        $writings[] = $appConfigJson;

        $collected = $this->collect();
        return array_merge($writings, $collected);
    }

    /**
     * Collect namespaces and interfaces from RouteCollection.
     *
     * @return array<FileAbstract>
     */
    protected function collect (): array
    {
        $collector = new Collector(
            $this->config->getMetadata('controllerPrefix'),
            $this->config->getMetadata('structurePrefix'),
            $this->config->getMetadata('structureSuperClass')
        );
        $collected = $collector->collect($this->collection);

        $namespaces = [];
        foreach ($collected['namespaces'] as $name => $data)
        {
            $namespaces[] = new NamespaceTs($name, $data);
        }

        $interfaces = [];
        foreach ($collected['interfaces'] as $name => $data)
        {
            $interfaces[] = new InterfaceTs($name, $data);
        }

        return array_merge($namespaces, $interfaces);
    }
}