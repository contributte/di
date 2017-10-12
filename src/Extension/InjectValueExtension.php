<?php

namespace Contributte\DI\Extension;

use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\DI\ServiceDefinition;
use Nette\Utils\Strings;
use ReflectionProperty;

class InjectValueExtension extends CompilerExtension
{

	const TAG_INJECT_VALUE = 'inject.value';

	/** @var array */
	protected $defaults = [
		'all' => FALSE,
	];

	/**
	 * Find all definitions and inject into @value
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		$config = $this->validateConfig($this->defaults);

		$definitions = $config['all'] === TRUE
			? $this->getContainerBuilder()->getDefinitions()
			: array_map(
				[$this->getContainerBuilder(), 'getDefinition'],
				array_keys($this->getContainerBuilder()->findByTag(self::TAG_INJECT_VALUE))
			);

		foreach ($definitions as $def) {
			// If class is not defined, then skip it
			if (!$def->getClass()) continue;

			// Inject @value into definitin
			$this->inject($def);
		}
	}

	/**
	 * Inject into @value property
	 *
	 * @param ServiceDefinition $def
	 * @return void
	 */
	protected function inject(ServiceDefinition $def)
	{
		foreach (get_class_vars($def->getClass()) as $name => $var) {
			$rp = new ReflectionProperty($def->getClass(), $name);

			// Try to match property by regex
			// https://regex101.com/r/D6gc21/1
			$match = Strings::match($rp->getDocComment(), '#@value\((.+)\)#U');

			// If there's no @value annotation or it's not in propel format,
			// then skip it
			if ($match === NULL) continue;

			// Hooray, we have a match!
			list ($doc, $content) = $match;

			// Expand content of @value and setup to definition
			$def->addSetup('$' . $name, [$this->expand($content)]);
		}
	}

	/**
	 * @param string $value
	 * @return string
	 */
	protected function expand($value)
	{
		return Helpers::expand($value, $this->compiler->getConfig()['parameters']);
	}

}
