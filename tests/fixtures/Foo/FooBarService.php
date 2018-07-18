<?php declare(strict_types = 1);

namespace Tests\Fixtures\Foo;

final class FooBarService
{

	/** @var FooService */
	public $foo;

	public function __construct(FooService $foo)
	{
		$this->foo = $foo;
	}

}
