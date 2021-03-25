<?php
class CMF_Hydrogen_Environment_Resource_Module_Component_Config
{
	public $key;

	public $value;

	public $type;

	public $values;

	public $mandatory;

	public $protected;

	public $title;

	public function __construct( $key, $value, $type = NULL, $title = NULL )
	{
		$this->key		= $key;
		$this->value	= $value;
		$this->type		= $type;
		$this->title	= $title;
	}
}
