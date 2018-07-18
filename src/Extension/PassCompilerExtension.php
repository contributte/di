<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Contributte\DI\Pass\AbstractPass;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

abstract class PassCompilerExtension extends CompilerExtension
{

	/** @var AbstractPass[] */
	protected $passes = [];

	protected function addPass(AbstractPass $pass): self
	{
		$this->passes[] = $pass;

		return $this;
	}

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		// Trigger passes
		foreach ($this->passes as $pass) {
			$pass->loadPassConfiguration();
		}
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		// Trigger passes
		foreach ($this->passes as $pass) {
			$pass->beforePassCompile();
		}
	}

	public function afterCompile(ClassType $class): void
	{
		// Trigger passes
		foreach ($this->passes as $pass) {
			$pass->afterPassCompile($class);
		}
	}

}
