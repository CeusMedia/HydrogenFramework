<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;

trait RequestByFake
{
	/**	@var	Dictionary					$request		Request Object */
	private Dictionary $request;

	/**
	 *	Returns Request Object.
	 *	@access		public
	 *	@return		Dictionary
	 */
	public function getRequest(): Dictionary
	{
		return $this->request ?? new Dictionary();
	}

	/**
	 * @return static
	 */
	protected function initRequest(): static
	{
		$this->request	= new Dictionary();
		return $this;
	}
}
