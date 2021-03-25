<?php
class View_Index extends CMF_Hydrogen_View
{
	public function index()
	{
		extract( $this->getData() );
		return 'View says: Hello World! The microtime is: '.$microtime;
	}
}
