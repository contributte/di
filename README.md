![](https://heatbadger.now.sh/github/readme/contributte/di/)

<p align=center>
  <a href="https://github.com/contributte/di/actions"><img src="https://badgen.net/github/checks/contributte/di/master?cache=300"></a>
  <a href="https://coveralls.io/r/contributte/di"><img src="https://badgen.net/coveralls/c/github/contributte/di?cache=300"></a>
  <a href="https://packagist.org/packages/contributte/di"><img src="https://badgen.net/packagist/dm/contributte/di"></a>
  <a href="https://packagist.org/packages/contributte/di"><img src="https://badgen.net/packagist/v/contributte/di"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/contributte/di"><img src="https://badgen.net/packagist/php/contributte/di"></a>
  <a href="https://github.com/contributte/di"><img src="https://badgen.net/github/license/contributte/di"></a>
  <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Extra dependency injection extensions and helpers for Nette applications.

## Versions

| State       | Version | Branch   | Nette | PHP     |
|-------------|---------|----------|-------|---------|
| dev         | `^0.7`  | `master` | 3.2+  | `>=8.2` |
| stable      | `^0.6`  | `master` | 3.2+  | `>=8.2` |

## Installation

To install latest version of `contributte/di` use [Composer](https://getcomposer.org).

```bash
composer require contributte/di
```

## Content

- [ResourceExtension](#resourceextension)
  - [Resources](#resources)
  - [Performance](#performance)
- [ContainerAware](#containeraware)
- [MutableExtension](#mutableextension)
- [InjectValueExtension](#injectvalueextension)
- [PassCompilerExtension](#passcompilerextension)
- [Decorator](#decorator)

## ResourceExtension

First, you have to register the extension.

```neon
extensions:
	autoload: Contributte\DI\Extension\ResourceExtension
```

Second, define some resources.

```neon
autoload:
	resources:
		App\Model\Services\:
			paths: [%appDir%/model/services]
```

> It may look familiar to you. You're right, the idea comes from [Symfony 3.3](http://symfony.com/doc/current/service_container/3.3-di-changes.html#the-new-default-services-yml-file).

That's all, the `ResourceExtension` will try to register all non-abstract instantiable classes to the container.

‼️ ResourceExtension should be the first extension in your `extensions` list.

### Resources

```neon
autoload:
	App\Model\Services\:
		paths: [%appDir%/model/services]
		excludes: [App\Model\Services\Gopay, App\Model\Services\CustomService\Testing]
		decorator:
			tags: [autoload]
			setup:
				- setLogger(@customlogger)
		autowired: false # true
		inject: true # enables inject annotations and methods
```

### Performance

Service loading is triggered only once at dependency injection container compile-time. It should be pretty fast,
almost as [official registering of presenters as services](https://api.nette.org/2.4/source-Bridges.ApplicationDI.ApplicationExtension.php.html#121-160).

## ContainerAware

This package provides the missing `IContainerAware` interface for your applications.

```neon
extensions:
	aware: Contributte\DI\Extension\ContainerAwareExtension
```

From that moment you can use the `IContainerAware` interface and let the container inject.

```php
<?php

namespace App\Model;

use Contributte\DI\IContainerAware;
use Nette\DI\Container;

final class LoggableCachedEventDispatcher implements IContainerAware
{

	/** @var Container */
	protected $container;

	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

}
```

Don't repeat yourself, use the `TContainerAware` trait.

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
use Contributte\DI\Extension\MutableExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Compiler;
use Nette\DI\ContainerBuilder;
use Nette\DI\ContainerLoader;

$loader = new ContainerLoader(TEMP_DIR, TRUE);
$class = $loader->load(static function (Compiler $compiler): void {
	$compiler->addExtension('x', $mutable = new MutableExtension());

	// called -> loadConfiguration()
	$mutable->onLoad[] = function (CompilerExtension $ext, ContainerBuilder $builder): void {
		$builder->addDefinition($ext->prefix('request'))
			->setType(Request::class)
			->setFactory(RequestFactory::class . '::createHttpRequest');
	};

	// called -> beforeCompile()
	$mutable->onBefore[] = static function (CompilerExtension $ext, ContainerBuilder $builder): void {
		$definitions = $builder->findByType(Xyz::class);
	};

	', 'neon'));
}, time());
```

## InjectValueExtension

This **awesome** extension allows you to inject values directly into public properties.

Let's say we have a service like this:

```php
class FooPresenter extends Presenter
{

	/** @var string @value(%appDir%/baz) */
	public $bar;

}
```

First, register `InjectValueExtension` under `extensions` key.

```neon
extensions:
	injectValue: Contributte\DI\Extension\InjectValueExtension

injectValue:
	all: on/off
```

By default, the extension `injects values` only for services having the `inject.value` tag.
You can override it to inject to all services by defining `all: on`. Or follow the preferred way
and use the Nette\DI decorator.

```neon
decorator:
	App\MyBaseService:
		tags: [inject.value]

	App\MyBasePresenter:
		tags: [inject.value]
```

In the end, after creating the `FooPresenter`, the `$bar` property will be filled with `<path>/www/baz`. Cool right?

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

Extending `AbstractPass` defines 3 methods:

- `loadPassConfiguration`
- `beforePassCompile`
- `afterPassCompile`

```php
use Contributte\DI\Pass\AbstractPass;

class PartAPass extends AbstractPass
{

	public function loadPassConfiguration(): void
	{
		$builder = $this->extension->getCompilerBuilder();
		// ...
	}

}
```

## Decorator

Using decorator you can programmatically decorate services. It finds all definitions by given type and add tags and setup as you know in decorator section in neon. Useful in libraries.

```php
use Contributte\DI\Decorator\Decorator;
use Nette\DI\CompilerExtension;

final class FooExtension extends CompilerExtension
{

	public function beforeCompile(): void
	{
		Decorator::of($this->getContainerBuilder())
		  ->decorate(BaseGrid::class)
			->addSetup('injectGrid')
			->addTags(['grid']);
	}

}
```

## Development

See [how to contribute](https://contributte.org) to this package. This package is currently maintained by these authors.

<a href="https://github.com/f3l1x">
    <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners) **contributte** development team.
Also thank you for using this package.
