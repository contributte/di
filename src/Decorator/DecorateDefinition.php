<?php declare(strict_types = 1);

namespace Contributte\DI\Decorator;

use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Utils\Arrays;

final class DecorateDefinition
{

	/** @var ServiceDefinition[] */
	private $definitions;

	/**
	 * @param ServiceDefinition[] $definitions
	 */
	public function __construct(array $definitions)
	{
		$this->definitions = $definitions;
	}

	/**
	 * @param string|mixed[]|Definition|Reference|Statement $entity
	 * @param mixed[] $args
	 */
	public function addSetup($entity, array $args = []): self
	{
		foreach ($this->definitions as $definition) {
			$definition->addSetup($entity, $args);
		}

		return $this;
	}

	/**
	 * @param string[] $tags
	 */
	public function addTags(array $tags): self
	{
		$tags = Arrays::normalize($tags, true);
		foreach ($this->definitions as $definition) {
			$definition->setTags($definition->getTags() + $tags);
		}

		return $this;
	}

}
