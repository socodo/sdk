<?php

namespace Socodo\SDK\Files;

use Socodo\SDK\Exceptions\FileResolutionException;

class StaticJson extends FileAbstract
{
    /** @var array Overrides data. */
    protected array $overrides = [];

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
     * Set JSON overrides.
     *
     * @param array $overrides
     */
    public function setOverrides (array $overrides): void
    {
        $this->overrides = $overrides;
    }

    /**
     * Compile JSON string.
     *
     * @return string
     */
    public function compile (): string
    {
        $raw = file_get_contents(__DIR__ . '/Static/' . $this->getFilePath());
        $data = json_decode($raw);
        foreach ($this->overrides as $key => $value)
        {
            $data->{$key} = $value;
        }

        return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}