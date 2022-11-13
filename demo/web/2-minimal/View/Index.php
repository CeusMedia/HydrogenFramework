<?php
use CeusMedia\HydrogenFramework\View;

class View_Index extends View
{
	public function index()
	{
		extract( $this->getData() );
		return 'View says: Hello World! The microtime is: '.$microtime;
	}
}
