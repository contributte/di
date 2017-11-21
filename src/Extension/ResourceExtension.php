<?php

namespace Contributte\DI\Extension;

use Nette\Caching\Storages\DevNullStorage;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\DI\Statement;
use Nette\Loaders\RobotLoader;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use ReflectionClass;
use RuntimeException;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ResourceExtension extends CompilerExtension
{

	/** @var array */
	private $defaults = [
		'resources' => [],
	];

	/** @var array */
	private $resource = [
		'paths' => [],
		'excludes' => [],
		'decorator' => [],
	];

	/** @var array */
	private $decorator = [
		'tags' => [],
		'setup' => [],
		'autowired' => NULL,
		'inject' => NULL,
	];

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Expand config (cause %appDir% etc..)
		$config = $this->validateConfig($this->defaults);
		$config = $this->config = Helpers::expand($config, $builder->parameters);

		foreach ($config['resources'] as $namespace => $resource) {
			if (substr($namespace, -1) !== '\\') {
				throw new RuntimeException(sprintf('Resource "%s" must end with /', $namespace));
			}

			// Merge and validace resource config
			$resource = $this->validateConfig($this->resource, $resource, $namespace);

			// Normalize resource config
			if (is_scalar($resource['paths'])) $resource['paths'] = [$resource['paths']];
			if (is_scalar($resource['excludes'])) $resource['excludes'] = [$resource['excludes']];

			// Find classes of given resource
			$classes = $this->findClasses($namespace, $resource['paths'], $resource['excludes']);

			// Register services of given resource
			$counter = 1;
			$name = preg_replace('#\W+#', '_', '.' . $namespace);
			foreach ($classes as $class) {
				$def = $builder->addDefinition($this->prefix($name . '.' . ($counter++)))
					->setClass($class);

				// Merge and validace decorator config
				$decorator = $this->validateConfig($this->decorator, $resource['decorator'], $namespace);

				if ($decorator['tags']) {
					$def->setTags(Arrays::normalize($decorator['tags'], TRUE));
				}

				if ($decorator['setup']) {
					foreach ($decorator['setup'] as $setup) {
						if (is_array($setup)) {
							$setup = new Statement(key($setup), array_values($setup));
						}
						$def->addSetup($setup);
					}
				}

				if ($decorator['autowired'] !== NULL) {
					$def->setAutowired($decorator['autowired']);
				}

				if ($decorator['inject'] !== NULL) {
					$def->setInject($decorator['inject']);
				}
			}
		}
	}

	/**
	 * HELPERS *****************************************************************
	 */

	/**
	 * Find classes by given arguments
	 *
	 * @param string $namespace
	 * @param array $dirs
	 * @param array $excludes
	 * @return string[]
	 */
	protected function findClasses($namespace, array $dirs, array $excludes = [])
	{
		$loader = $this->createLoader();
		$loader->addDirectory($dirs);
		$loader->rebuild();

		$indexed = $loader->getIndexedClasses();
		$classes = [];
		foreach ($indexed as $class => $file) {
			// Different namespace
			if (!Strings::startsWith($class, $namespace)) continue;

			// Excluded namespace
			if (array_filter($excludes, function ($exclude) use ($class) {
				return Strings::startsWith($class, $exclude);
			})) continue;

			// Skip not existing class
			if (!class_exists($class, TRUE)) continue;

			// Detect by reflection
			$ct = new ReflectionClass($class);

			// Skip abstract
			if ($ct->isAbstract()) continue;

			// All tests passed, it's our class
			$classes[] = $class;
		}

		return $classes;
	}

	/**
	 * @return RobotLoader
	 */
	protected function createLoader()
	{
		$robot = new RobotLoader();

		// From version >=3.0.0, there's no setCacheStorage method
		if (method_exists($robot, 'setCacheStorage')) {
			$robot->setCacheStorage(new DevNullStorage());
		}

		return $robot;
	}

}
