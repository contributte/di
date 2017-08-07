<?php

namespace Contributte\DI;

use Nette\DI\Container;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
interface IContainerAware
{

	/**
	 * @param Container $container
	 * @return void
	 */
	public function setContainer(Container $container);

}
