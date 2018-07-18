<?php declare(strict_types = 1);

namespace Tests\Fixtures\Priority;

use Nette\DI\CompilerExtension;

final class FirstExtension extends CompilerExtension
{

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition('shared')
			->setFactory(self::class);
	}

}
