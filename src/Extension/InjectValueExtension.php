<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\DI\ServiceDefinition;
use Nette\Utils\Strings;
use ReflectionProperty;

class InjectValueExtension extends CompilerExtension
{

	public const TAG_INJECT_VALUE = 'inject.value';

	/** @var mixed[] */
	protected $defaults = [
		'all' => false,
	];

	/**
	 * Find all definitions and inject into @value
	 */
	public function beforeCompile(): void
	{
		$config = $this->validateConfig($this->defaults);

		$definitions = $config['all'] === true
			? $this->getContainerBuilder()->getDefinitions()
			: array_map(
				[$this->getContainerBuilder(), 'getDefinition'],
				array_keys($this->getContainerBuilder()->findByTag(self::TAG_INJECT_VALUE))
			);

		foreach ($definitions as $def) {
			// If class is not defined, then skip it
			if ($def->getType() === null) continue;

			// Inject @value into definitin
			$this->inject($def);
		}
	}

	/**
	 * Inject into @value property
	 */
	protected function inject(ServiceDefinition $def): void
	{
		$class = $def->getType();

		if ($class === null) return;

		foreach (get_class_vars($class) as $name => $var) {
			$rp = new ReflectionProperty($def->getType(), $name);

			// Try to match property by regex
			// https://regex101.com/r/D6gc21/1
			$match = Strings::match($rp->getDocComment(), '#@value\((.+)\)#U');

			// If there's no @value annotation or it's not in propel format,
			// then skip it
			if ($match === null) continue;

			// Hooray, we have a match!
			 [$doc, $content] = $match;

			// Expand content of @value and setup to definition
			$def->addSetup('$' . $name, [$this->expand($content)]);
		}
	}

	/**
	 * @return mixed
	 */
	protected function expand(string $value)
	{
		return Helpers::expand($value, $this->compiler->getConfig()['parameters']);
	}

}
