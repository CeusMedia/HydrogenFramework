<?php
interface CMF_Hydrogen_View_Helper
{
	public function hasEnv();
	public function needsEnv();
	public function setEnv( CMF_Hydrogen_Environment $env );
}
?>
