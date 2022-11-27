<?php

namespace Socodo\SDK\Files;

class InterfaceTs extends TypescriptAbstract
{
    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string
    {
        return '/src/Structures/' . $this->name . '.ts';
    }

    /**
     * Compile to typescript interface.
     *
     * @return string
     */
    public function compile (): string
    {
        $imports = [];

        $propertyLines = [];
        foreach ($this->data as $name => $data)
        {
            $propertyLines[] = $this->compileProperty($name, $data);
        }
        $property = implode("\n\n", $propertyLines);
        $property = $this->addIndent($property, 1);

        $lines = [
            ...$imports,
            '/**',
            ' * Interface ' . $this->name . '.',
            ' */',
            'export interface ' . $this->name . ' {',
            $property,
            '}'
        ];

        return implode("\n", $lines);
    }

    protected function compileProperty (string $name, array $data): string
    {
        $comment = $data['comment'];
        $comment['annotations'] = [
            'type' => [ $comment['type'] ],
            ...$comment['annotations']
        ];
        unset($comment['annotations']['var']);
        $comment = $this->compileComment($comment);

        $lines = [
            $comment,
            $name . ': ' . $data['type'] . ($data['default'] !== null ? ' = ' . json_encode($data['default'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : '') . ';'
        ];

        return implode("\n", $lines);
    }
}