<?php

namespace Contributte\DI\Extension;

use Nette\DI\Extensions\ExtensionsExtension;
use Nette\DI\Statement;
use Nette\InvalidStateException;

/**
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class NewExtensionsExtension extends ExtensionsExtension
{

	/**
	 * Register other extensions with pleasure.
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$extensions = [];

		// Collect all extensions
		foreach ($this->getConfig() as $name => $extension) {
			if (is_int($name)) {
				$name = NULL;
			}

			if (is_array($extension)) {
				$extension = array_merge([
					'priority' => 10,
				], $extension);

				if (!array_key_exists('class', $extension)) {
					throw new InvalidStateException(sprintf('Key "class" is required'));
				}

				$extensions[] = [
					'name' => $name,
					'class' => $extension['class'],
					'arguments' => [],
					'priority' => $extension['priority'],
				];
			} else if ($extension instanceof Statement) {
				$extensions[] = [
					'name' => $name,
					'class' => $extension->getEntity(),
					'arguments' => $extension->arguments,
					'priority' => $extension['priority'],
				];
			} else if (is_string($extension)) {
				$extensions[] = [
					'name' => $name,
					'class' => $extension,
					'arguments' => [],
					'priority' => 10,
				];
			} else {
				throw new InvalidStateException(
					sprintf(
						'Invalid extension definition "%s" given',
						is_scalar($extension) ? $extension : gettype($extension)
					)
				);
			}
		}

		// Sort all extensions
		usort($extensions, function ($a, $b) {
			if ($a['priority'] === $b['priority']) return 0;

			return $a['priority'] > $b['priority'] ? 1 : -1;
		});

		// Register all extensions
		foreach ($extensions as $extension) {
			$instance = new $extension['class'](...$extension['arguments']);
			$this->compiler->addExtension($extension['name'], $instance);
		}
	}

}
