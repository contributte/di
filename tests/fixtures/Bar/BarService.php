<?php

namespace Tests\Fixtures\Bar;

use stdClass;

final class BarService
{

	/** @var stdClass */
	private $logger;

	/**
	 * @param stdClass $logger
	 * @return void
	 */
	public function setLogger(stdClass $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return stdClass
	 */
	public function getLogger()
	{
		return $this->logger;
	}

}
