<?php
/**
 *	...
 *
 *	Copyright (c) 2011-2022 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Router;

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Environment\RouterInterface;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

use RuntimeException;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2022 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Single extends Abstraction implements RouterInterface
{
	protected $isSetUp	= FALSE;

	public function parseFromRequest()
	{
		if( !$this->env->has( 'request' ) )
			throw new RuntimeException( 'Routing needs a registered request resource' );
		$request	= $this->env->getRequest();

		if( $request->has( '__path' ) )
			self::$pathKey	= '__path';

		$path	= $request->get( self::$pathKey );
		if( $this->env instanceof WebEnvironment )
			if( $request instanceof HttpRequest )
				$path		= $request->getFromSource( self::$pathKey, 'get' );
		if( !trim( $path ) )
			return;

		$parts	= explode( '/', $path );
		$request->set( '__controller',	array_shift( $parts ) );
		$request->set( '__action',		array_shift( $parts ) );
		$arguments	= [];
		while( count( $parts ) ){
			$part = trim( array_shift( $parts ) );
			if( strlen( $part ) )
				$arguments[]	= $part;
		}
		$request->set( '__arguments', $arguments );
	}
}
