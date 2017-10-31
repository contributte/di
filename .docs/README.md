# Dependency Injection (DI)

## Content

- [ResourceExtension - autoload classes by definitions](#resourceextension)
- [ContainerAware - inject container](#containeraware)
- [MutableExtension - used in tests](#mutableextension)
- [InjectValueExtension - inject parameters](#injectvalueextension)
- [PassCompilerExtension - split big extension](#passcompilerextension)

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
At this moment you can use `IContainerAware` interface and let container to be injected.

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
    $mutable->onLoad[] = function (CompilerExtension $ext, ContainerBuilder $builder) {
        $builder->addDefinition($ext->prefix('request'))
            ->setClass(Request::class)
            ->setFactory(RequestFactory::class . '::createHttpRequest');
    };
    
    ', 'neon'));
}, time());
```

## InjectValueExtension

This **awesome** extension allowed you to inject values directly into public properties.

Let's say, we have service like this:

```php
class FooBarService
{

    /** @var string @value(%appDir%/baz) */
    public $baz;

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

## PassCompilerExtension

With this extension you can split your big extension/configuration into more compiler passes (symfony idea).

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
