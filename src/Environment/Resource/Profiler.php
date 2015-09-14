<?php
class CMF_Hydrogen_Environment_Resource_Profiler{

	/**	@var	Alg_Time_Clock		$clock		Inner stopwatch with lap support */
	protected $clock	= NULL;
	protected $enabled	= TRUE;
	
	public function __construct( $enabled = TRUE ){
		$this->enabled	= (bool) $enabled;
		if( $this->enabled )
			$this->clock	= new Alg_Time_Clock();
	}
	
	public function tick( $message ){
		if( $this->enabled )
			$this->clock->stopLap( 0, 0, $message );
	}

	public function get(){
		if( $this->enabled )
			return $this->clock->getLaps();
		return array();
	}
}
?>