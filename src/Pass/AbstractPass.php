<?php

namespace Contributte\DI\Pass;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

abstract class AbstractPass
{

	/** @var CompilerExtension */
	protected $extension;

	/**
	 * @param CompilerExtension $extension
	 */
	public function __construct(CompilerExtension $extension)
	{
		$this->extension = $extension;
	}

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadPassConfiguration()
	{
	}

	/**
	 * Decorate services
	 *
	 * @return void
	 */
	public function beforePassCompile()
	{
	}

	/**
	 * @param ClassType $class
	 * @return void
	 */
	public function afterPassCompile(ClassType $class)
	{
	}

}
