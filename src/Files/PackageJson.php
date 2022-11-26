<?php

namespace Socodo\SDK\Files;

use Socodo\SDK\Exceptions\FileResolutionException;

class PackageJson extends FileAbstract
{
    /** @var string Package name. */
    protected string $name;

    /** @var string Package version. */
    protected string $version;

    /** @var string Package description. */
    protected string $description = '';

    /** @var string Package type. */
    protected string $type = '';

    /** @var string Package main. */
    protected string $main = '';

    /** @var array Package exports. */
    protected array $exports = [];

    /** @var array<string, string> NPM scripts. */
    protected array $scripts = [];

    /** @var array<string, string> Production dependencies. */
    protected array $dependencies = [];

    /** @var array<string, string> Development dependencies. */
    protected array $devDependencies = [];

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $version
     */
    public function __construct (string $name, string $version)
    {
        $this->setName($name);
        $this->setVersion($version);
    }

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string
    {
        return '/package.json';
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
        throw new FileResolutionException(static::class . '::setFilePath() Cannot set file path of PackageJson.');
    }

    /**
     * Get package name.
     *
     * @return string
     */
    public function getName (): string
    {
        return $this->name;
    }

    /**
     * Set package name.
     *
     * @param string $name
     * @return void
     */
    public function setName (string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get package version.
     *
     * @return string
     */
    public function getVersion (): string
    {
        return $this->version;
    }

    /**
     * Set package version.
     *
     * @param string $version
     * @return void
     */
    public function setVersion (string $version): void
    {
        $this->version = $version;
    }

    /**
     * Get package description.
     *
     * @return string
     */
    public function getDescription (): string
    {
        return $this->description;
    }

    /**
     * Set package description.
     *
     * @param string $description
     * @return void
     */
    public function setDescription (string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get package type.
     *
     * @return string
     */
    public function getType (): string
    {
        return $this->type;
    }

    /**
     * Set package type.
     *
     * @param string $type
     * @return void
     */
    public function setType (string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get main.
     *
     * @return string
     */
    public function getMain (): string
    {
        return $this->main;
    }

    /**
     * Set main.
     *
     * @param string $main
     * @return void
     */
    public function setMain (string $main): void
    {
        $this->main = $main;
    }

    /**
     * Get exports.
     *
     * @return array
     */
    public function getExports (): array
    {
        return $this->exports;
    }

    /**
     * Set exports.
     *
     * @param string|array $exports
     * @return void
     */
    public function setExports (string|array $exports): void
    {
        if (is_string($exports))
        {
            $exports = [ $exports ];
        }

        $this->exports = $exports;
    }

    /**
     * Get dependencies.
     *
     * @return array<string, string>
     */
    public function getDependencies (): array
    {
        return $this->dependencies;
    }

    /**
     * Set dependencies.
     *
     * @param array<string, string> $dependencies
     * @return void
     */
    public function setDependencies (array $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Get development dependencies.
     *
     * @return array<string, string>
     */
    public function getDevDependencies (): array
    {
        return $this->devDependencies;
    }

    /**
     * Set development dependencies.
     *
     * @param array<string, string> $dependencies
     * @return void
     */
    public function setDevDependencies (array $dependencies): void
    {
        $this->devDependencies = $dependencies;
    }

    /**
     * Get NPM scripts.
     *
     * @return string[]
     */
    public function getScripts (): array
    {
        return $this->scripts;
    }

    /**
     * Set NPM scripts.
     *
     * @param array $scripts
     * @return void
     */
    public function setScripts (array $scripts): void
    {
        $this->scripts = $scripts;
    }

    /**
     * Compile package.json
     *
     * @return string
     */
    public function compile (): string
    {
        $data = [
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'description' => $this->getDescription(),

            'scripts' => $this->getScripts(),

            'dependencies' => $this->getDependencies(),
            'devDependencies' => $this->getDevDependencies(),
        ];

        if ($this->getType() !== '')
        {
            $data['type'] = $this->getType();
        }

        if ($this->getMain() !== '')
        {
            $data['main'] = $this->getMain();
        }

        $exports = $this->getExports();
        if (!empty($exports))
        {
            if (isset($exports[0]) && count($exports) == 1)
            {
                $data['exports'] = $exports[0];
            }
            else
            {
                $data['exports'] = $exports;
            }
        }

        return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}