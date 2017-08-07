<?php

/**
 * Test: Extension\ContainerAwareExtension
 */

use Contributte\DI\Extension\ContainerAwareExtension;
use Contributte\DI\IContainerAware;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\FileMock;
use Tests\Fixtures\TestContainerAware;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('aware', new ContainerAwareExtension());
		$compiler->loadConfig(FileMock::create('
		services:
			- Tests\Fixtures\TestContainerAware
		', 'neon'));
	}, time());

	/** @var Container $container */
	$container = new $class();

	Assert::count(1, $container->findByType(IContainerAware::class));
	Assert::same($container, $container->getByType(TestContainerAware::class)->getContainer());
});
