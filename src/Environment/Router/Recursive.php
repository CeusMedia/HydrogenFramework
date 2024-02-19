<?php
/**
 *	...
 *
 *	Copyright (c) 2011-2024 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Router;

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Environment\RouterInterface;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

use RuntimeException;

use function ucfirst;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Recursive extends Abstraction implements RouterInterface
{
	/**
	 *	@return	void
	 */
	public function parseFromRequest()
	{
		if( !$this->env->has( 'request' ) )
			throw new RuntimeException( 'Routing needs a registered request resource' );

		$request	= $this->env->getRequest();

		if( FALSE !== getEnv( 'REDIRECT_URL' ) && $request->has( '__path' ) )
			self::$pathKey	= '__path';

		$path	= $request->get( self::$pathKey, '' );
		if( $this->env instanceof WebEnvironment )
			if( $request instanceof HttpRequest )
				$path	= $request->getFromSource( self::$pathKey, 'get' ) ?? '';

		$path	= urldecode( $path );
		$path	= preg_replace( '@^(.*)/?$@U', '\\1', trim( $path ) );
		$parts	= explode( '/', $path );
		$left	= $parts;
		$right	= [];
		if( strlen( trim( $path ) ) ){
			while( count( $left ) ){
				$class	= [];
				foreach( $left as $part )
					$class[]	= ucfirst( $part );
				$className	= 'Controller_'.implode( '_', $class );
				if( class_exists( $className ) ){
	//				remark( 'Controller Class: '.$className );
					$controller	= implode( '/', $left );
					$request->set( '__controller', $controller );
					if( 0 !== count( $right ) ){
						if( method_exists( $className, $right[0] ) ){
	//						remark( 'Controller Method: '.$right[0] );
							$request->set( '__action', array_shift( $right ) );
						}
						$request->set( '__arguments', $right );
	//					if( $right )
	//						remark( 'Arguments: '.implode( ', ', $right ) );
					}
					break;
				}
				array_unshift( $right, array_pop( $left ) );
			}
		}
		if( !$request->get( '__controller' ) )
			if( 0 !== count( $right ) )
				$request->set( '__arguments', $right );

/*		remark( "controller: ".$request->get( '__controller' ) );
		remark( "action: ".$request->get( '__action' ) );
		remark( "arguments: " );
		print_m( $request->get( '__arguments' ) );
		die;*/
	}
}
