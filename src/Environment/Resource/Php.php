<?php
class CMF_Hydrogen_Environment_Resource_Php{

	public $version;

	public function __construct(){
		$this->version	= new CMF_Hydrogen_Environment_Resource_Php_Version();
	}

	public function getCurrentVersion(){
		return $this->version->get();
	}
}
