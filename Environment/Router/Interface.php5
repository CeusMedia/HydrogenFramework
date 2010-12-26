<?php
interface CMF_Hydrogen_Environment_Router_Interface
{
	public function __construct( CMF_Hydrogen_Environment_Abstract $env );

	public function getAbsoluteUri( $controller = NULL, $action = NULL, $arguments = NULL, $parameters = NULL, $fragmentId = NULL );

	public function getRelativeUri( $controller = NULL, $action = NULL, $arguments = NULL, $parameters = NULL, $fragmentId = NULL );

	public function parseFromRequest();

	public function realizeInResponse();
}
?>
