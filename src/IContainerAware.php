<?php declare(strict_types = 1);

namespace Contributte\DI;

use Nette\DI\Container;

interface IContainerAware
{

	public function setContainer(Container $container): void;

}
