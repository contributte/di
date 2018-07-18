<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Contributte\DI\IContainerAware;
use Nette\DI\CompilerExtension;

class ContainerAwareExtension extends CompilerExtension
{

	/**
	 * Tweak DI container
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		// Register as services
		foreach ($builder->findByType(IContainerAware::class) as $service) {
			$service->addSetup('setContainer');
		}
	}

}
