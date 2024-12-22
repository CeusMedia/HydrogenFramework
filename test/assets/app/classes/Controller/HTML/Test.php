<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_HTML_Test extends Controller
{
	public function test(): void
	{
		$this->addData( 'topic', 'HTML' );
	}
}