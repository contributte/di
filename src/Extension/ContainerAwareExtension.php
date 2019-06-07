<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Contributte\DI\Finder;
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

		$finder = new Finder($builder);
		$definitions = $finder->getServiceDefinitionsFromAllDefinitions($builder->findByType(IContainerAware::class));

		// Register as services
		foreach ($definitions as $definition) {
			$definition->addSetup('setContainer');
		}
	}

}
