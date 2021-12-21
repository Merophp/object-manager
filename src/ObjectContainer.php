<?php
namespace Merophp\ObjectManager;

use Merophp\ObjectManager\ClassInfo\Cache\ClassInfoCache;
use Merophp\ObjectManager\ClassInfo\ClassInfo;
use Merophp\ObjectManager\ClassInfo\Factory\ClassInfoFactory;
use Merophp\ObjectManager\Exception\CannotBuildObjectException;

class ObjectContainer{

	/**
	 * @var array
	 */
	protected static array $singletonInstances = [];

	/**
     * @var ?ClassInfoCache
     */
    private ?ClassInfoCache $cache = null;

    /**
     * reference to the classinfofactory, that analyses dependencies
     *
     * @var ?ClassInfoFactory
     */
    private ?ClassInfoFactory $classInfoFactory = null;

	 /**
     * Array of prototype objects currently being built, to prevent recursion.
     *
     * @var array
     */
    private array $prototypeObjectsWhichAreCurrentlyInstanciated;

	/**
     * Internal method to create the classInfoFactory, extracted to be mockable.
     *
     * @return ClassInfoFactory
     */
    protected function getClassInfoFactory(): ClassInfoFactory
    {
        if ($this->classInfoFactory == null) {
            $this->classInfoFactory = new ClassInfoFactory;
        }
        return $this->classInfoFactory;
    }

    /**
     * Internal method to create the classInfoCache, extracted to be mockable.
     *
     * @return ClassInfoCache
     */
    protected function getClassInfoCache(): ClassInfoCache
    {
        if ($this->cache == null) {
            $this->cache = new ClassInfoCache;
        }
        return $this->cache;
    }

	/**
	 * Get an instance
     *
     * @param string $className
     * @param array $arguments
	 */
	public function getInstance(string $className, array $arguments=[])
    {

		$this->prototypeObjectsWhichAreCurrentlyInstanciated = [];

		if ($className === get_class($this)) {
            return $this;
        }
        if(self::hasSingletonInstance($className)){
            return self::getSingletonInstance($className);
        }

		$instance = call_user_func_array(
			[ObjectManager::class, 'makeInstance'],
			array_merge([$className], $arguments)
		);

		$classInfo = $this->getClassInfo(get_class($instance));
		$classIsSingleton = $classInfo->getIsSingleton();
        if (!$classIsSingleton) {
            if (array_key_exists($className, $this->prototypeObjectsWhichAreCurrentlyInstanciated) !== false) {
                throw new CannotBuildObjectException(
                	'Cyclic dependency in prototype object, for class "' . $className . '".'
				);
            }
            $this->prototypeObjectsWhichAreCurrentlyInstanciated[$className] = true;
        }

		$this->injectDependencies($instance, $classInfo);
        $this->initializeInstance($instance, $classInfo);

		if($classIsSingleton){
			self::$singletonInstances[$className] = $instance;
		}
		else{
			unset($this->prototypeObjectsWhichAreCurrentlyInstanciated[$className]);
		}

		return $instance;
	}

	 /**
     * Gets Classinfos for the className - using the cache and the factory
     *
     * @param string $className
     * @return ?ClassInfo
     */
    private function getClassInfo(string $className): ?ClassInfo
    {
        $classNameHash = md5($className);

		$classInfo = null;
		if($this->getClassInfoCache()->has($classNameHash)) $classInfo = $this->getClassInfoCache()->get($classNameHash);

		if (!$classInfo instanceof ClassInfo) {
			$classInfo = $this->getClassInfoFactory()->buildClassInfoFromClassName($className);
            $this->getClassInfoCache()->set($classNameHash, $classInfo);
		}

        return $classInfo;
    }



    /**
     * Inject setter-dependencies into $instance
     *
     * @param object $instance
     * @param ClassInfo $classInfo
     * @return void
     */
    protected function injectDependencies($instance, ClassInfo $classInfo)
    {
        if (!$classInfo->hasInjectMethods()) {
            return;
        }

        foreach ($classInfo->getInjectMethods() as $injectMethodName => $classNameToInject) {
            $instanceToInject = $this->getInstance($classNameToInject);


            if (is_callable([$instance, $injectMethodName])) {
                $instance->{$injectMethodName}($instanceToInject);
            }
        }
    }

    /**
     * Call object initializer if present in object
     *
     * @param object $instance
     * @param ClassInfo $classInfo
     */
    protected function initializeInstance($instance, ClassInfo $classInfo)
    {
        if ($classInfo->getIsInitializeable() && is_callable([$instance, 'initializeObject'])) {
            $instance->initializeObject();
        }
    }

	/**
	 * @param string $className
     * @param mixed $instance
	 */
	public static function addSingletonInstance(string $className, $instance)
    {
		self::$singletonInstances[$className] = $instance;
	}

	/**
	 * @return array
	 */
	public static function getSingletonInstances(): array
    {
		return self::$singletonInstances;
	}

	/**
	 * @param string $className
     * @return bool
	 */
	public static function hasSingletonInstance(string $className): bool
    {
		return array_key_exists($className, self::$singletonInstances);
	}

	/**
	 * @param string $className
	 */
	public static function getSingletonInstance(string $className)
    {
		return self::$singletonInstances[$className];
	}

}
