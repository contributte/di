<?php

namespace Contributte\DI;

use Nette\DI\Container;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
trait TContainerAware
{

	/** @var Container */
	protected $container;

	/**
	 * @param Container $container
	 * @return void
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

}
