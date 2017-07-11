<?php
/**
 *	Abstract logic class for singletons.
 *	Attention: Should not be used anymore since only one environment can be used.
 *
 *	@abstract
 *	@deprecated		use CMF_Hydogen_Logic_Contextual instead
 */
abstract class CMF_Hydrogen_Logic_Singleton{

	protected $env;

	static protected $instance;

	protected function __construct( $env ){
		$this->env	= $env;
		$this->__onInit();
	}

	protected function __clone(){}

	static public function getInstance( CMF_Hydrogen_Environment $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	protected function __onInit(){}
}
?>
