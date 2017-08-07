<?php

/**
 * Test: Extension\ResourceExtension
 */

use Contributte\DI\Extension\ResourceExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\MissingServiceException;
use Tester\Assert;
use Tester\FileMock;
use Tests\Fixtures\Bar\BarService;
use Tests\Fixtures\Baz\BazService;
use Tests\Fixtures\Baz\Nested\NestedBazService;
use Tests\Fixtures\Foo\FooBarService;
use Tests\Fixtures\Foo\FooService;

require_once __DIR__ . '/../../bootstrap.php';

// Autoload services
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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
	Assert::throws(function () use ($container) {
		$container->getByType(NestedBazService::class);
	}, MissingServiceException::class, sprintf('Service of type %s not found.', NestedBazService::class));
});

// Invalid resource - must end with /
test(function () {
	Assert::throws(function () {
		$loader = new ContainerLoader(TEMP_DIR, TRUE);
		$class = $loader->load(function (Compiler $compiler) {
			$compiler->addExtension('autoload', new ResourceExtension());
			$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Baz:
		', 'neon'));
		}, 5);

		/** @var Container $container */
		$container = new $class();
	}, RuntimeException::class, 'Resource "Tests\Fixtures\Baz" must end with /');
});

// Exclude whole namespace
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
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

	Assert::throws(function () use ($container) {
		$container->getByType(BazService::class);
	}, MissingServiceException::class, sprintf('Service of type %s not found.', BazService::class));
	Assert::throws(function () use ($container) {
		$container->getByType(NestedBazService::class);
	}, MissingServiceException::class, sprintf('Service of type %s not found.', NestedBazService::class));
});

// Paths as string
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: %appDir%/fixtures/Bar
		', 'neon'));
	}, 7);

	/** @var Container $container */
	$container = new $class();

	Assert::type('object', $container->getByType(BarService::class));
});

// Decorate services - add tags
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: %appDir%/fixtures/Bar
					decorator:
						tags: [bazbaz]
		', 'neon'));
	}, 8);

	/** @var Container $container */
	$container = new $class();

	Assert::count(1, $container->findByTag('bazbaz'));
});

// Decorate services - add setup
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		services:
			- stdClass
		
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: %appDir%/fixtures/Bar
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
test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('autoload', new ResourceExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR]]);
		$compiler->loadConfig(FileMock::create('
		autoload:
			resources:
				Tests\Fixtures\Bar\:
					paths: %appDir%/fixtures/Bar
					decorator:
						autowired: false
		', 'neon'));
	}, 10);

	/** @var Container $container */
	$container = new $class();

	Assert::null($container->getByType(BarService::class, FALSE));
});
