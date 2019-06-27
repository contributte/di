<?php declare(strict_types = 1);

namespace Contributte\DI\Helper;

use Nette\DI\Compiler;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\LocatorDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\DI\Resolver;
use Nette\Utils\Strings;

class ExtensionDefinitionsHelper
{

	/** @var ContainerBuilder */
	private $builder;

	/** @var Compiler */
	private $compiler;

	public function __construct(ContainerBuilder $containerBuilder, Compiler $compiler)
	{
		$this->builder = $containerBuilder;
		$this->compiler = $compiler;
	}

	/**
	 * @param Definition[] $definitions
	 * @return ServiceDefinition[]
	 */
	public function getServiceDefinitionsFromDefinitions(array $definitions): array
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

	/**
	 * @param string|mixed[]|Statement $config
	 * @return Definition|string
	 */
	public function getDefinitionFromConfig($config, string $preferredPrefix)
	{
		$builder = $this->builder;

		if (is_string($config) && Strings::startsWith($config, '@')) { // Definition is defined in ServicesExtension, try get it
			$definitionName = substr($config, 1);

			if ($builder->hasDefinition($definitionName)) { // Definition is already loaded (beforeCompile phase), return it
				return $builder->getDefinition($definitionName);
			}

			return $config; // Definition not loaded yet (loadConfiguration phase), return reference string
		}

		// Raw configuration given, create definition from it
		$this->compiler->loadDefinitionsFromConfig([$preferredPrefix => $config]);
		return $builder->getDefinition($preferredPrefix);
	}

}
