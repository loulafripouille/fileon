<?php
namespace Fileon;

/**
 * Class Resource
 *
 * @description
 * This class represent the resource(s) that will be watched
 *
 * @package Fileon
 *
 * @author laudeon <louis.audeon@mail.be>
 * @licence MIT
 */
class Resource
{
    /**
     * @var \RecursiveIteratorIterator
     */
    protected $resources;

    /**
     * @var string
     */
    protected $resourcePath;

    /**
     * @var array
     */
    protected $stashedResources = [];

    /**
     * @var array
     */
    protected $ignoringPatterns = [];

    /**
     * @var array
     */
    protected $needingPatterns = [];

    /**
     * Resource constructor.
     *
     * @param string $resourcePath
     * @param bool $recursive
     */
    public function __construct(string $resourcePath, bool $recursive = true)
    {
        //TODO implement recursive

        if ( ! file_exists($resourcePath) && ! is_dir($resourcePath)) {
            throw new \BadMethodCallException('The resource must be an existing file or directory');
        }

        $this->resourcePath = $resourcePath;
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    public function getResources()
    {
        if ( ! $this->resources) {
            $this->createResources();
            $this->stash();
        }

        return $this->resources;
    }

    /**
     * @return array
     */
    public function getStashedResources(): array
    {
        if ( ! $this->resources) {
            $this->createResources();
            $this->stash();
        }

        return $this->stashedResources;
    }

    /**
     * @return array
     */
    public function getIgnoringPatterns(): array
    {
        return $this->ignoringPatterns;
    }

    /**
     * @return array
     */
    public function getNeedingPatterns(): array
    {
        return $this->needingPatterns;
    }

    /**
     * @param array $patterns
     *
     * @return $this
     */
    public function addIgnoringPatterns(array $patterns = [])
    {
        $ignoringPatterns = array_flip($this->ignoringPatterns);

        foreach ($patterns as $pattern) {
            if ( ! isset($ignoringPatterns[$pattern])) {
                $this->ignoringPatterns[] = $pattern;
            }
        }

        return $this;
    }

    /**
     * @param array $patterns
     *
     * @return $this
     */
    public function addNeedingPatterns(array $patterns = [])
    {
        $needingPatterns = array_flip($this->needingPatterns);

        foreach ($patterns as $pattern) {
            if ( ! isset($needingPatterns[$pattern])) {
                $this->needingPatterns[] = $pattern;
            }
        }

        return $this;
    }

    /**
     * Check if a resource path have to be ignored
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function haveToBeIgnored(string $filePath): bool
    {
        foreach ($this->ignoringPatterns as $pattern) {
            if (false !== strpos($filePath, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a resource path have to be ignored (not in the needing patterns)
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function outOfNeeding(string $filePath): bool
    {
        if (empty($this->needingPatterns)) {
            return false;
        }

        foreach ($this->needingPatterns as $pattern) {
            if (false !== strpos($filePath, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $path
     * @param int $time
     */
    public function updateStashedResource(string $path, int $time)
    {
        $this->stashedResources[$path] = $time;
    }

    /**
     * stash resources
     *
     * @codeCoverageIgnore
     */
    protected function stash()
    {
        /**
         * @var string $path
         * @var \SplFileInfo $SplFileInfo
         */
        foreach ($this->getResources() as $path => $object) {
            if ($this->haveToBeIgnored($path) || $this->outOfNeeding($path)) {
                continue;
            }

            $this->stashedResources[$path] = filemtime($object->getPathname());
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createResources()
    {
        if(is_dir($this->resourcePath)) {
            $this->resources = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->resourcePath, \FilesystemIterator::SKIP_DOTS)
            );
        }
        elseif (is_file($this->resourcePath)) {
            $this->resources = [
                new \SplFileInfo($this->resourcePath)
            ];
        }
    }
}
