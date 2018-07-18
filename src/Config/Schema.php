<?php declare(strict_types = 1);

namespace Contributte\DI\Config;

use Nette\InvalidStateException;
use Nette\Utils\Arrays;

class Schema
{

	/** @var Node[] */
	protected $nodes = [];

	public function add(Node $node): self
	{
		$this->nodes[$node->getName()] = $node;

		return $this;
	}

	/**
	 * @param mixed[] $values
	 */
	public function validate(array $values): void
	{
		$extra = array_diff_key($values, $this->nodes);
		if ($extra !== []) {
			$extra = implode(', ', array_keys($extra));
			throw new InvalidStateException('Unknown configuration option ' . $extra);
		}

		foreach ($this->nodes as $name => $node) {
			$value = Arrays::get($values, $name, Node::NOT_AVAILABLE);
			$node->validate($value);
		}
	}

	/**
	 * @param mixed[] $values
	 * @return mixed[]
	 */
	public function merge(array $values): array
	{
		$config = $values;

		foreach ($this->nodes as $name => $node) {
			$value = Arrays::get($values, $name, Node::NOT_AVAILABLE);
			$config[$name] = $node->merge($value);
		}

		return $config;
	}

	/**
	 * @param mixed[] $values
	 * @return mixed[]
	 */
	public function process(array $values): array
	{
		$this->validate($values);

		return $this->merge($values);
	}

	public static function root(): Schema
	{
		return new Schema();
	}

}
