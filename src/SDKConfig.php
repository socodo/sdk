<?php

namespace Socodo\SDK;

use Socodo\SDK\Interfaces\SDKConfigInterface;

class SDKConfig implements SDKConfigInterface
{
    /** @var string SDK name. */
    protected string $name = '@socodo/sdk';

    /** @var string SDK base URL. */
    protected string $baseUrl = 'http://localhost';

    /** @var array Metadata array. */
    protected array $metadata = [];

    /**
     * Get SDK name.
     *
     * @return string
     */
    public function getName (): string
    {
        return $this->name;
    }

    /**
     * Set SDK name.
     *
     * @param string $name
     * @return void
     */
    public function setName (string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get SDK base URL.
     *
     * @return string
     */
    public function getBaseUrl (): string
    {
        return $this->baseUrl;
    }

    /**
     * Set SDK base URL.
     *
     * @param string $url
     * @return void
     */
    public function setBaseUrl (string $url): void
    {
        $this->baseUrl = $url;
    }

    /**
     * Get metadata item.
     * If no key provided, returns entire metadata array.
     *
     * @param string|null $key
     * @return mixed
     */
    public function getMetadata (string $key = null): mixed
    {
        if ($key === null)
        {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }

    /**
     * Set entire metadata array.
     *
     * @param array $metadata
     * @return void
     */
    public function setMetadata (array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * Add a new metadata item.
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function addMetadata (string $key, mixed $data): void
    {
        $this->metadata[$key] = $data;
    }
}