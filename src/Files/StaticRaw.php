<?php

namespace Socodo\SDK\Files;

class StaticRaw extends FileAbstract
{
    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct (string $path)
    {
        $this->setFilePath($path);
    }

    /**
     * Compile to string.
     *
     * @return string
     */
    public function compile (): string
    {
        return file_get_contents(__DIR__ . '/Static/' . $this->getFilePath());
    }
}