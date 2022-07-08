<?php declare(strict_types = 1);

/**
 * Test: Extension\ResourceExtension
 */

use Contributte\DI\Extension\ResourceExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Tester\Assert;
use Tester\FileMock;
use Tests\Fixtures\Bar\BarService;
use Tests\Fixtures\Baz\BazService;
use Tests\Fixtures\Baz\Nested\NestedBazService;
use Tests\Fixtures\Foo\FooBarService;
use Tests\Fixtures\Foo\FooService;
use Tests\Fixtures\Scalar\ScalarService;

require_once __DIR__ . '/../../bootstrap.php';

// Autoload services
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Foo\:
					paths: [%appDir%/fixtures/Foo]
		', 'neon'));
	}, 1);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(FooBarService::class));
	Assert::type('object', $container->getByType(FooService::class));
});

// Skip interface & abstract classes
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/fixtures/Bar]
		', 'neon'));
	}, 2);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(BarService::class));
});

// Multiple resources
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Foo\:
					paths: [%appDir%/fixtures/Foo]

				Tests\Fixtures\Bar\:
					paths: [%appDir%/fixtures/Bar]
		', 'neon'));
	}, 3);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(FooBarService::class));
	Assert::type('object', $container->getByType(FooService::class));
	Assert::type('object', $container->getByType(BarService::class));
});

// Exclude namespace
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Baz\:
					paths: [%appDir%/fixtures/Baz]
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
test(static function (): void {
	Assert::throws(static function (): void {
		$loader = new ContainerLoader(TEMP_DIR, true);
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
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Baz\:
					paths: [%appDir%/fixtures/Baz]
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
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/fixtures/Bar]
					decorator:
						tags: [bazbaz]
		', 'neon'));
	}, 8);

	/** @var Container $container */
	$container = new $class();

	Assert::count(1, $container->findByTag('bazbaz'));
});

// Decorate services - add setup
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		services:
			- stdClass

		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/fixtures/Bar]
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
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: [%appDir%/fixtures/Bar]
					decorator:
						autowired: false
		', 'neon'));
	}, 10);

	/** @var Container $container */
	$container = new $class();

	Assert::null($container->getByType(BarService::class, false));
});

// Register services manually
test(static function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		services:
			scalar: Tests\Fixtures\Scalar\ScalarService("foobar")
		autoload:
			resources:
				Tests\Fixtures\Scalar\:
					paths: [%appDir%/fixtures/Scalar]
		', 'neon'));
	}, 11);

	/** @var Container $container */
	$container = new $class();

	/** @var ScalarService $service */
	$service = $container->getService('scalar');

	Assert::equal('foobar', $service->text);
});

// Register services manually (exception)
test(static function (): void {
	Assert::exception(function (): void {
		$loader = new ContainerLoader(TEMP_DIR, true);
		$loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('autoload', new ResourceExtension());
			$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
			$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Scalar\:
					paths: [%appDir%/fixtures/Scalar]
		', 'neon'));
		}, 12);
	}, ServiceCreationException::class, "Service 'autoload._Tests_Fixtures_Scalar_.2' (type of Tests\Fixtures\Scalar\ScalarService): Parameter \$text in ScalarService::__construct() has no class type or default value, so its value must be specified.");
});
