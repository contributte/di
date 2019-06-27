<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\LocatorDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Resolver;

class ExtensionDefinitionsHelper
{

	/** @var ContainerBuilder */
	private $builder;

	public function __construct(ContainerBuilder $containerBuilder)
	{
		$this->builder = $containerBuilder;
	}

	/**
	 * @param Definition[] $definitions
	 * @return ServiceDefinition[]
	 */
	public function getServiceDefinitionsFromAllDefinitions(array $definitions): array
	{
		$serviceDefinitions = [];
		$resolver = new Resolver($this->builder);

		foreach ($definitions as $definition) {
			if ($definition instanceof ServiceDefinition) {
				$serviceDefinitions[] = $definition;
			} elseif ($definition instanceof FactoryDefinition) {
				$serviceDefinitions[] = $definition->getResultDefinition();
			} elseif ($definition instanceof LocatorDefinition) {
				$references = $definition->getReferences();
				foreach ($references as $reference) {
					$reference = $resolver->normalizeReference($reference); // Check that reference is valid
					$definition = $resolver->resolveReference($reference); // Get definition by reference
					assert($definition instanceof ServiceDefinition); // Only ServiceDefinition should be possible here
					$serviceDefinitions[] = $definition;
				}
			} else {
				// Definition is of type:
				//		accessor - service definition exists independently
				//		imported - runtime-created service, cannot work with
				//		unknown
				continue;
			}
		}

		// Filter out duplicates - we cannot distinguish if service from LocatorDefinition is created by accessor or factory so duplicates are possible
		$serviceDefinitions = array_unique($serviceDefinitions, SORT_REGULAR);

		return $serviceDefinitions;
	}

}
