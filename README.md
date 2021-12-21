# Introduction

Object manager with dependency injection for the Merophp Framework.

## Installation

Via composer:

<code>
composer require merophp/object-manager
</code>

## Basic Usage

<pre><code>require_once 'vendor/autoload.php';

use Merophp\ObjectManager\ObjectContainer;
use Merophp\ObjectManager\ObjectManager;

$oc = new ObjectContainer;
ObjectManager::setObjectContainer($oc);

$myInstance = ObjectManager::get(MyClass::class);

</code></pre>

### Dependency Injection
The object manager will scan the classes he has to instantiate for injection 
methods will use them to inject the dependencies. 

<pre><code>require_once 'vendor/autoload.php';

use Merophp\ObjectManager\ObjectContainer;
use Merophp\ObjectManager\ObjectManager;

class Foo
{
    public Bar $bar = null;

    public function injectBar(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function getBar()
    {
        return $this->bar;
    }
}

$myFooInstance = ObjectManager::get(Foo::class);
$myBarInstance = $myFooInstance->getBar();
</code></pre>

By instantiating from class <i>Foo</i> the object manager will also instantiate the dependency <i>Bar</i>.

