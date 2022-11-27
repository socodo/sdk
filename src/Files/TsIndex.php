<?php

namespace Socodo\SDK\Files;

use Socodo\SDK\Exceptions\FileResolutionException;

class TsIndex extends FileAbstract
{
    protected const EXPORT_TEMPLATE =
        "export {" .
        "%ITEMS%" . "\n" .
        "} from '%FROM%';";

    protected const ITEM_TEMPLATE = "\n    %NAME%,";

    /** @var array Exports. */
    protected array $exports = [];

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string
    {
        return '/src/index.ts';
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
        throw new FileResolutionException(static::class . '::setFilePath() Cannot set file path of TsIndex.');
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
     * @param array $exports
     */
    public function setExports (array $exports): void
    {
        $this->exports = $exports;
    }

    /**
     * Compile to typescript text.
     *
     * @return string
     */
    public function compile (): string
    {
        $exports = [];
        foreach ($this->getExports() as $export)
        {
            $items = [];
            foreach ($export['items'] as $item)
            {
                $items[] = $this->compileTemplate(self::ITEM_TEMPLATE, [
                    'name' => $item
                ]);
            }

            $exports[] = $this->compileTemplate(self::EXPORT_TEMPLATE, [
                'items' => implode('', $items),
                'from' => $export['from']
            ]);
        }

        return implode("\n\n", $exports);
    }
}