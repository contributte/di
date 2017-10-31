<?php

namespace Contributte\DI\Extension;

use Contributte\DI\Pass\AbstractPass;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

abstract class PassCompilerExtension extends CompilerExtension
{

	/** @var AbstractPass[] */
	protected $passes = [];

	/**
	 * @param AbstractPass $pass
	 * @return self
	 */
	protected function addPass(AbstractPass $pass)
	{
		$this->passes[] = $pass;

		return $this;
	}

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		// Trigger passes
		foreach ($this->passes as $pass) {
			$pass->loadPassConfiguration();
		}
	}

	/**
	 * Decorate services
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		// Trigger passes
		foreach ($this->passes as $pass) {
			$pass->beforePassCompile();
		}
	}

	/**
	 * @param ClassType $class
	 * @return void
	 */
	public function afterCompile(ClassType $class)
	{
		// Trigger passes
		foreach ($this->passes as $pass) {
			$pass->afterPassCompile($class);
		}
	}

}
