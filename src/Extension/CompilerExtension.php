<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Contributte\DI\Helper\ExtensionDefinitionsHelper;
use Nette\DI\CompilerExtension as NCompilerExtension;

abstract class CompilerExtension extends NCompilerExtension
{

	/** @var ExtensionDefinitionsHelper|null */
	private $helper;

	protected function getHelper(): ExtensionDefinitionsHelper
	{
		if ($this->helper === null) {
			$this->helper = new ExtensionDefinitionsHelper($this->compiler);
		}

		return $this->helper;
	}

}
