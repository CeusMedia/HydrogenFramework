<?php


use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Logic\Capsuled as CapsuledLogic;

class Logic_Capsuled extends CapsuledLogic
{
	public string $uuid;

	protected function __onInit(): void
	{
		$this->uuid = ID::uuid();
	}
}
