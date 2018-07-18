<?php declare(strict_types = 1);

namespace Tests\Fixtures\Bar;

use stdClass;

final class BarService
{

	/** @var stdClass */
	private $logger;

	public function setLogger(stdClass $logger): void
	{
		$this->logger = $logger;
	}

	public function getLogger(): stdClass
	{
		return $this->logger;
	}

}
