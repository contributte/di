<?php

namespace Contributte\DI\Config;

use Nette\Utils\Arrays;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class Node
{

	const NOT_AVAILABLE = '\0\0\0\0\0';

	/** @var string */
	private $name;

	/** @var callable[] */
	private $validators = [];

	/** @var mixed */
	private $defaultValue;

	/** @var bool */
	private $nullable = FALSE;

	/** @var bool */
	private $required = TRUE;

	/** @var Node[] */
	private $children = [];

	/** @var Node[] */
	private $nested = [];

	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * GETTERS *****************************************************************
	 */

	/**
	 * @return string
	 */
	public function getName()
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
	 * FLUENT SETTERS **********************************************************
	 */

	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setDefault($value)
	{
		$this->defaultValue = $value;
		$this->required = FALSE;

		return $this;
	}

	/**
	 * @param bool $nullable
	 * @return static
	 */
	public function nullable($nullable = TRUE)
	{
		$this->nullable = $nullable;

		return $this;
	}

	/**
	 * VALIDATORS **************************************************************
	 */

	/**
	 * @return static
	 */
	public function isString()
	{
		$this->validators['type'] = function ($value) {
			Validators::assert($value, 'string', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	/**
	 * @return static
	 */
	public function isArray()
	{
		$this->validators['type'] = function ($value) {
			Validators::assert($value, 'array', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	/**
	 * @return static
	 */
	public function isInt()
	{
		$this->validators['type'] = function ($value) {
			Validators::assert($value, 'int', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	/**
	 * @return static
	 */
	public function isFloat()
	{
		$this->validators['type'] = function ($value) {
			Validators::assert($value, 'float', sprintf('variable "%s"', $this->getName()));
		};

		return $this;
	}

	/**
	 * @param array $children
	 * @return static
	 */
	public function children(array $children)
	{
		$this->isArray();
		$this->children = $children;

		return $this;
	}

	/**
	 * @param array $nested
	 * @return static
	 */
	public function nested(array $nested)
	{
		$this->isArray();
		$this->nested = $nested;

		return $this;
	}

	/**
	 * VALIDATION **************************************************************
	 */

	/**
	 * @param mixed $value
	 * @return void
	 */
	public function validate($value)
	{
		// If given value is NA and default value is provided, then skip it
		if ($value === self::NOT_AVAILABLE && $this->defaultValue !== NULL) return;

		// If given value is NULL and nullable, then skip it
		if ($value === NULL && $this->nullable === TRUE) return;

		// Otherwise, apply validators on given value
		foreach ($this->validators as $validator) {
			$validator($value);
		}

		if ($this->children) {
			foreach ($this->children as $node) {
				foreach ($value as $val) {
					$v = Arrays::get($val, $node->getName(), self::NOT_AVAILABLE);
					$node->validate($v);
				}
			}
		}

		if ($this->nested) {
			foreach ($this->nested as $node) {
				$v = Arrays::get($value, $node->getName(), self::NOT_AVAILABLE);
				$node->validate($v);
			}
		}
	}

	/**
	 * MERGING *****************************************************************
	 */

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function merge($value)
	{
		// If given value is NA and also node is not nullable and is required, throws an exception
		if ($value === self::NOT_AVAILABLE
			&& $this->required === TRUE
			&& $this->nullable !== TRUE) throw new AssertionException(sprintf('The variable "%s" is required, null given.', $this->getName()));

		if ($this->children) {
			$result = [];
			foreach ($this->children as $node) {
				foreach ($value as $key => $val) {
					$v = Arrays::get($val, $node->getName(), self::NOT_AVAILABLE);
					$result[$key][$node->getName()] = $node->merge($v);
				}
			}

			return $result;
		}

		if ($this->nested) {
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
		return $this->defaultValue ?: NULL;
	}

	/**
	 * FACTORY *****************************************************************
	 */

	/**
	 * @param string $name
	 * @return static
	 */
	public static function create($name)
	{
		return new static($name);
	}

}
