<?php declare(strict_types = 1);

namespace Contributte\DI\Pass;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

abstract class AbstractPass
{

	/** @var CompilerExtension */
	protected $extension;

	public function __construct(CompilerExtension $extension)
	{
		$this->extension = $extension;
	}

	/**
	 * Register services
	 */
	public function loadPassConfiguration(): void
	{
	}

	/**
	 * Decorate services
	 */
	public function beforePassCompile(): void
	{
	}

	public function afterPassCompile(ClassType $class): void
	{
	}

}
