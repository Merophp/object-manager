<?php
namespace Merophp\ObjectManager\ClassInfo;

/**
 * Value object containing the relevant informations for a class,
 * this object is build by the classInfoFactory - or could also be restored from a cache
 */
class ClassInfo
{
    /**
     * The classname of the class where the infos belong to
     *
     * @var string
     */
    private string $className;

    /**
     * The constructor Dependencies for the class in the format:
     * array(
     * 0 => array( <-- parameters for argument 1
     * 'name' => <arg name>, <-- name of argument
     * 'dependency' => <classname>, <-- if the argument is a class, the type of the argument
     * 'defaultvalue' => <mixed>) <-- if the argument is optional, its default value
     * ),
     * 1 => ...
     * )
     *
     * @var array
     */
    private array $constructorArguments;

    /**
     * All setter injections in the format
     * array (nameOfMethod => classNameToInject )
     *
     * @var array
     */
    private array $injectMethods;

    /**
     * Indicates if the class is a singleton or not.
     *
     * @var bool
     */
    private bool $isSingleton = false;

    /**
     * Indicates if the class has the method initializeObject
     *
     * @var bool
     */
    private bool $isInitializeable = false;

    /**
     * @param string $className
     * @param array $constructorArguments
     * @param array $injectMethods
     * @param bool $isSingleton
     * @param bool $isInitializeable
     */
    public function __construct(string $className, array $constructorArguments, array $injectMethods, bool $isSingleton, bool $isInitializeable)
    {
        $this->className = $className;
        $this->constructorArguments = $constructorArguments;
        $this->injectMethods = $injectMethods;
        $this->isSingleton = $isSingleton;
        $this->isInitializeable = $isInitializeable;
    }

    /**
     * Gets the class name passed to constructor
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Get arguments passed to constructor
     *
     * @return array
     */
    public function getConstructorArguments(): array
    {
        return $this->constructorArguments;
    }

    /**
     * Returns an array with the inject methods.
     *
     * @return array
     */
    public function getInjectMethods(): array
    {
        return $this->injectMethods;
    }

    /**
     * Asserts if the class is a singleton or not.
     *
     * @return bool
     */
    public function getIsSingleton(): bool
    {
        return $this->isSingleton;
    }

    /**
     * Asserts if the class is initializeable with initializeObject.
     *
     * @return bool
     */
    public function getIsInitializeable(): bool
    {
        return $this->isInitializeable;
    }

    /**
     * Asserts if the class has Dependency Injection methods
     *
     * @return bool
     */
    public function hasInjectMethods(): bool
    {
        return !empty($this->injectMethods);
    }

    /**
     * @return bool
     */
    public function hasInjectProperties(): bool
    {
        return !empty($this->injectProperties);
    }
}
