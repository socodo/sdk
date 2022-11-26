<?php

namespace Socodo\SDK\Files;

use Socodo\SDK\Exceptions\FileResolutionException;

class TsConfigJson extends FileAbstract
{
    /** @var array Data. */
    protected array $data = [];

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string
    {
        return '/tsconfig.json';
    }

    /**
     * Set file path.
     * File path of PackageJson is fixed to '/package.json'.
     *
     * @param string $filePath
     * @return void
     */
    public function setFilePath (string $filePath): void
    {
        throw new FileResolutionException(static::class . '::setFilePath() Cannot set file path of TsConfigJson.');
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData (): array
    {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param array $data
     */
    public function setData (array $data): void
    {
        $this->data = $data;
    }

    /**
     * Compile tsconfig.json.
     *
     * @return string
     */
    public function compile (): string
    {
        return json_encode($this->getData(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}