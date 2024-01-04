<?php declare(strict_types = 1);

namespace Tests\Fixtures\Foo;

final class FooBarService
{

	public FooService $foo;

	public function __construct(FooService $foo)
	{
		$this->foo = $foo;
	}

}
