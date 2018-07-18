<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\SmartObject;

final class MutableExtension extends CompilerExtension
{

	use SmartObject;

	/** @var callable[] function (MutableExtension $extension, \Nette\DI\ContainerBuilder $builder, array $config); */
	public $onLoad = [];

	/** @var callable[] function (MutableExtension $extension, \Nette\DI\ContainerBuilder $builder, array $config); */
	public $onBefore = [];

	/** @var callable[] function (MutableExtension $extension, ClassType $class, array $config); */
	public $onAfter = [];

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$this->onLoad($this, $this->getContainerBuilder(), $this->getConfig());
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		$this->onBefore($this, $this->getContainerBuilder(), $this->getConfig());
	}

	public function afterCompile(ClassType $class): void
	{
		$this->onAfter($this, $class, $this->getConfig());
	}

}
