<?php declare(strict_types = 1);

namespace Contributte\DI\Decorator;

use Contributte\DI\Exception\Logical\ClassNotExistsException;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class Decorator
{

	/** @var ContainerBuilder */
	private $builder;

	private function __construct(ContainerBuilder $builder)
	{
		$this->builder = $builder;
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
		return array_filter($this->builder->getDefinitions(), function (ServiceDefinition $def) use ($type): bool {
			return ($def->getImplement() !== null && is_a($def->getImplement(), $type, true))
					|| ($def->getImplementMode() !== $def::IMPLEMENT_MODE_GET && $def->getType() !== null && is_a($def->getType(), $type, true));
		});
	}

}
