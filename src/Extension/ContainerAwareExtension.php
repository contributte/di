<?php

namespace Contributte\DI\Extension;

use Contributte\DI\IContainerAware;
use Nette\DI\CompilerExtension;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ContainerAwareExtension extends CompilerExtension
{

	/**
	 * Tweak DI container
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Register as services
		foreach ($builder->findByType(IContainerAware::class) as $service) {
			$service->addSetup('setContainer');
		}
	}

}
