<?php declare(strict_types = 1);

use Contributte\DI\Decorator\Decorator;
use Contributte\DI\Helper\ExtensionDefinitionsHelper;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tests\Fixtures\Inject\Base;
use Tests\Fixtures\Inject\Child;
use Tests\Fixtures\Inject\Tester;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(static function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(static function (Compiler $compiler): void {
		$extension = new class extends CompilerExtension
		{

			public function loadConfiguration(): void
			{
				$builder = $this->getContainerBuilder();

				$builder->addDefinition($this->prefix('child'))
					->setType(Child::class);

				$builder->addDefinition($this->prefix('inject'))
					->setType(Tester::class);
			}

			public function beforeCompile(): void
			{
				$builder = $this->getContainerBuilder();

				$decorator = Decorator::of($builder, new ExtensionDefinitionsHelper($this->compiler));
				$decorator->decorate(Base::class)
					->addSetup('setup', [
						'bar' => 'foo',
					])
					->addTags(['tag']);
			}

		};
		$compiler->addExtension('x', $extension);
	}, 1);
	/** @var Container $container */
	$container = new $class();
	Assert::notEqual(new Child(), $container->getService('x.child'));
	Assert::equal([new Tester(), 'foo'], $container->getService('x.child')->setup);
	Assert::equal(['x.child' => true], $container->findByTag('tag'));
});
