<?php declare(strict_types = 1);

namespace Contributte\DI\Exception\Logical;

use Contributte\DI\Exception\LogicalException;

final class ClassNotExistsException extends LogicalException
{

	public function __construct(string $type)
	{
		parent::__construct('Class ' . $type . ' does not exist');
	}

}
