<?php declare(strict_types = 1);

namespace Tests\Fixtures\Scalar;

final class ScalarService
{

	public string $text;

	public function __construct(string $text)
	{
		$this->text = $text;
	}

}
