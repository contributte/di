<?php declare(strict_types = 1);

/**
 * Test: Decorator/Decorator
 */

use Contributte\DI\Decorator\Decorator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$extension = new class extends CompilerExtension
		{

			public function loadConfiguration(): void
			{
				$builder = $this->getContainerBuilder();

				$builder->addDefinition($this->prefix('foo'))
					->setType(Foo::class);

				$builder->addDefinition($this->prefix('inject'))
					->setType(InjectTester::class);
			}

			public function beforeCompile(): void
			{
				$builder = $this->getContainerBuilder();

				$decorator = Decorator::of($builder);
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
	Assert::notEqual(new Foo(), $container->getService('x.foo'));
	Assert::equal([new InjectTester(), 'foo'], $container->getService('x.foo')->setup);
	Assert::equal(['x.foo' => true], $container->findByTag('tag'));
});

abstract class Base
{

	/** @var mixed[] */
	public $setup = [];

	public function setup(InjectTester $tester, string $bar)
	{
		$this->setup = func_get_args();
	}

}

class Foo extends Base
{

}

class InjectTester
{}
