<?php

namespace Socodo\SDK\Interfaces;

interface SDKConfigInterface
{
    /**
     * Get SDK name.
     *
     * @return string
     */
    public function getName (): string;

    /**
     * Set SDK name.
     *
     * @param string $name
     * @return void
     */
    public function setName (string $name): void;

    /**
     * Get SDK base URL.
     *
     * @return string
     */
    public function getBaseUrl (): string;

    /**
     * Set SDK base URL.
     *
     * @param string $url
     * @return void
     */
    public function setBaseUrl (string $url): void;

    /**
     * Get metadata item.
     * If no key provided, returns entire metadata array.
     *
     * @param string|null $key
     * @return mixed
     */
    public function getMetadata (string $key = null): mixed;

    /**
     * Set entire metadata array.
     *
     * @param array $metadata
     * @return void
     */
    public function setMetadata (array $metadata): void;

    /**
     * Add a new metadata item.
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function addMetadata (string $key, mixed $data): void;
}