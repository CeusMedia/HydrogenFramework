<?php
/**
 *	Abstract logic class for contextual singletons.
 *	Every environment can have one instance.
 *	Please use this class instead of deprecated logic singleton.
 *
 *	@abstract
 */
abstract class CMF_Hydrogen_Logic_Contextual{

	public function __construct( CMF_Hydrogen_Environment $env ){
		$class	= get_class( self );
		if( !$env->logic->has( $class ){
			if( !is_a( self, Logic_Contextual ) )
				throw new DomainException( 'Logic class "'.get_class( self ).'" is contextual and cannot be added to logic pool.' );
			$this->env	= $env;
			$this->__onInit();
			$env->logic->add( $class, $this );
		}
		return $env->logic->get( $class );

		return self::getInstance( CMF_Hydrogen_Environment $env );
	}

	protected function __clone(){}

	static public function getInstance( CMF_Hydrogen_Environment $env ){
	}
}
?>
