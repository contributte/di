<?php declare(strict_types = 1);

namespace Contributte\DI\Decorator;

use Contributte\DI\Exception\Logical\ClassNotExistsException;
use Contributte\DI\Extension\ExtensionDefinitionsHelper;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;

final class Decorator
{

	/** @var ContainerBuilder */
	private $builder;

	/** @var ExtensionDefinitionsHelper */
	private $definitionsHelper;

	private function __construct(ContainerBuilder $builder)
	{
		$this->builder = $builder;
		$this->definitionsHelper = new ExtensionDefinitionsHelper($builder);
	}

	public static function of(ContainerBuilder $builder): self
	{
		return new self($builder);
	}

	public function decorate(string $type): DecorateDefinition
	{
		if (!class_exists($type)) {
			throw new ClassNotExistsException($type);
		}

		return new DecorateDefinition($this->findByType($type));
	}

	/**
	 * @return ServiceDefinition[]
	 */
	private function findByType(string $type): array
	{
		$definitions = $this->definitionsHelper->getServiceDefinitionsFromDefinitions($this->builder->getDefinitions());
		return array_filter($definitions, static function (ServiceDefinition $def) use ($type): bool {
			return $def->getType() !== null && is_a($def->getType(), $type, true);
		});
	}

}
