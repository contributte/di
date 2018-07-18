<?php declare(strict_types = 1);

namespace Contributte\DI\Config;

use Nette\Utils\Arrays;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;

class Node
{

	public const NOT_AVAILABLE = '\0\0\0\0\0';

	/** @var string */
	private $name;

	/** @var callable[] */
	private $validators = [];

	/** @var mixed */
	private $defaultValue;

	/** @var bool */
	private $nullable = false;

	/** @var bool */
	private $required = true;

	/** @var Node[] */
	private $children = [];

	/** @var Node[] */
	private $nested = [];

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * @param mixed $value
	 */
	public function setDefault($value): self
	{
		$this->defaultValue = $value;
		$this->required = false;

		return $this;
	}

	public function nullable(bool $nullable = true): self
	{
		$this->nullable = $nullable;

		return $this;
	}

	public function isString(): self
	{
		$this->validators['type'] = function ($value): void {
			Validators::assert($value, 'string', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	public function isArray(): self
	{
		$this->validators['type'] = function ($value): void {
			Validators::assert($value, 'array', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	public function isInt(): self
	{
		$this->validators['type'] = function ($value): void {
			Validators::assert($value, 'int', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	public function isFloat(): self
	{
		$this->validators['type'] = function ($value): void {
			Validators::assert($value, 'float', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	/**
	 * @param mixed[] $children
	 */
	public function children(array $children): self
	{
		$this->isArray();
		$this->children = $children;

		return $this;
	}

	/**
	 * @param mixed[] $nested
	 */
	public function nested(array $nested): self
	{
		$this->isArray();
		$this->nested = $nested;

		return $this;
	}

	/**
	 * @param mixed $value
	 */
	public function validate($value): void
	{
		// If given value is NA and default value is provided, then skip it
		if ($value === self::NOT_AVAILABLE && $this->defaultValue !== null) return;

		// If given value is NULL and nullable, then skip it
		if ($value === null && $this->nullable === true) return;

		// Otherwise, apply validators on given value
		foreach ($this->validators as $validator) {
			$validator($value);
		}

		if ($this->children !== []) {
			foreach ($this->children as $node) {
				foreach ($value as $val) {
					$v = Arrays::get($val, $node->getName(), self::NOT_AVAILABLE);
					$node->validate($v);
				}
			}
		}

		if ($this->nested !== []) {
			foreach ($this->nested as $node) {
				$v = Arrays::get($value, $node->getName(), self::NOT_AVAILABLE);
				$node->validate($v);
			}
		}
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function merge($value)
	{
		// If given value is NA and also node is not nullable and is required, throws an exception
		if ($value === self::NOT_AVAILABLE
			&& $this->required === true
			&& $this->nullable !== true) throw new AssertionException(sprintf('The variable "%s" is required, null given.', $this->getName()));

		if ($this->children !== []) {
			$result = [];
			foreach ($this->children as $node) {
				foreach ($value as $key => $val) {
					$v = Arrays::get($val, $node->getName(), self::NOT_AVAILABLE);
					$result[$key][$node->getName()] = $node->merge($v);
				}
			}

			return $result;
		}

		if ($this->nested !== []) {
			$result = [];
			foreach ($this->nested as $node) {
				$v = Arrays::get($value, $node->getName(), self::NOT_AVAILABLE);
				$result[$node->getName()] = $node->merge($v);
			}

			return $result;
		}

		// Return given value if value is not NA
		if ($value !== self::NOT_AVAILABLE) return $value;

		// Return default value or NULL
		return $this->defaultValue ?: null;
	}

	public static function create(string $name): self
	{
		return new static($name);
	}

}
