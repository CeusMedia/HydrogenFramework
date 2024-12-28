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

use CeusMedia\HydrogenFramework\Dispatcher\General;
use CeusMedia\HydrogenFramework\Environment\RouterInterface;

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
	public function parseFromRequest(): void
	{
		[$request, $path]	= $this->getPathFromRequest();
		if( '' === $path )
			return;

		$facts	= self::parseFromPath( urldecode( $path ) );
		foreach( ['controller', 'action', 'arguments'] as $key )
			$request->set( '__'.$key, $facts->$key );
		$this->counter	= $facts->counter;

/*		remark( "controller: ".$request->get( '__controller' ) );
		remark( "action: ".$request->get( '__action' ) );
		remark( "arguments: " );
		print_m( $request->get( '__arguments' ) );
		die;*/

		$message	= '%s: %s attempts to find controller for %s';
		$this->env->getLog()?->log( 'info', vsprintf( $message, [
			static::class,
			$facts->counter,
			$path
		] ) );
	}

	/**
	 *	@param		string		$path
	 *	@return		object{controller: string, action: string, arguments: array, counter: int}
	 */
	protected static function parseFromPath( string $path ): object
	{
		$facts	= (object) [
			'controller'	=> '',
			'action'		=> '',
			'arguments'		=> [],
			'counter'		=> 0,
		];

		$path	= preg_replace( '@^(.*)/?$@U', '\\1', trim( $path ) ) ?? '';	//  secure path
		$parts	= explode( '/', $path );												//  split into parts
		$left	= $parts;																		//  start with all parts
		$right	= [];																			//  prepare list for arguments
		if( '' !== trim( $path ) ){
			while( count( $left ) ){
				$classNameVariations	= General::getClassNameVariationsByPath( join( '/', $left ), TRUE, FALSE, TRUE, TRUE );
				foreach( $classNameVariations as $classNameVariation ){
					$className	= 'Controller_'.$classNameVariation;
					$facts->counter++;
					if( !class_exists( $className ) )
						continue;
	//				remark( 'Controller Class: '.$className );
					$facts->controller	= implode( '/', $left );
					if( 0 !== count( $right ) ){
						if( method_exists( $className, current( $right ) ) ){
	//						remark( 'Controller Method: '.$right[0] );
							$facts->action	= array_shift( $right ) ?? '';
						}
						$facts->arguments	= $right;
	//					if( $right )
	//						remark( 'Arguments: '.implode( ', ', $right ) );
					}
					break 2;
				}
				array_unshift( $right, array_pop( $left ) ?? '_undefined' );
			}
		}
		if( '' === $facts->controller )
			if( 0 !== count( $right ) )
				$facts->arguments	= $right;

		return $facts;
	}
}
