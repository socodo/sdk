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

    /**
     * Compile template string.
     *
     * @param string $template
     * @param array $bindings
     * @return string
     */
    protected function compileTemplate (string $template, array $bindings = []): string
    {
        foreach ($bindings as $key => $value)
        {
            $template = str_replace('%' . strtoupper($key) . '%', $value, $template);
        }

        return $template;
    }
}