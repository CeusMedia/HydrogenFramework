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

use CeusMedia\HydrogenFramework\Environment;
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
abstract class Abstraction implements RouterInterface
{
	/**	@var		string		Key of path in request, default: path */
	public static string $pathKey		= "path";

	/**	@var	Environment		$env		Environment object */
	protected Environment $env;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->parseFromRequest();
	}

	public function getAbsoluteUri( string $controller = NULL, string $action = NULL, array $arguments = [], array $parameters = [], string $fragmentId = NULL ): string
	{
		$uri	= $this->getRelativeUri( $controller, $action, $arguments, $parameters, $fragmentId );
		if( strlen( $uri ) ){
			if( $uri == '.' )
				$uri	= '';
			if( substr( $uri, 0, 2 ) == './' )
				$uri	= substr( $uri, 2 );
		}
		return $this->env->url.$uri;
	}

	public function getRelativeUri( string $controller = NULL, string $action = NULL, array $arguments = [], array $parameters = [], string $fragmentId = NULL ): string
	{
		$uri	= '.';
		if( !is_null( $controller ) ){
			$uri	.= '/'.$controller;
			if( !is_null( $action ) )
				$uri	.= '/'.$action;
		}

		foreach( $arguments as $value )
			$uri	.= '/'.$value;
		if( count( $parameters ) )
			$uri	.= '?'.http_build_query( $parameters, '', '&amp;' );
		if( $fragmentId )
			$uri	.= '#'.$fragmentId;
		return $uri;
	}
}
