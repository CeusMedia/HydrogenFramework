<?php
abstract class CMF_Hydrogen_Environment_Resource_Module_Source_Abstract{
	protected $env;
	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
	}
	public function index();
}
?>
