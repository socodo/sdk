<?php

namespace Socodo\SDK\Files;

class TsInterface extends FileAbstract
{
    protected const INTERFACE_TEMPLATE =
        "export interface %NAME% {" .
        "%PROPERTIES%" . "\n" .
        "}";

    protected const PROPERTY_TEMPLATE =
        "\n" .
        "    %COMMENT%" . "\n" .
        "    %NAME%: %TYPE%%DEFAULT%;";

    /** @var string Interface name. */
    protected string $name;

    /** @var array<array{key: string, type: string, comment: string, default: mixed}> Properties. */
    protected array $properties = [];

    /**
     * Constructor.
     *
     * @param string $name
     * @param array $properties
     */
    public function __construct (string $name, array $properties = [])
    {
        $this->setName($name);
        $this->setProperties($properties);
    }

    /**
     * Get interface name.
     *
     * @return string
     */
    public function getName (): string
    {
        return $this->name;
    }

    /**
     * Set interface name.
     *
     * @param string $name
     * @return void
     */
    public function setName (string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get properties.
     *
     * @return string[]
     */
    public function getProperties (): array
    {
        return $this->properties;
    }

    /**
     * Set properties.
     *
     * @param array $properties
     * @return void
     */
    public function setProperties (array $properties = []): void
    {
        $this->properties = $properties;
    }

    /**
     * Compile to typescript text.
     *
     * @return string
     */
    public function compile (): string
    {
        $properties = [];
        foreach ($this->getProperties() as $property)
        {
            if ($property['default'] !== null)
            {
                $property['default'] = ' = ' . (is_string($property['default']) ? '"' . $property['default'] . '"' : $property['default']);
            }
            else
            {
                $property['default'] = '';
            }

            $properties[] = $this->compileTemplate(self::PROPERTY_TEMPLATE, $property);
        }

        return $this->compileTemplate(self::INTERFACE_TEMPLATE, [ 'name' => $this->getName(), 'properties' => implode("\n", $properties) ]);
    }
}