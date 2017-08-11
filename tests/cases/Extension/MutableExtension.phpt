<?php

/**
 * Test: Extension\MutableExtension
 */

use Contributte\DI\Extension\MutableExtension;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\ContainerBuilder;
use Nette\DI\ContainerLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		$mutable = new MutableExtension();
		$mutable->onLoad[] = function (CompilerExtension $ext, ContainerBuilder $builder) {
			$builder->addDefinition($ext->prefix('service'))
				->setClass(stdClass::class);
		};
		$compiler->addExtension('x', $mutable);
	}, 1);
	/** @var Container $container */
	$container = new $class;
	Assert::equal(new stdClass(), $container->getService('x.service'));
});
