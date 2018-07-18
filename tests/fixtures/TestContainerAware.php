<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Contributte\DI\IContainerAware;
use Contributte\DI\TContainerAware;
use Nette\DI\Container;

final class TestContainerAware implements IContainerAware
{

	use TContainerAware;

	public function getContainer(): Container
	{
		return $this->container;
	}

}
