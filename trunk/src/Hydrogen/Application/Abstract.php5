<?php
abstract class CMF_Hydrogen_Application_Abstract{

	/**	@var		string								$classEnvironment		Class Name of Application Environment to build */
	public static $classEnvironment						= 'CMF_Hydrogen_Environment_Web';
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env					Application Environment Object */
	protected $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env					Framework Environment
	 *	@return		void
	 */
	public function __construct( $env = NULL )
	{
		if( !$env )
			$env		= Alg_Object_Factory::createObject( self::$classEnvironment );
		else if( is_string( $env ) )
			$env		= Alg_Object_Factory::createObject( $env );
		$this->env		= $env;
	}

	abstract public function run();
}
?>