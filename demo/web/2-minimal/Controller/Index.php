<?php
class Controller_Index extends CMF_Hydrogen_Controller
{
	public function index()
	{
		$this->addData( 'microtime', microtime( TRUE ) );
	}
}
