<?php
class CMF_Hydrogen_Application_Abstract{

	/**	@var		string								$classEnvironment		Class Name of Application Environment to build */
	public static $classEnvironment						= 'CMF_Hydrogen_Environment_Web';
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env					Application Environment Object */
	protected $env;
}
?>