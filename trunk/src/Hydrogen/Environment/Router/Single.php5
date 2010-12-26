<?php
class CMF_Hydrogen_Environment_Router_Single extends CMF_Hydrogen_Environment_Router_Abstract implements CMF_Hydrogen_Environment_Router_Interface
{
	protected $isSetUp	= FALSE;

	public function parseFromRequest()
	{
		if( !$this->env->request )
			throw new RuntimeException( 'Routing needs a registered request resource' );
		$request	= $this->env->getRequest();
		$path		= $request->getFromSource( 'path', 'get' );
		if( !trim( $path ) )
			return;

		$parts	= explode( '/', $path );
		$request->set( 'controller',	array_shift( $parts ) );
		$request->set( 'action',		array_shift( $parts ) );
		$arguments	= array();
		while( count( $parts ) )
		{
			$part = trim( array_shift( $parts ) );
			if( strlen( $part ) )
				$arguments[]	= $part;
		}
		$request->set( 'arguments', $arguments );
	}
}
?>