<?php

namespace Contributte\DI\Extension;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\SmartObject;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
final class MutableExtension extends CompilerExtension
{

	use SmartObject;

	/** @var array */
	public $onLoad = [];

	/** @var array */
	public $onBefore = [];

	/** @var array */
	public $onAfter = [];

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$this->onLoad($this, $this->getContainerBuilder(), $this->getConfig());
	}

	/**
	 * Decorate services
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		$this->onBefore($this, $this->getContainerBuilder(), $this->getConfig());
	}

	/**
	 * @param ClassType $class
	 * @return void
	 */
	public function afterCompile(ClassType $class)
	{
		$this->onAfter($this, $class, $this->getConfig());
	}

}
