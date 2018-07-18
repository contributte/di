<?php declare(strict_types = 1);

namespace Contributte\DI\Extension;

use Nette\DI\Extensions\ExtensionsExtension;
use Nette\DI\Statement;
use Nette\InvalidStateException;

class NewExtensionsExtension extends ExtensionsExtension
{

	/**
	 * Register other extensions with pleasure.
	 */
	public function loadConfiguration(): void
	{
		$extensions = [];

		// Collect all extensions
		foreach ($this->getConfig() as $name => $extension) {
			if (is_int($name)) {
				$name = null;
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
			} elseif ($extension instanceof Statement) {
				$extensions[] = [
					'name' => $name,
					'class' => $extension->getEntity(),
					'arguments' => $extension->arguments,
					'priority' => $extension['priority'],
				];
			} elseif (is_string($extension)) {
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
