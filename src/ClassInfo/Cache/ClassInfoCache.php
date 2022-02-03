<?php
namespace Merophp\ObjectManager\ClassInfo\Cache;

/**
 * Simple Cache for classInfos
 */
class ClassInfoCache
{

    /**
     * @var array
     */
    private array $level1Cache = [];

    /**
     * checks if cacheentry exists for id
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->level1Cache[$id]);
    }

    /**
     * Gets the cache for the id
     *
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
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
    }
}
