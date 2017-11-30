<?php

/**
 * Test: Extension\NewExtensionsExtension
 */

use Contributte\DI\ConfiguratorHelper;
use Nette\Configurator;
use Nette\DI\Container;
use Tester\Assert;
use Tester\FileMock;
use Tests\Fixtures\Priority\FirstExtension;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$configurator = new Configurator();
	ConfiguratorHelper::upgrade($configurator);
	$configurator->setTempDirectory(TEMP_DIR);
	$configurator->addConfig(FileMock::create('
	extensions:
		# Register by key
		normal: Tests\Fixtures\Priority\NormalExtension
		
		# Register unnamed
		- Tests\Fixtures\Priority\NormalExtension
		
		# Register with priority
		second:
			class: Tests\Fixtures\Priority\SecondExtension
			priority: 10
		first:
			class: Tests\Fixtures\Priority\FirstExtension
			priority: 5
	', 'neon'));

	$class = $configurator->createContainer();

	/** @var Container $container */
	$container = new $class;
	Assert::type(FirstExtension::class, $container->getService('shared'));
});
