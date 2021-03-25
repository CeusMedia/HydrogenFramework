<?php
class CMF_Hydrogen_Environment_Resource_Module_Component_File
{
	public $file;

	public $load;

	public $level;

	public $source;

	public function __construct( $file )
	{
		$this->file		= $file;
	}
}
