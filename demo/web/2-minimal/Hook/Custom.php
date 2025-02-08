<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Custom extends Hook
{
	public function random(): void
	{
		$this->payload['number']	= rand( 1, 10 );
	}

}