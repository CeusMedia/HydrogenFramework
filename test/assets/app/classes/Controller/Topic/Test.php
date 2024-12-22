<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Topic_Test extends Controller
{
	public function test(): void
	{
		$this->addData( 'topic', 'Test' );
	}
}