<?php
namespace Merophp\ObjectManager;

use Exception;
use ReflectionClass;
use ReflectionException;
use DateTime;
use Merophp\Singleton\Singleton;
use Merophp\Singleton\SingletonInterface;

class ObjectManager extends Singleton implements ObjectManagerInterface{

	/**
	 * @var ?ObjectContainer
	 */
	protected static ?ObjectContainer $objectContainer = null;

	/**
     * @param ObjectContainer $objectContainer
	 */
	public static function setObjectContainer(ObjectContainer $objectContainer){
		self::$objectContainer = $objectContainer;
	}

    /**
     * Get an instance
     *
     * @api
     * @param string $className
     * @return false|mixed
     */
	public static function get(string $className){
		$arguments = func_get_args();
        array_shift($arguments);
        if ($className === DateTime::class) {
            array_unshift($arguments, $className);
        }
		return self::$objectContainer->getInstance($className,$arguments);
	}

	/**
     * Returns TRUE if an object with the given name is registered
     *
     * @param string $objectName Name of the object
     * @return bool TRUE if the object has been registered, otherwise FALSE
     */
    public static function isRegistered(string $objectName): bool
    {
        return class_exists($objectName, true);
    }

    /**
     * @param $className
     * @return bool
     */
	public static function hasSingletonInstance($className): bool
    {
		return self::$objectContainer->hasSingletonInstance($className);
	}


    /**
     * Get an instance of a class
     *
     * @param string $className
     * @return mixed|SingletonInterface
     * @throws ReflectionException
     * @throws Exception
     */
	public static function makeInstance(string $className)
    {
		if (!is_string($className) || empty($className)) {
            throw new Exception('$className must be a non empty string.');
        }

		if($className[0] !== '\\'){
			$className = '\\'.$className;
		}

        if(class_exists($className) && in_array(SingletonInterface::class, class_implements($className, true))){
            $instance = $className::getInstance();
        }
        else{
            // Create new instance and call constructor with parameters
            $instance = self::instantiateClass($className, func_get_args());
        }

		return $instance;
	}

    /**
     * Speed optimized alternative to ReflectionClass::newInstanceArgs()
     *
     * @param string $className Name of the class to instantiate
     * @param array $arguments Arguments passed to self::makeInstance() thus the first one with index 0 holds the requested class name
     * @return mixed
     * @throws ReflectionException
     */
    protected static function instantiateClass(string $className, array $arguments=[])
    {

        switch (count($arguments)) {
            case 1:
                $instance = new $className();
                break;
            case 2:
                $instance = new $className($arguments[1]);
                break;
            case 3:
                $instance = new $className($arguments[1], $arguments[2]);
                break;
            case 4:
                $instance = new $className($arguments[1], $arguments[2], $arguments[3]);
                break;
            case 5:
                $instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4]);
                break;
            case 6:
                $instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
                break;
            case 7:
                $instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6]);
                break;
            case 8:
                $instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6], $arguments[7]);
                break;
            case 9:
                $instance = new $className($arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5], $arguments[6], $arguments[7], $arguments[8]);
                break;
            default:
                // The default case for classes with constructors that have more than 8 arguments.
                // This will fail when one of the arguments shall be passed by reference.
                $class = new ReflectionClass($className);
                array_shift($arguments);
                $instance = $class->newInstanceArgs($arguments);
        }
        return $instance;
    }
}
