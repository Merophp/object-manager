<?php
namespace Merophp\ObjectManager\ClassInfo\Cache;

use Exception;
use Merophp\ObjectManager\ObjectManager;

/**
 * Simple Cache for classInfos
 */
class ClassInfoCache
{

    /**
     * @var array
     */
    private array $level1Cache = [];

    //private $level2Cache = null;

    /**
     * constructor
     */
    public function __construct()
    {
        //$this->initializeLevel2Cache();
    }

    /**
     * checks if cacheentry exists for id
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->level1Cache[$id]);// || $this->level2Cache->has($id);
    }

    /**
     * Gets the cache for the id
     *
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        /*if (!isset($this->level1Cache[$id])) {
            $this->level1Cache[$id] = $this->level2Cache->get($id);
        }*/
        return $this->level1Cache[$id];
    }

    /**
     * sets the cache for the id
     *
     * @param string $id
     * @param mixed $value
     */
    public function set(string $id, $value)
    {
        $this->level1Cache[$id] = $value;
        //$this->level2Cache->set($id, $value);
    }

    /**
     * Initialize the second level cache
     * @throws Exception
     */
    private function initializeLevel2Cache()
    {
        $this->level2Cache = ObjectManager::makeInstance(
            RuntimeCache::class
        );
    }
}
