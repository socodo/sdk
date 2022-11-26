<?php

namespace Socodo\SDK\Files;

use Socodo\SDK\Exceptions\TypescriptResolutionException;

class GitIgnore extends FileAbstract
{
    /** @var array Ignores. */
    protected array $ignores = [];

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath (): string
    {
        return '/.gitignore';
    }

    /**
     * Set file path.
     * File path of GitIgnore is fixed to '/.gitignore'.
     *
     * @param string $filePath
     * @return void
     */
    public function setFilePath (string $filePath): void
    {
        throw new TypescriptResolutionException(static::class . '::setFilePath() Cannot set file path of GitIgnore.');
    }

    /**
     * Get ignores.
     *
     * @return array
     */
    public function getIgnores (): array
    {
        return $this->ignores;
    }

    /**
     * Set ignores.
     *
     * @param array $ignores
     * @return void
     */
    public function setIgnores (array $ignores): void
    {
        $this->ignores = $ignores;
    }

    /**
     * Add ignore path.
     *
     * @param string $path
     * @return void
     */
    public function addPath (string $path): void
    {
        $this->ignores[] = $path;
    }

    /**
     * Add ignore comment.
     *
     * @param string $comment
     * @return void
     */
    public function addComment (string $comment): void
    {
        $this->ignores[] = '# ' . $comment;
    }

    /**
     * Add ignore block.
     *
     * @param string $comment
     * @param array $path
     * @return void
     */
    public function addBlock (string $comment, array $path): void
    {
        $this->addComment($comment);
        foreach ($path as $item)
        {
            $this->addPath($item);
        }

        $this->addPath('');
    }

    /**
     * Compile to string.
     *
     * @return string
     */
    public function compile (): string
    {
        return implode("\n", $this->getIgnores());
    }
}