<?php declare(strict_types = 1);

namespace Tests\Fixtures\Scalar;

final class Scalar2Service
{

	/** @var string */
	public $text;

	/**
	 * @param string $text
	 */
	public function __construct($text = 'default')
	{
		$this->text = $text;
	}

}
