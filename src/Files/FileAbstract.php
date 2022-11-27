<?php

namespace Socodo\SDK\Files;

use Socodo\SDK\Interfaces\FileInterface;

abstract class FileAbstract implements FileInterface
{
    /** @var string File path. */
    protected string $filePath;

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string
    {
        return $this->filePath;
    }

    /**
     * Set file path.
     *
     * @param string $filePath
     */
    public function setFilePath (string $filePath): void
    {
        $this->filePath = $filePath;
    }
}