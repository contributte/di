<?php declare(strict_types = 1);

namespace Tests\Fixtures\Inject;

abstract class Base
{

	/** @var mixed[] */
	public array $setup = [];

	public function setup(Tester $tester, string $bar): void
	{
		$this->setup = func_get_args();
	}

}
