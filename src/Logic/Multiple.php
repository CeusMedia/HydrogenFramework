<?php
/**
 *	Abstract logic class for multipe instances of one logic class.
 *	Use this class if multiple instances of one logic class for one environment is okay.
 *	But in most cases, eg. if a logic class is using resources, a contextual logic will be better.
 *
 *	@abstract
 */
abstract class CMF_Hydrogen_Logic_Multiple{

	protected $env;

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
		$this->__onInit();
	}

	public function __onInit(){
	}
}
?>
