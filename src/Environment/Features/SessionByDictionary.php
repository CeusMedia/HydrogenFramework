<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\ADT\Collection\Dictionary;

trait SessionByDictionary
{
	/**	@var	Dictionary					$session		Session Object */
	protected Dictionary $session;

	/**
	 *	@return		Dictionary
	 */
	public function getSession(): Dictionary
	{
		return $this->session;
	}

	protected function initSession(): static
	{
		$this->session	= new Dictionary();
		return $this;
	}

}
