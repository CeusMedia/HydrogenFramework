<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\CLI\ArgumentParser as ArgumentParser;

trait RequestByCli
{
	/**	@var	ArgumentParser			$request	Console Request Object */
	protected ArgumentParser $request;

	public function getRequest(): ArgumentParser|Dictionary
	{
		return $this->request ?? new Dictionary();
	}

	protected function initRequest(): static
	{
		$this->request	= new ArgumentParser();
		$this->request->parseArguments();
		return $this;
	}
}
