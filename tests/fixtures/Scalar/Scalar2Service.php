<?php declare(strict_types = 1);

namespace Tests\Fixtures\Scalar;

final class Scalar2Service
{

	public string $text;

	public function __construct(string $text = 'default')
	{
		$this->text = $text;
	}

}
