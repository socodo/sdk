<?php

namespace Socodo\SDK\Interfaces;

interface FileInterface
{
    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string;

    /**
     * Set file path.
     *
     * @param string $filePath
     * @return void
     */
    public function setFilePath (string $filePath): void;

    /**
     * Compile to string.
     *
     * @return string
     */
    public function compile (): string;
}