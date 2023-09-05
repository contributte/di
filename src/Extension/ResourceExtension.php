<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\DI\Extensions\InjectExtension;
use Nette\InvalidStateException;
use Nette\Loaders\RobotLoader;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use ReflectionClass;
use RuntimeException;
use stdClass;

/**
 * @property-read stdClass $config
 */
class ResourceExtension extends CompilerExtension
{

	/** @var array<int, array{namespace: string, resource: stdClass, classes: string[]}> */
	private $map = [];

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'resources' => Expect::arrayOf(
				Expect::structure([
					'paths' => Expect::arrayOf('string'),
					'excludes' => Expect::arrayOf('string'),
					'decorator' => Expect::structure([
						'tags' => Expect::array(),
						'setup' => Expect::listOf('callable|Nette\DI\Definitions\Statement|array:1'),
						'autowired' => Expect::type('bool|string|array')->nullable(),
						'inject' => Expect::bool()->nullable(),
					]),
				])
			),
		]);
	}

	public function loadConfiguration(): void
	{
		$config = $this->config;

		foreach ($config->resources as $namespace => $resource) {
			if (substr($namespace, -1) !== '\\') {
				throw new RuntimeException(sprintf('Resource "%s" must end with /', $namespace));
			}

			// Find classes of given resource
			$classes = $this->findClasses($namespace, $resource->paths, $resource->excludes);

			// Store found classes
			$this->map[] = [
				'namespace' => $namespace,
				'resource' => $resource,
				'classes' => $classes,
			];
		}
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		foreach ($this->map as $config) {
			$classes = $config['classes'];
			sort($classes);
			$resource = $config['resource'];
			$namespace = $config['namespace'];

			// Register services of given resource
			$counter = 1;
			$name = preg_replace('#\W+#', '_', '.' . $namespace);
			foreach ($classes as $class) {
				// Check already registered classes
				if ($builder->getByType($class) !== null) {
					continue;
				}

				$def = $builder->addDefinition($this->prefix($name . '.' . $counter++))
					->setFactory($class)
					->setType($class);

				$decorator = $resource->decorator;

				if ($decorator->tags !== []) {
					$def->setTags(Arrays::normalize($decorator->tags, true));
				}

				if ($decorator->setup !== []) {
					foreach ($decorator->setup as $setup) {
						if (is_array($setup)) {
							$key = key($setup);
							$key = is_int($key) ? (string) $key : $key;
							$setup = new Statement($key, array_values($setup));
						}

						$def->addSetup($setup);
					}
				}

				if ($decorator->autowired !== null) {
					$def->setAutowired($decorator->autowired);
				}

				if ($decorator->inject !== null) {
					$def->addTag(InjectExtension::TagInject, $decorator->inject);
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
		$loader = $this->createRobotLoader();
		$loader->addDirectory(...$dirs);
		$loader->rebuild();

		$indexed = $loader->getIndexedClasses();
		$classes = [];
		foreach ($indexed as $class => $file) {
			// Different namespace
			if (!Strings::startsWith($class, $namespace)) {
				continue;
			}

			// Excluded namespace
			if (array_filter($excludes, static function (string $exclude) use ($class): bool {
					return Strings::startsWith($class, $exclude);
				}) !== []) {
				continue;
			}

			// Skip not existing class
			if (!class_exists($class)) {
				continue;
			}

			// Detect by reflection
			$ct = new ReflectionClass($class);

			// Skip abstract
			if ($ct->isAbstract()) {
				continue;
			}

			// All tests passed, it's our class
			$classes[] = $class;
		}

		return $classes;
	}

	protected function createRobotLoader(): RobotLoader
	{
		if (!class_exists(RobotLoader::class)) {
			throw new InvalidStateException('Install nette/robot-loader at first');
		}

		return new RobotLoader();
	}

}
