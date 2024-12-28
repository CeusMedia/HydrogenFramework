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
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\RouterInterface;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

use RuntimeException;

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

	protected int $counter	= 0;

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
			if( str_starts_with( $uri, './' ) )
				$uri	= substr( $uri, 2 );
		}
		return $this->env->url.$uri;
	}

	/**
	 *	Number of attempts to find a suitable controller class.
	 *	Since last parse call, of course.
	 *	@return int
	 */
	public function getCounter(): int
	{
		return $this->counter;
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

	protected function getPathFromRequest(): array
	{
		if( !$this->env->has( 'request' ) )
			throw new RuntimeException( 'Routing needs a registered request resource' );
		/** @var HttpRequest $request */
		$request	= $this->env->getRequest();

		if( FALSE !== getEnv( 'REDIRECT_URL' ) && $request->has( '__path' ) )
			self::$pathKey	= '__path';

		$path	= $request->get( self::$pathKey, '' );
		if( $this->env instanceof WebEnvironment )
			if( $request instanceof HttpRequest )
				$path	= $request->getFromSource( self::$pathKey, 'get' ) ?? '';
		return [$request, trim( $path )];
	}
}
