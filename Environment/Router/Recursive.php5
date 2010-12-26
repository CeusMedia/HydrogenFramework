<?php
class CMF_Hydrogen_Environment_Router_Recursive
{
	public function parseFromRequest()
	{
		$request	= $this->env->getRequest();

		$path	= $request->getFromSource( 'path', 'get' );
		$path	= preg_replace( '@^(.*)/?$@U', '\\1', trim( $path ) );
		$parts	= explode( '/', $path );
		$right	= array();
		$left	= $parts;
		while( count( $left ) )
		{
			$class	= array();
			foreach( $left as $part )
				$class[]	= ucfirst( $part );
			$className	= 'Controller_'.implode( '_', $class );
			if( class_exists( $className ) )
			{
//				remark( 'Controller Class: '.$className );
				$controller	= implode( '/', $left );
				$request->set( 'controller', $controller );
				if( count( $right ) )
				{
					if( method_exists( $className, $right[0] ) )
					{
//						remark( 'Controller Method: '.$right[0] );
						$request->set( 'action', array_shift( $right ) );
					}
					$request->set( 'arguments', $right );
//					if( $right )
//						remark( 'Arguments: '.implode( ', ', $right ) );
				}
				break;
			}
			array_unshift( $right, array_pop( $left ) );
		}
		remark( 'Router::Level X' );
		remark( "controller: ".$this->request->get( 'controller' ) );
		remark( "action: ".$this->request->get( 'action' ) );
		die;
	}
}

?>
