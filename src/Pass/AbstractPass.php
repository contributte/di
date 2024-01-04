<?php declare(strict_types = 1);

namespace Contributte\DI\Pass;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

abstract class AbstractPass
{

	protected CompilerExtension $extension;

	public function __construct(CompilerExtension $extension)
	{
		$this->extension = $extension;
	}

	/**
	 * Register services
	 */
	public function loadPassConfiguration(): void
	{
		// No-op
	}

	/**
	 * Decorate services
	 */
	public function beforePassCompile(): void
	{
		// No-op
	}

	public function afterPassCompile(ClassType $class): void
	{
		// No-op
	}

}
