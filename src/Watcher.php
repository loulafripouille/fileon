<?php
namespace Fileon;

use Evenement\EventEmitter;

/**
 * Class Watcher
 *
 * @description
 * Handles the watching action
 *
 * @package Fileon
 *
 * @author laudeon <louis.audeon@mail.be>
 * @licence MIT
 */
class Watcher
{
    const EVENT_NEW = 0;
    const EVENT_MODIFIED = 1;

    /**
     * @var \Evenement\EventEmitter
     */
    protected $ee;

    /**
     * @var \Fileon\Resource
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $isStopped = false;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * Watcher constructor.
     *
     * @param \Fileon\Resource $resource
     * @param int               $speed
     */
    public function __construct(\Fileon\Resource $resource, int $speed = 1000000)
    {
        $this->ee = new EventEmitter();
        $this->resource = $resource;
        $this->speed = $speed;
    }

    /**
     * @param callable $callback
     *
     * @return void
     */
    public function watch(callable $callback = null)
    {
        $this->registerEvents();
        $resources = iterator_to_array($this->resource->getResources());

        while ($this->isStopped === false) {
            clearstatcache();
            foreach ($resources as $path => $object) {
                //Ignored pattern
                if ($this->resource->haveToBeIgnored($path) || $this->resource->outOfNeeding($path)) {
                    unset($resources[$path]);
                    continue;
                }

                $time = filemtime($object->getPathname());

                //New file
                if (!isset($this->resource->getStashedResources()[$path])) {
                    $this->resource->updateStashedResource($path, $time);
                    $this->ee->emit(self::EVENT_NEW, [$object]);
                    continue;
                }

                //TODO Deleted file

                //Modified
                if ($time > $this->resource->getStashedResources()[$path]) {
                    $this->resource->updateStashedResource($path, $time);
                    $this->ee->emit(self::EVENT_MODIFIED, [$object]);
                    continue;
                }
            }
            usleep($this->speed);
            if (is_callable($callback)) {
                $callback();
            }
        }
    }

    /**
     * @return void
     */
    public function stop()
    {
        $this->isStopped = true;
    }

    /**
     * @return bool
     */
    public function isStopped(): bool
    {
        return $this->isStopped;
    }

    /**
     * @param callable $callback
     */
    public function onModified(callable $callback)
    {
        $this->actions[self::EVENT_MODIFIED] = $callback;
    }

    /**
     * @param callable $callback
     */
    public function onNew(callable $callback)
    {
        $this->actions[self::EVENT_NEW] = $callback;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @return EventEmitter
     */
    public function getEventEmitter(): EventEmitter
    {
        return $this->ee;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function registerEvents()
    {
        $this->ee->on(
            self::EVENT_MODIFIED,
            function (\SplFileInfo $file) {
                if (isset($this->actions[self::EVENT_MODIFIED])) {
                    $this->actions[self::EVENT_MODIFIED]($file);
                }
            }
        );

        $this->ee->on(
            self::EVENT_NEW,
            function (\SplFileInfo $file) {
                if (isset($this->actions[self::EVENT_NEW])) {
                    $this->actions[self::EVENT_NEW]($file);
                }
            }
        );
    }
}
