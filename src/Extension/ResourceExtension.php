<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Nette\Caching\Storages\DevNullStorage;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\DI\Statement;
use Nette\InvalidStateException;
use Nette\Loaders\RobotLoader;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use ReflectionClass;
use RuntimeException;

class ResourceExtension extends CompilerExtension
{

	/** @var mixed[] */
	private $defaults = [
		'resources' => [],
	];

	/** @var mixed[] */
	private $resource = [
		'paths' => [],
		'excludes' => [],
		'decorator' => [],
	];

	/** @var mixed[] */
	private $decorator = [
		'tags' => [],
		'setup' => [],
		'autowired' => null,
		'inject' => null,
	];

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
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
				// Check already registered classes
				if ($builder->getByType($class) !== null) return;

				$def = $builder->addDefinition($this->prefix($name . '.' . ($counter++)))
					->setFactory($class);

				// Merge and validace decorator config
				$decorator = $this->validateConfig($this->decorator, $resource['decorator'], $namespace);

				if ($decorator['tags'] !== []) {
					$def->setTags(Arrays::normalize($decorator['tags'], true));
				}

				if ($decorator['setup'] !== []) {
					foreach ($decorator['setup'] as $setup) {
						if (is_array($setup)) {
							$setup = new Statement(key($setup), array_values($setup));
						}
						$def->addSetup($setup);
					}
				}

				if ($decorator['autowired'] !== null) {
					$def->setAutowired($decorator['autowired']);
				}

				if ($decorator['inject'] !== null) {
					$def->setInject($decorator['inject']);
				}
			}
		}
	}

	/**
	 * Find classes by given arguments
	 *
	 * @param string[] $dirs
	 * @param string[] $excludes
	 * @return string[]
	 */
	protected function findClasses(string $namespace, array $dirs, array $excludes = []): array
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
			if (array_filter($excludes, function (string $exclude) use ($class): bool {
				return Strings::startsWith($class, $exclude);
			}) !== []) continue;

			// Skip not existing class
			if (!class_exists($class, true)) continue;

			// Detect by reflection
			$ct = new ReflectionClass($class);

			// Skip abstract
			if ($ct->isAbstract()) continue;

			// All tests passed, it's our class
			$classes[] = $class;
		}

		return $classes;
	}

	protected function createLoader(): RobotLoader
	{
		if (!class_exists(RobotLoader::class)) {
			throw new InvalidStateException('Install nette/robot-loader at first');
		}

		$robot = new RobotLoader();

		// From version >=3.0.0, there's no setCacheStorage method
		if (method_exists($robot, 'setCacheStorage')) {
			$robot->setCacheStorage(new DevNullStorage());
		}

		return $robot;
	}

}
