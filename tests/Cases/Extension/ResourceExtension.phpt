<?php declare(strict_types = 1);

use Contributte\DI\Extension\ResourceExtension;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\InjectExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Tester\Assert;
use Tester\FileMock;
use Tests\Fixtures\Bar\BarService;
use Tests\Fixtures\Baz\BazService;
use Tests\Fixtures\Baz\Nested\NestedBazService;
use Tests\Fixtures\Decorator\InjectService;
use Tests\Fixtures\Foo\FooBarService;
use Tests\Fixtures\Foo\FooService;
use Tests\Fixtures\Scalar\Scalar2Service;
use Tests\Fixtures\Scalar\ScalarService;
use Tests\Fixtures\Scalar\ZScalarService;

require_once __DIR__ . '/../../bootstrap.php';

const APP_DIR = __DIR__ . '/../..';

// Autoload services
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Foo\:
					paths: [%appDir%/Fixtures/Foo]
		', 'neon'));
	}, 1);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(FooBarService::class));
	Assert::type('object', $container->getByType(FooService::class));
});

// Skip interface & abstract classes
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/Fixtures/Bar]
		', 'neon'));
	}, 2);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(BarService::class));
});

// Multiple resources
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Foo\:
					paths: [%appDir%/Fixtures/Foo]

				Tests\Fixtures\Bar\:
					paths: [%appDir%/Fixtures/Bar]
		', 'neon'));
	}, 3);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(FooBarService::class));
	Assert::type('object', $container->getByType(FooService::class));
	Assert::type('object', $container->getByType(BarService::class));
});

// Exclude namespace
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Baz\:
					paths: [%appDir%/Fixtures/Baz]
					excludes: [Tests\Fixtures\Baz\Nested\]
		', 'neon'));
	}, 4);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(BazService::class));
	Assert::throws(static function () use ($container): void {
		$container->getByType(NestedBazService::class);
	}, MissingServiceException::class);
});

// Invalid resource - must end with /
Toolkit::test(static function (): void {
	Assert::throws(static function (): void {
		$loader = new ContainerLoader(Environment::getTestDir(), true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('autoload', new ResourceExtension());
			$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Baz:
		', 'neon'));
		}, 5);

		new $class();
	}, RuntimeException::class, 'Resource "Tests\Fixtures\Baz" must end with /');
});

// Exclude whole namespace
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Baz\:
					paths: [%appDir%/Fixtures/Baz]
					excludes: [Tests\Fixtures\Baz\]
		', 'neon'));
	}, 6);

	/** @var Container $container */
	$container = new $class();

	Assert::throws(static function () use ($container): void {
		$container->getByType(BazService::class);
	}, MissingServiceException::class);
	Assert::throws(static function () use ($container): void {
		$container->getByType(NestedBazService::class);
	}, MissingServiceException::class);
});

// Decorate services - add tags
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/Fixtures/Bar]
					decorator:
						tags: [bazbaz]
		', 'neon'));
	}, 8);

	/** @var Container $container */
	$container = new $class();

	Assert::count(1, $container->findByTag('bazbaz'));
});

// Decorate services - add setup
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		services:
			- stdClass

		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/Fixtures/Bar]
					decorator:
						setup:
							- setLogger
		', 'neon'));
	}, 9);

	/** @var Container $container */
	$container = new $class();

	Assert::same($container->getByType('stdClass'), $container->getByType(BarService::class)->getLogger());
});

// Decorate services - add setup
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/Fixtures/Bar]
					decorator:
						autowired: false
		', 'neon'));
	}, 10);

	/** @var Container $container */
	$container = new $class();

	Assert::null($container->getByType(BarService::class, false));
});

// Register services manually
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		services:
			scalar: Tests\Fixtures\Scalar\ScalarService("foobar")
		autoload:
			resources:
				Tests\Fixtures\Scalar\:
					paths: [%appDir%/Fixtures/Scalar]
		', 'neon'));
	}, 11);

	/** @var Container $container */
	$container = new $class();

	Assert::type(ScalarService::class, $container->getByType(ScalarService::class));
	Assert::type(Scalar2Service::class, $container->getByType(Scalar2Service::class));
	Assert::type(ZScalarService::class, $container->getByType(ZScalarService::class));

	/** @var ScalarService $service */
	$service = $container->getService('scalar');
	Assert::equal('foobar', $service->text);
});

// Register services manually (exception)
Toolkit::test(static function (): void {
	Assert::exception(function (): void {
		$loader = new ContainerLoader(Environment::getTestDir(), true);
		$loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('autoload', new ResourceExtension());
			$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
			$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Scalar\:
					paths: [%appDir%/Fixtures/Scalar]
		', 'neon'));
		}, 12);
	}, ServiceCreationException::class, '~Service \'autoload\._Tests_Fixtures_Scalar_\.\d+\' \(type of Tests\\\\Fixtures\\\\Scalar\\\\ScalarService\): Parameter \$text in ScalarService::__construct\(\) has no class type or default value, so its value must be specified\.~');
});

// Register services manually
Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addExtension('inject', new InjectExtension());
		$compiler->addConfig(['parameters' => ['appDir' => APP_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Decorator\:
					paths: [%appDir%/Fixtures/Decorator]
					decorator:
						inject: true
		', 'neon'));
	}, 13);

	/** @var Container $container */
	$container = new $class();

	/** @var InjectService $service */
	$service = $container->getByType(InjectService::class);

	Assert::notNull($service->authenticator);
});
