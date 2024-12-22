<?php
class Controller_Test extends CeusMedia\HydrogenFramework\Controller
{
	public function test(): void
	{
		$this->addData( 'test', time() );
	}
}