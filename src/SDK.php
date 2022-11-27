<?php

namespace Socodo\SDK;

use Socodo\Router\Interfaces\RouteCollectionInterface;
use Socodo\SDK\Files\FileAbstract;
use Socodo\SDK\Files\InterfaceTs;
use Socodo\SDK\Files\NamespaceTs;
use Socodo\SDK\Files\StaticRaw;
use Socodo\SDK\Files\StaticJson;

class SDK
{
    /** @var RouteCollectionInterface Route collection. */
    protected RouteCollectionInterface $collection;

    /**
     * Constructor.
     *
     * @param RouteCollectionInterface $collection
     */
    public function __construct (RouteCollectionInterface $collection)
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

        $packageJson = new StaticJson('/package.json');
        $writings[] = $packageJson;

        $appConfigJson = new StaticJson('/src/config.json');
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
        $collector = new Collector();
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