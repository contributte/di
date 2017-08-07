# Dependency Injection (DI)

## Content

- [ResourceExtension - autoload classes by definitions](#resourceextension)
- [ContainerAware - inject container](#containeraware)

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
