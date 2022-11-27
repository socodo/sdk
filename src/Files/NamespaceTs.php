<?php

namespace Socodo\SDK\Files;

class NamespaceTs extends TypescriptAbstract
{
    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string
    {
        return '/src/Namespaces/' . $this->name . '.ts';
    }

    /**
     * Compile to typescript namespace.
     *
     * @return string
     */
    public function compile (): string
    {
        $usedStructures = [];
        $imports = [
            '/**',
            ' * Import API client',
            ' */',
            'import { Client } from \'../client.js\';',
            ''
        ];

        $methodLines = [];
        foreach ($this->data as $name => $data)
        {
            foreach ($data['params'] as $param)
            {
                if (str_starts_with($param['type'], 'I'))
                {
                    $usedStructures[] = $param['type'];
                }
            }
            if (str_starts_with($data['return'], 'I'))
            {
                $usedStructures[] = $data['return'];
            }

            $methodLines[] = $this->compileMethod($name, $data);
        }
        $method = implode("\n\n", $methodLines);
        $method = $this->addIndent($method, 1);

        if (!empty($usedStructures))
        {
            $imports = [
                ...$imports,
                '/**',
                ' * Import used structures.',
                ' */'
            ];
            $usedStructures = array_unique($usedStructures);
            foreach ($usedStructures as $structure)
            {
                $imports[] = 'import { ' . $structure . ' } from \'../Structures/' . $structure . '.js\';';
            }
        }

        $lines = [
            ...$imports,
            '',
            '/**',
            ' * Namespace ' . $this->name . '.',
            ' */',
            'export namespace ' . $this->name . ' {',
            '',
            $method,
            '',
            '}'
        ];

        return implode("\n", $lines);
    }

    /**
     * Compile method string.
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    protected function compileMethod (string $name, array $data): string
    {
        $comment = $this->compileComment($data['comment']);

        $body = null;
        $params = [];
        foreach ($data['params'] as $paramName => $paramData)
        {
            $params[] = $paramName . ': ' . $paramData['type'] . ($paramData['default'] !== null ? (' = ' . json_encode($paramData['default'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)) : '');
            if (str_starts_with($paramData['type'], 'I'))
            {
                $body = $paramName;
            }
        }

        $codeLines = [
            'const path = `' . $data['path'] . '`;',
            'const result = await Client.request(\'' . strtolower($data['method']) . '\', path' . ( $body !== null ? (', ' . $body) : '' ) . ');',
            'return JSON.parse(result) as ' . $data['return'] . ';'
        ];
        $code = implode("\n", $codeLines);
        $code = $this->addIndent($code, 1);

        $lines = [
            $comment,
            'export const ' . $name . ' = async (' . implode(', ', $params) . '): Promise<' . $data['return'] . '> => {',
                $code,
            '};'
        ];

        return implode("\n", $lines);
    }
}