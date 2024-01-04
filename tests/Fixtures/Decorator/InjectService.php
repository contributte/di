<?php declare(strict_types = 1);

namespace Tests\Fixtures\Decorator;

class InjectService
{

	public Authenticator $authenticator;

	public function injectAuthenticator(Authenticator $authenticator): void
	{
		$this->authenticator = $authenticator;
	}

}
