<?php

namespace Tests\Fixtures\Priority;

use Nette\DI\CompilerExtension;

final class FirstExtension extends CompilerExtension
{

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition('shared')
			->setFactory(self::class);
	}

}
