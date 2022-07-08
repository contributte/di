<?php declare(strict_types = 1);

namespace Tests\Fixtures\Scalar;

final class ScalarService
{

	/** @var string */
	public $text;

	/**
	 * @param string $text
	 */
	public function __construct($text)
	{
		$this->text = $text;
	}

}
