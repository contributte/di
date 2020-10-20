<?php declare(strict_types = 1);

namespace Tests\Fixtures\Inject;

abstract class Base
{

	/** @var mixed[] */
	public $setup = [];

	public function setup(Tester $tester, string $bar): void
	{
		$this->setup = func_get_args();
	}

}
