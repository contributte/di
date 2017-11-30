<?php

namespace Tests\Fixtures\Priority;

use Nette\DI\CompilerExtension;

final class SecondExtension extends CompilerExtension
{

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->getDefinition('shared');
	}

}
