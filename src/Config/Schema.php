<?php

namespace Contributte\DI\Config;

use Nette\InvalidStateException;
use Nette\Utils\Arrays;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class Schema
{

	/** @var Node[] */
	protected $nodes = [];

	/**
	 * @param Node $node
	 * @return static
	 */
	public function add(Node $node)
	{
		$this->nodes[$node->getName()] = $node;

		return $this;
	}

	/**
	 * @param array $values
	 * @return void
	 */
	public function validate(array $values)
	{
		if ($extra = array_diff_key((array) $values, $this->nodes)) {
			$extra = implode(', ', array_keys($extra));
			throw new InvalidStateException('Unknown configuration option ' . $extra);
		}

		foreach ($this->nodes as $name => $node) {
			$value = Arrays::get($values, $name, Node::NOT_AVAILABLE);
			$node->validate($value);
		}
	}

	/**
	 * @param array $values
	 * @return array
	 */
	public function merge(array $values)
	{
		$config = $values;

		foreach ($this->nodes as $name => $node) {
			$value = Arrays::get($values, $name, Node::NOT_AVAILABLE);
			$config[$name] = $node->merge($value);
		}

		return $config;
	}

	/**
	 * @param array $values
	 * @return array
	 */
	public function process(array $values)
	{
		$this->validate($values);

		return $this->merge($values);
	}

	/**
	 * FACTORY *****************************************************************
	 */

	/**
	 * @return Schema
	 */
	public static function root()
	{
		return new Schema();
	}

}
