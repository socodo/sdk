<?php

namespace Socodo\SDK;

use Socodo\SDK\Interfaces\FileInterface;

class Writer
{
    /** @var string Project base path. */
    protected string $basePath;

    /**
     * Constructor.
     *
     * @param string $basePath
     */
    public function __construct (string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Write file.
     *
     * @param FileInterface $ts
     * @return void
     */
    public function write (FileInterface $ts): void
    {
        $path = $this->basePath . $ts->getFilePath();
        $segments = explode('/', $path);
        array_pop($segments);

        $dirPath = implode('/', $segments);
        if (!is_dir($dirPath))
        {
            mkdir($dirPath, 0777, true);
        }

        file_put_contents($path, $ts->compile());
    }

    /**
     * Clear base path.
     *
     * @return void
     */
    public function clearBasePath (): void
    {
        $this->removeDirectory($this->basePath);
    }

    /**
     * Remove a directory recursively.
     *
     * @param string $path
     * @return void
     */
    protected function removeDirectory (string $path): void
    {
        if (!file_exists($path))
        {
            return;
        }

        if (!is_dir($path))
        {
            unlink($path);
            return;
        }

        $files = array_diff(scandir($path), [ '.', '..' ]);
        foreach ($files as $file)
        {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath) && !is_link($filePath))
            {
                $this->removeDirectory($filePath);
                continue;
            }

            @unlink($filePath);
        }

        @rmdir($path);
    }
}