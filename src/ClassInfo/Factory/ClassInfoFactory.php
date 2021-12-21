<?php
namespace Merophp\ObjectManager\ClassInfo\Factory;

use DateTime;
use Exception;
use ReflectionClass;
use ReflectionException;
use Merophp\Reflection\ClassReflection;
use Merophp\Singleton\SingletonInterface;
use Merophp\ObjectManager\ClassInfo\ClassInfo;
use ReflectionParameter;

/**
 * Class info factory
 */
class ClassInfoFactory
{
    /**
     * Factory method that builds a ClassInfo Object for the given classname - using reflection
     *
     * @param string $className The class name to build the class info for
     * @return ClassInfo the class info
     * @throws Exception
     */
    public function buildClassInfoFromClassName(string $className): ClassInfo
    {
        if ($className === DateTime::class) {
            return new ClassInfo($className, [], [], false, false, []);
        }
        try {
            $reflectedClass = new ClassReflection($className);
        } catch (Exception $e) {
            throw new Exception('Could not analyse class: "' . $className . '" maybe not loaded or no autoloader? ' . $e->getMessage());
        }
        $constructorArguments = $this->getConstructorArguments($reflectedClass);
        $injectMethods = $this->getInjectMethods($reflectedClass);
        $isSingleton = $this->getIsSingleton($className);
        $isInitializeable = $this->getIsInitializeable($className);
        return new ClassInfo($className, $constructorArguments, $injectMethods, $isSingleton, $isInitializeable);
    }

    /**
     * Build a list of constructor arguments
     *
     * @param ReflectionClass $reflectedClass
     * @return array of parameter infos for constructor
     */
    private function getConstructorArguments(ReflectionClass $reflectedClass): array
    {
        $reflectionMethod = $reflectedClass->getConstructor();
        if (!is_object($reflectionMethod)) {
            return [];
        }
        $result = [];
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            /* @var $reflectionParameter ReflectionParameter */
            $info = [];
            $info['name'] = $reflectionParameter->getName();
            if ($reflectionParameter->getClass()) {
                $info['dependency'] = $reflectionParameter->getClass()->getName();
            }

            try {
                $info['defaultValue'] = $reflectionParameter->getDefaultValue();
            } catch (ReflectionException $e) {
            }

            $result[] = $info;
        }
        return $result;
    }

    /**
     * Build a list of inject methods for the given class.
     *
     * @param ReflectionClass $reflectedClass
     * @throws Exception
     * @return array (nameOfInjectMethod => nameOfClassToBeInjected)
     */
    private function getInjectMethods(ReflectionClass $reflectedClass): array
    {
        $result = [];
        $reflectionMethods = $reflectedClass->getMethods();
        if (is_array($reflectionMethods)) {
            foreach ($reflectionMethods as $reflectionMethod) {
                if ($reflectionMethod->isPublic() && $this->isNameOfInjectMethod($reflectionMethod->getName())) {
                    $reflectionParameter = $reflectionMethod->getParameters();
                    if (isset($reflectionParameter[0])) {
                        if (!$reflectionParameter[0]->getClass()) {
                            throw new Exception('Method "' . $reflectionMethod->getName() . '" of class "' . $reflectedClass->getName() . '" is marked as setter for Dependency Injection, but does not have a type annotation');
                        }
                        $result[$reflectionMethod->getName()] = $reflectionParameter[0]->getClass()->getName();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * This method checks if given method can be used for injection
     *
     * @param string $methodName
     * @return bool
     */
    private function isNameOfInjectMethod(string $methodName): bool
    {
        if (
            substr($methodName, 0, 6) === 'inject'
            && $methodName[6] === strtoupper($methodName[6])
            && $methodName !== 'injectSettings'
        ) {
            return true;
        }
        return false;
    }

    /**
     * This method is used to determine if a class is a singleton or not.
     *
     * @param string $classname
     * @return bool
     */
    private function getIsSingleton(string $classname): bool
    {
    	return in_array(SingletonInterface::class, class_implements($classname));
    }

    /**
     * This method is used to determine of the object is initializeable with the
     * method initializeObject.
     *
     * @param string $classname
     * @return bool
     */
    private function getIsInitializeable(string $classname): bool
    {
        return method_exists($classname, 'initializeObject');
    }
}
