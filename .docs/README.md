# Dependency Injection (DI)

## Content

- [ResourceExtension - autoload classes by definitions](#resourceextension)
- [ContainerAware - inject container](#containeraware)
- [MutableExtension - used in tests](#mutableextension)
- [InjectValueExtension - inject parameters](#injectvalueextension)
- [PassCompilerExtension - split big extension](#passcompilerextension)
- [NewExtensionsExtension - powerful extensions](#newextensionsextension)

## ResourceExtension

At first, you have to register extension.

```yaml
extensions:
    autoload: Contributte\DI\Extension\ResourceExtension
```

Secondly, define some resources.

```yaml
autoload:
    App\Model\Services\:
      paths: [%appDir%/model/services]
```

> It maybe looks familiar to you. You're right idea comes from [Symfony 3.3](http://symfony.com/doc/current/service_container/3.3-di-changes.html#the-new-default-services-yml-file).

That's all, `ResourceExtension` will try to register all non-abstract instantiable classes to container.

### Resources

```yaml
autoload:
    App\Model\Services\:
      paths: [%appDir%/model/services]
      excludes: [App\Model\Services\Gopay, App\Model\Services\CustomService\Testing]
      decorator: 
        tags: [autoload]
        setup:
          - setLogger(@customlogger)
        autowire: false # true
```

### Performance

Service loading is triggered only once at dependency injection container compile-time. You should be pretty fast, 
almost as [official registering presenter as services](https://api.nette.org/2.4/source-Bridges.ApplicationDI.ApplicationExtension.php.html#121-160).

## ContainerAware

This package provide missing `IContainerAware` interface for you Applications.

```yaml
extensions:
    aware: Contributte\DI\Extension\ContainerAwareExtension
```

From that moment you can use `IContainerAware` interface and let container inject.

```php
<?php

namespace App\Model;

use Contributte\DI\IContainerAware;
use Nette\DI\Container;

final class LoggableCachedEventDispatcher implements IContainerAware
{

    /** @var Container */
    protected $container;

    /**
     * @param Container $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

}
```

Don't repeat yourself, use `TContainerAware` trait.

```php
<?php

namespace App\Model;

use Contributte\DI\IContainerAware;
use Contributte\DI\TContainerAware;

final class LoggableCachedEventDispatcher implements IContainerAware
{

    use TContainerAware;

}
```

## MutableExtension

This extension is suitable for testing.

```php
$loader = new ContainerLoader(TEMP_DIR, TRUE);
$class = $loader->load(function (Compiler $compiler) {
    $compiler->addExtension('x', $mutable = new MutableExtension());

    // called -> loadConfiguration()
    $mutable->onLoad[] = function (CompilerExtension $ext, ContainerBuilder $builder) {
        $builder->addDefinition($ext->prefix('request'))
            ->setClass(Request::class)
            ->setFactory(RequestFactory::class . '::createHttpRequest');
    }; 

    // called -> beforeCompile()
    $mutable->onBefore[] = function (CompilerExtension $ext, ContainerBuilder $builder) {
        $classes = $builder->findByDefinition(Xyz::class);
    };
    
    ', 'neon'));
}, time());
```

## InjectValueExtension

This **awesome** extension allow you to inject values directly into public properties.

Let's say, we have service like this:

```php
class FooPresenter extends Presenter
{

    /** @var string @value(%appDir%/baz) */
    public $bar;

}
```

At first register `InjectValueExtension` under `extensions` key.

```yaml
extensions:
    injectvalue: Contributte\DI\Extension\InjectValueExtension
    
injectvalue:
    all: on/off
```

By default, extension `inject values` only for services having `inject.value` tag.
You can override it to inject to all services by define `all: on`. Or follow the prefer way 
and use Nette\DI decorator.

```yaml
decorator:
    App\MyBaseService:
      tags: [inject.value]

    App\MyBasePresenter:
      tags: [inject.value]
```

After all, the when the `FooPresenter` in created it will have filled `$bar` property with `<path>/www/baz`. Cool right?

## PassCompilerExtension

With this extension you can split your big extension/configuration into more compiler passes (Symfony idea).

```php
use Contributte\DI\Extension\PassCompilerExtension;

final class FoobarExtension extends PassCompilerExtension
{

    public function __construct() 
    {
        $this->addPass(new PartAPass($this));
        $this->addPass(new PartBPass($this));
    }

}
```

Extending `AbstractPass` define 3 methods:

- `loadPassConfiguration`
- `beforePassCompile`
- `afterPassCompile`

```php
use Contributte\DI\Pass\AbstractPass;

class PartAPass extension AbstractPass
{

    public function loadPassConfiguration()
    {
        $builder = $this->extension->getCompilerBuilder();
        // ...
    }

}
```

## NewExtensionsExtension

From time to time you get into the point when you have a lot of extensions. Some depends on others and reverse. 
Therefore comes the need of `NewExtensionsExtension`.

In classic Nette application you will see something like that:

```yaml
extensions:
    foo: App\DI\FooExtension
    bar: App\DI\BarExtension
    baz1: App\DI\Baz1Extension
    baz2: App\DI\Baz2Extension
```

The `bar` & `baz` require to have `foo` registered. How can resolve it?

At first you have to replace default `extensions` extension, yes, it's name is `extensions`! Change it manually
or via `ConfiguratorHelper` class.

**Manual replacement**

```php
$configurator->defaultExtensions['extensions'] = Contributte\DI\Extension\NewExtensionsExtension::class;
```

**ConfiguratorHelper**

```php
$configurator = new Configurator();
Contributte\DI\ConfiguratorHelper::upgrade($configurator);
```

**New-way how to register extensions**

```yaml
extensions:
    # Register by key
    baz1: App\DI\Baz1Extension
    
    # Register unnamed
    - App\DI\Baz2Extension
    
    # Register with priority
    bar:
        class: App\DI\BarExtension
        # default priority is 10, you can omit it
        priority: 10
    foo:
        class: App\DI\FooExtension
        priority: 5
```
