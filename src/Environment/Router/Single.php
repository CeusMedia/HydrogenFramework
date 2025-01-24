<?php
/**
 *	...
 *
 *	Copyright (c) 2011-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2011-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
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
 *	@copyright		2011-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Single extends Abstraction implements RouterInterface
{
	/**
	 *	@return	void
	 */
	public function parseFromRequest(): void
	{
		[$request, $path]	= $this->getPathFromRequest();
		if( '' === $path )
			return;

		$facts	= self::parseFromPath( urldecode( $path ) );
		foreach( ['controller', 'action', 'arguments'] as $key )
			$request->set( '__'.$key, $facts->$key );
	}

	/**
	 *	@param		string		$path
	 *	@return		object{controller: string, action: string, arguments: array, counter: int}
	 */
	protected function parseFromPath( string $path ): object
	{
		$facts	= (object) [
			'controller'	=> '',
			'action'		=> '',
			'arguments'		=> [],
			'counter'		=> 0,
		];

		$parts	= explode( '/', $path );
		$facts->controller	= array_shift( $parts );
		$facts->action		= array_shift( $parts );
		while( count( $parts ) ){
			$part = trim( array_shift( $parts ) );
			if( strlen( $part ) )
				$facts->arguments[]	= $part;
		}
		return $facts;
	}
}
