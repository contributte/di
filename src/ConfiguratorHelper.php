<?php

namespace Contributte\DI;

use Contributte\DI\Extension\NewExtensionsExtension;
use Nette\Configurator;

final class ConfiguratorHelper
{

	/**
	 * Upgrade default Nette\Configurator
	 * ----------------------------------
	 *
	 * 1. Replace <extensions> compiler extension to our new extension.
	 * 2. More coming.
	 *
	 * @param Configurator $configurator
	 * @return void
	 */
	public static function upgrade(Configurator $configurator)
	{
		$configurator->defaultExtensions['extensions'] = NewExtensionsExtension::class;
	}

}
