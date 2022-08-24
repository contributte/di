<?php declare(strict_types = 1);

namespace Tests\Fixtures\Decorator;

class InjectService
{

	/** @var Authenticator */
	public $authenticator;

	public function injectAuthenticator(Authenticator $authenticator): void
	{
		$this->authenticator = $authenticator;
	}

}
