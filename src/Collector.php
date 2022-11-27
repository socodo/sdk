<?php

namespace Socodo\SDK;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Socodo\Router\RouteCollection;
use Socodo\SDK\Attributes\ExcludeSDK;

class Collector
{
    /** @var string Controller prefix. */
    protected string $controllerPrefix = 'App\\Controllers\\';

    /** @var string Structure prefix. */
    protected string $structurePrefix = 'App\\Structures\\';

    /** @var string Structure super class name. */
    protected string $structureSuper = 'Socodo\\Framework\\Spec\\Structure';

    /** @var array Namespaces. */
    protected array $namespaces = [];

    /** @var array Interfaces. */
    protected array $interfaces = [];

    /**
     * Constructor.
     *
     * @param string|null $controllerPrefix
     * @param string|null $structurePrefix
     * @param string|null $structureSuper
     */
    public function __construct (?string $controllerPrefix = null, ?string $structurePrefix = null, ?string $structureSuper = null)
    {
        if ($controllerPrefix !== null)
        {
            $this->controllerPrefix = $controllerPrefix;
        }

        if ($structurePrefix !== null)
        {
            $this->structurePrefix = $structurePrefix;
        }

        if ($structureSuper !== null)
        {
            $this->structureSuper = $structureSuper;
        }
    }

    /**
     * Collect from RouteCollection.
     *
     * @param RouteCollection $collection
     * @return array{
     *     namespaces: array<string, array{
     *         method: string,
     *         path: string,
     *         params: array<string, array{
     *             type: string,
     *             default: mixed
     *         }>,
     *         return: string,
     *         comment: array{
     *             description: array<string>,
     *             return: array{
     *                 type: string,
     *                 description: array<string>,
     *             },
     *             parameters: array<string, array{
     *                 type: string,
     *                 description: array<string>
     *             }>,
     *             annotations: array<string, array<string>>
     *         }
     *     }>,
     *     interfaces: array<string, array<array{
     *         type: string,
     *         default: mixed,
     *         comment: array{
     *             type: string,
     *             description: array<string>,
     *             annotations: array<string, array<string>>
     *         }
     *     }>>
     * }
     */
    public function collect (RouteCollection $collection): array
    {
        $this->namespaces = [];
        $this->interfaces = [];
        $this->collectFromRouteCollection($collection);

        return [
            'namespaces' => $this->namespaces,
            'interfaces' => $this->interfaces
        ];
    }

    /**
     * Collect namespaces from RouteCollection.
     *
     * @param RouteCollection $collection
     * @return void
     */
    protected function collectFromRouteCollection (RouteCollection $collection): void
    {
        $routes = $collection->getRoutes();
        foreach ($routes as $route)
        {
            /** @var array{0: class-string, 1: string} $controller */
            $controller = $route->getController();
            if (!is_array($controller) )
            {
                $controller = [ $controller, '__invoke' ];
            }
            if (!method_exists($controller[0], $controller[1]))
            {
                continue;
            }

            try
            {
                $class = new ReflectionClass($controller[0]);
                if ($this->isExcluded($class))
                {
                    continue;
                }

                $method = $class->getMethod($controller[1]);
                if ($this->isExcluded($method))
                {
                    continue;
                }
            }
            catch (ReflectionException)
            {
                continue;
            }

            $namespaceName = $class->getName();
            if (str_starts_with($namespaceName, $this->controllerPrefix))
            {
                $namespaceName = substr($namespaceName, strlen($this->controllerPrefix));
            }

            $namespaceName = str_replace('\\', '', $namespaceName);
            $methodName = $method->getName();

            $compiled = $route->compile();
            $path = trim(array_reduce($compiled['segments'], static function (string $path, array $segment) {
                return $path . '/' . ($segment['type'] == 'literal' ? $segment['name'] : '{' . $segment['name'] . '}');
            }, ''), '/');

            $params = $this->getParams($method);
            $return = $this->getReturnType($method);

            $comment = $this->getMethodComment($method);
            $comment['return']['type'] = $this->getTypescriptType($comment['return']['type']);
            foreach ($comment['parameters'] as $name => &$parameter)
            {
                if (isset($params[$name]) && $params[$name]['type'] === 'I' . $parameter['type'])
                {
                    $parameter['type'] = 'I' . $parameter['type'];
                }
                else
                {
                    $parameter['type'] = $this->getTypescriptType($parameter['type']);
                }

                if (isset($compiled['params'][$name]))
                {
                    $parameter['description'][] = 'Should be matched with /' . $compiled['params'][$name] . '/.';
                }
            }

            $methodData = [
                'path' => $path,
                'params' => $params,
                'return' => $return,
                'comment' => $comment
            ];

            $methods = $route->getMethods();
            if (count($methods) > 1)
            {
                foreach ($methods as $httpMethod)
                {
                    $httpMethod = strtoupper($httpMethod->value);
                    $methodData['method'] = $httpMethod;
                    $this->addNamespaceMethod($namespaceName, strtolower($httpMethod) . ucfirst($methodName), $methodData);
                }
                continue;
            }

            $methodData['method'] = strtoupper($methods[0]->value);
            $this->addNamespaceMethod($namespaceName, $methodName, $methodData);
        }
    }

    /**
     * Add a method to namespace.
     *
     * @param string $namespace
     * @param string $name
     * @param array $methodData
     * @return void
     */
    protected function addNamespaceMethod (string $namespace, string $name, array $methodData): void
    {
        if (!isset($this->namespaces[$namespace]))
        {
            $this->namespaces[$namespace] = [];
        }

        $this->namespaces[$namespace][$name] = $methodData;
    }

    /**
     * Get parameters from ReflectionMethod.
     *
     * @param ReflectionMethod $method
     * @return array<string, array{
     *     type: string,
     *     default: mixed
     * }>
     */
    protected function getParams (ReflectionMethod $method): array
    {
        $params = [];
        foreach ($method->getParameters() as $param)
        {
            $name = $param->getName();
            $type = $this->getTypescriptType($param->getType());
            $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;

            $params[$name] = [
                'type' => $type,
                'default' => $default
            ];
        }

        return $params;
    }

    /**
     * Get typescript return type from ReflectionMethod.
     *
     * @param ReflectionMethod $method
     * @return string
     */
    protected function getReturnType (ReflectionMethod $method): string
    {
        $return = $method->getReturnType();
        return $this->getTypescriptType($return);
    }

    /**
     * Get comment from ReflectionMethod.
     *
     * @param ReflectionMethod $method
     * @return array{
     *     description: array<string>,
     *     return: array{
     *         type: ?string,
     *         description: array<string>
     *     },
     *     parameters: array<string, array{
     *         type: string,
     *         description: array<string>
     *     }>,
     *     annotations: array<string, array<string>>
     * }
     * @noinspection DuplicatedCode
     */
    protected function getMethodComment (ReflectionMethod $method): array
    {
        $docComment = $method->getDocComment();
        if ($docComment === false)
        {
            return [
                'description' => [],
                'return' => [ 'type' => null, 'description' => [] ],
                'parameters' => [],
                'annotations' => []
            ];
        }

        $docComment = trim(substr($docComment, 3, -2));
        $docLine = explode("\n", str_replace("\r", '', $docComment));
        $docLine = array_map(static fn (string $line) => trim($line), $docLine);

        $description = [];
        $return = [ 'type' => null, 'description' => [] ];
        $parameters = [];
        $annotations = [];

        $isAnnotationStarted = false;
        $latestAnnotation = null;
        $latestParameter = null;

        $handleAnnotation = static function (string $type, string $content) use (&$return, &$parameters, &$annotations, &$latestParameter) {
            $content = trim($content);

            if ($type === 'return')
            {
                if ($return['type'] === null)
                {
                    if (preg_match('/^(\S+)(?:\s+(.*))?$/', $content, $matches) === 0)
                    {
                        $return['type'] = '';
                        $return['description'][] = $content;
                        return;
                    }

                    $return['type'] = $matches[1];
                    if (isset($matches[2]))
                    {
                        $return['description'][] = $matches[2];
                    }
                    return;
                }

                $return['description'][] = $content;
                return;
            }

            if ($type === 'param')
            {
                if (preg_match('/^(\S+)\s+\$([_a-zA-Z]+[_a-zA-Z0-9]*)(?:\s+(.*))?$/', $content, $matches) === 0)
                {
                    if ($latestParameter === null)
                    {
                        return;
                    }

                    $matches[2] = $latestParameter;
                    $matches[3] = $content;
                }

                $latestParameter = $matches[2];
                if (!isset($parameters[$latestParameter]))
                {
                    $parameters[$latestParameter] = [ 'type' => $matches[1], 'description' => [] ];
                }

                if (isset($matches[3]))
                {
                    $parameters[$latestParameter]['description'][] = $matches[3];
                }
                return;
            }

            if (!isset($annotations[$type]))
            {
                $annotations[$type] = [];
            }
            $annotations[$type][] = $content;
        };

        foreach ($docLine as $line)
        {
            $line = ltrim($line, "* \t");
            if (str_starts_with($line, '@'))
            {
                $isAnnotationStarted = true;
            }

            if (!$isAnnotationStarted)
            {
                $description[] = $line;
                continue;
            }

            if (preg_match('/^@(\S+)\s+(.*)$/', $line, $matches) === 0)
            {
                if ($latestAnnotation === null)
                {
                    $isAnnotationStarted = false;
                    $description[] = $line;
                    continue;
                }

                $handleAnnotation($latestAnnotation, $line);
                continue;
            }

            $latestAnnotation = $matches[1];
            $handleAnnotation($latestAnnotation, $matches[2]);
        }

        return [
            'description' => $description,
            'return' => $return,
            'parameters' => $parameters,
            'annotations' => $annotations
        ];
    }

    /**
     * Determine if the Reflector is excluded to SDK.
     *
     * @param ReflectionMethod|ReflectionClass|ReflectionProperty $reflector
     * @return bool
     */
    protected function isExcluded (ReflectionMethod|ReflectionClass|ReflectionProperty $reflector): bool
    {
        $excludeAttr = $reflector->getAttributes(ExcludeSDK::class, ReflectionAttribute::IS_INSTANCEOF);
        return !empty($excludeAttr);
    }

    /**
     * Get typescript type from ReflectionType.
     *
     * @param string|ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null $type
     * @return string
     */
    protected function getTypescriptType (string|ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null $type): string
    {
        if ($type === null)
        {
            return 'any';
        }

        if (is_string($type))
        {
            $typeName = $type;
            $allowsNull = false;

            if (str_starts_with($typeName, '?'))
            {
                $typeName = substr($typeName, 1);
                $allowsNull = true;
            }
            elseif (str_starts_with(strtolower($typeName), 'null|'))
            {
                $typeName = substr($typeName, 5);
                $allowsNull = true;
            }
            elseif (str_ends_with(strtolower($typeName), '|null'))
            {
                $typeName = substr($typeName, 0, -5);
                $allowsNull = true;
            }
        }
        else
        {
            $typeName = $type->getName();
            $allowsNull = $type->allowsNull();
        }

        $typeString = 'any';
        if ($typeName === 'string')
        {
            $typeString = 'string';
        }
        elseif ($typeName === 'int' || $typeName === 'float' || $typeName === 'double')
        {
            $typeString = 'number';
        }
        elseif ($typeName === 'bool')
        {
            $typeString = 'boolean';
        }
        elseif (is_subclass_of($typeName, $this->structureSuper))
        {
            $isNotExcluded = $this->collectStructure($typeName);
            $typeString = $isNotExcluded ? $this->getStructureInterfaceName($typeName) : 'any';
        }

        if ($allowsNull)
        {
            $typeString = '?' . $typeString . ($typeString !== 'any' ? '|null' : '');
        }

        return $typeString;
    }

    /**
     * Get typescript interface name from Structure class name.
     *
     * @param string $structureClass
     * @return string
     */
    protected function getStructureInterfaceName (string $structureClass): string
    {
        if (str_starts_with($structureClass, $this->structurePrefix))
        {
            $structureClass = substr($structureClass, strlen($this->structurePrefix));
        }

        return 'I' . str_replace('\\', '', $structureClass);
    }

    /**
     * Collect a structure by Structure class name.
     *
     * @param string $structureClass
     * @return bool
     */
    protected function collectStructure (string $structureClass): bool
    {
        if (!class_exists($structureClass) || !is_subclass_of($structureClass, $this->structureSuper))
        {
            return false;
        }

        $interfaceName = $this->getStructureInterfaceName($structureClass);
        if (isset($this->interfaces[$interfaceName]))
        {
            return true;
        }

        $class = new ReflectionClass($structureClass);
        $structure = [];

        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property)
        {
            if ($this->isExcluded($property))
            {
                continue;
            }

            $name = $property->getName();
            $default = $property->hasDefaultValue() ? $property->getDefaultValue() : null;
            if ($property->getType()?->getName() === $structureClass)
            {
                $type = $this->getStructureInterfaceName($structureClass);
            }
            else
            {
                $type = $this->getTypescriptType($property->getType());
            }

            $comment = $this->getPropertyComment($property);

            $structure[$name] = [
                'type' => $type,
                'default' => $default,
                'comment' => $comment
            ];
        }

        $this->interfaces[$interfaceName] = $structure;
        return true;
    }

    /** @noinspection DuplicatedCode */
    protected function getPropertyComment (ReflectionProperty $property): array
    {
        $docComment = $property->getDocComment();
        if ($docComment === false)
        {
            return [
                'type' => null,
                'description' => [],
                'annotations' => []
            ];
        }

        $docComment = trim(substr($docComment, 3, -2));
        $docLine = explode("\n", str_replace("\r", '', $docComment));
        $docLine = array_map(static fn (string $line) => trim($line), $docLine);

        $type = null;
        $description = [];
        $annotations = [];

        $isAnnotationStarted = false;
        $latestAnnotation = null;

        $handleAnnotation = static function (string $annoType, string $content) use (&$type, &$description, &$annotations, &$latestAnnotation) {
            $content = trim($content);

            if ($annoType === 'var')
            {
                if (preg_match('/^(\S+)(?:\s+(.*))?$/', $content, $matches) === 0)
                {
                    $type = '';
                }
                else
                {
                    $type = $matches[1];
                    if (!isset($matches[2]))
                    {
                        return;
                    }

                    $content = $matches[2];
                }
            }

            if (!isset($annotations[$annoType]))
            {
                $annotations[$annoType] = [];
            }

            $annotations[$annoType][] = $content;
        };

        foreach ($docLine as $line)
        {
            $line = ltrim($line, '* \t');
            if (str_starts_with($line, '@'))
            {
                $isAnnotationStarted = true;
            }

            if (!$isAnnotationStarted)
            {
                $description[] = $line;
                continue;
            }

            if (preg_match('/^@(\S+)\s+(.*)$/', $line, $matches) === 0)
            {
                if ($latestAnnotation === null)
                {
                    $isAnnotationStarted = false;
                    $description[] = $line;
                    continue;
                }

                $handleAnnotation($latestAnnotation, $line);
                continue;
            }

            $latestAnnotation = $matches[1];
            $handleAnnotation($latestAnnotation, $matches[2]);
        }

        if (isset($annotations['var']))
        {
            $description = array_merge($annotations['var'], $description);
        }

        return [
            'type' => $type,
            'description' => $description,
            'annotations' => $annotations
        ];
    }
}