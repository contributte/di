<?php

/**
 * Test: Autoload\DI\AutoloaderExtension - interface autoloading
 */

use Contributte\DI\Autoload\AutoloadService;
use Contributte\DI\Autoload\DI\AutoloaderExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tests\Autoload\Services\TestInterface;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);

	$class = $loader->load(function (Compiler $compiler) {
		$compiler->addExtension('autoload', new AutoloaderExtension());
		$compiler->addConfig(['parameters' => ['appDir' => TESTER_DIR . '/cases']]);
		// Clear default annotations
		$compiler->addConfig(['autoload' => ['annotations' => []]]);
	}, time());

	/** @var Container $container */
	$container = new $class();
	Assert::type(Container::class, $container);

	Assert::count(1, $container->findByType(AutoloadService::class));
	Assert::type(TestInterface::class, $container->getService('autoload.1'));
});
