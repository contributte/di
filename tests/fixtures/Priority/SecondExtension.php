<?php declare(strict_types = 1);

namespace Tests\Fixtures\Priority;

use Nette\DI\CompilerExtension;

final class SecondExtension extends CompilerExtension
{

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->getDefinition('shared');
	}

}
