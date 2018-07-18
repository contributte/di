<?php declare(strict_types = 1);

namespace Contributte\DI;

use Nette\DI\Container;

trait TContainerAware
{

	/** @var Container */
	protected $container;

	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

}
