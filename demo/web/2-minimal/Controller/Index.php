<?php
use CeusMedia\HydrogenFramework\Controller;

class Controller_Index extends Controller
{
	public function index()
	{
		$this->addData( 'microtime', microtime( TRUE ) );
	}
}
