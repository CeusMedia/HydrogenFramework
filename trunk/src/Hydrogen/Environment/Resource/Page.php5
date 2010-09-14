<?php
class Framework_Hydrogen_Environment_Resource_Page extends UI_HTML_PageFrame
{
	public function __construct( Framework_Hydrogen_Environment_Abstract $env )
	{
		parent::__construct();
		$this->env	= $env;
		$this->js	= Framework_Hydrogen_View_Helper_JavaScript::getInstance();
	}
}
?>
