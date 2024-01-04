<?php declare(strict_types = 1);

namespace Tests\Fixtures\Bar;

use stdClass;

final class BarService
{

	private stdClass $logger;

	public function setLogger(stdClass $logger): void
	{
		$this->logger = $logger;
	}

	public function getLogger(): stdClass
	{
		return $this->logger;
	}

}
