<?php

namespace Socodo\SDK\Files;

class TsNamespace extends FileAbstract
{
    protected const NAMESPACE_TEMPLATE =
        "import { http } from '../http';" . "\n" .
        "%IMPORTS%" . "\n" .
        "export namespace %NAME% {" .
        "%METHODS%" . "\n" .
        "}";

    protected const METHOD_TEMPLATE =
        "\n" .
        "    /**" . "\n" .
        "%COMMENT%" . "\n" .
        "     */" . "\n" .
        "    export const %NAME% = async (%PARAMS%): Promise<%RETURN%> => {" .
        "%CODE%" . "\n" .
        "    }";

    protected const PARAM_TEMPLATE =
        "%NAME%: %TYPE%%DEFAULT%";

    protected const CODE_TEMPLATE =
        "\n" .
        "        const path = `%PATH%`" . "\n" .
        "        const result = await http.request('%METHOD%', path);" . "\n" .
        "        return %RETURN%;";

    protected const RETURN_TEMPLATES =
        [
            'structure' => "JSON.parse(result) as %RETURN%",
            'string' => "result",
            'any' => "JSON.parse(result)"
        ];

    /** @var string Class name. */
    protected string $name;

    /** @var array<array> Methods. */
    protected array $methods = [];

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct (string $name)
    {
        $this->setName($name);
    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getName (): string
    {
        return $this->name;
    }

    /**
     * Set class name.
     *
     * @param string $name
     */
    public function setName (string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get methods.
     *
     * @return array
     */
    public function getMethods (): array
    {
        return $this->methods;
    }

    /**
     * Add a new method.
     *
     * @param string $name
     * @param array<string, string{regex: string, type: string, comment: ?string}> $params
     * @param array<array{path: string, method: string, return: string}> $code
     * @param string $comment
     * @return void
     */
    public function addMethod (string $name, array $params, array $code, string $comment): void
    {
        $method = [
            'name' => $name,
            'params' => $params,
            'code' => $code,
            'comment' => $comment
        ];
        $this->methods[] = $method;
    }

    /**
     * Compile to typescript text.
     *
     * @return string
     */
    public function compile (): string
    {
        $imports = [];
        $methods = [];
        foreach ($this->getMethods() as $method)
        {
            $paramComments = [];

            $params = [];
            foreach ($method['params'] as $name => $param)
            {
                $params[$name] = $this->compileTemplate(self::PARAM_TEMPLATE, [
                    'name' => $name,
                    'type' => $param['type'],
                    'default' => ''
                ]);
                $paramComments[$name] =
                    ($param['comment'] !== null ? $param['comment'] . "\n     * " : '') .
                    'Should be matched with /' . $param['regex'] . '/.';
            }
            $params = implode(', ', array_values($params));

            $returnType = $method['code']['return'];
            if (str_starts_with($method['code']['return'], 'I'))
            {
                $returnType = 'structure';
                $imports[] = 'import { ' . $method['code']['return'] . ' } from \'../Structures/' . $method['code']['return'] . '\';';
            }

            $code = $this->compileTemplate(self::CODE_TEMPLATE, [
                'path' => $method['code']['path'],
                'method' => $method['code']['method'],
                'return' => $this->compileTemplate(self::RETURN_TEMPLATES[$returnType], [
                    'return' => $method['code']['return']
                ])
            ]);

            $comments = [];
            $comments[] = $method['comment'] !== '' ? $method['comment'] : '';
            $comments[] = '[' . $method['code']['method'] . '] ' . $method['code']['path'];
            $comments[] = '';

            foreach ($paramComments as $name => $comment)
            {
                $comments[] = '@param ' . $name . ' {' . $method['params'][$name]['type'] . '} ' . $comment;
            }

            $methods[] = $this->compileTemplate(self::METHOD_TEMPLATE, [
                'comment' => '     * ' . implode("\n     * ", $comments),
                'name' => $method['name'],
                'params' => $params,
                'return' => $method['code']['return'],
                'code' => $code
            ]);
        }

        if (!empty($imports))
        {
            $imports[] = '';
        }

        return $this->compileTemplate(self::NAMESPACE_TEMPLATE, [
            'imports' => implode("\n", $imports),
            'name' => $this->getName(),
            'methods' => implode('', $methods)
        ]);
    }
}