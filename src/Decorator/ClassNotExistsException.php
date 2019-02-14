<?php declare(strict_types = 1);

namespace Contributte\DI\Decorator;

use RuntimeException;

/**
 * @internal
 */
final class ClassNotExistsException extends RuntimeException
{

	public function __construct(string $type)
	{
		parent::__construct('Class ' . $type . ' not exists.');
	}

}
