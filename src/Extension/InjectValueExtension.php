<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Contributte\DI\Helper\ExtensionDefinitionsHelper;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Strings;
use ReflectionProperty;
use stdClass;

/**
 * @property-read stdClass $config
 */
class InjectValueExtension extends CompilerExtension
{

	public const TAG_INJECT_VALUE = 'inject.value';

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'all' => Expect::bool(false),
		]);
	}

	/**
	 * Find all definitions and inject into @value
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$definitions = $config->all
			? $builder->getDefinitions()
			: array_map(
				[$builder, 'getDefinition'],
				array_keys($builder->findByTag(self::TAG_INJECT_VALUE))
			);

		$definitionsHelper = new ExtensionDefinitionsHelper($this->compiler);
		$definitions = $definitionsHelper->getServiceDefinitionsFromDefinitions($definitions);

		foreach ($definitions as $def) {
			// Inject @value into definition
			$this->inject($def);
		}
	}

	/**
	 * Inject into @value property
	 */
	protected function inject(ServiceDefinition $def): void
	{
		$class = $def->getType();

		// Class is not defined, skip it
		if ($class === null) {
			return;
		}

		foreach (get_class_vars($class) as $name => $var) {
			$rp = new ReflectionProperty($class, $name);

			// Try to match property by regex
			// https://regex101.com/r/D6gc21/1
			$match = Strings::match((string) $rp->getDocComment(), '#@value\((.+)\)#U');

			// If there's no @value annotation or it's not in propel format,
			// then skip it
			if ($match === null) {
				continue;
			}

			// Hooray, we have a match!
			[,$content] = $match;

			// Expand content of @value and setup to definition
			$def->addSetup('$' . $name, [$this->expand($content)]);
		}
	}

	/**
	 * @return mixed
	 */
	protected function expand(string $value)
	{
		return Helpers::expand($value, $this->getContainerBuilder()->parameters);
	}

}
