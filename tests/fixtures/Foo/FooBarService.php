<?php

namespace Tests\Fixtures\Foo;

final class FooBarService
{

	/** @var FooService */
	public $foo;

	/**
	 * @param FooService $foo
	 */
	public function __construct(FooService $foo)
	{
		$this->foo = $foo;
	}

}
