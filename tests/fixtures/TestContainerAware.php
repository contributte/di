<?php

namespace Tests\Fixtures;

use Contributte\DI\IContainerAware;
use Contributte\DI\TContainerAware;
use Nette\DI\Container;

final class TestContainerAware implements IContainerAware
{

	use TContainerAware;

	/**
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

}
