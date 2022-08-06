<?php
/**
 *	...
 *
 *	Copyright (c) 2011-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2011-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
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
 *	@copyright		2011-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class Abstraction implements RouterInterface
{
	/**	@var		string		Key of path in request, default: path */
	static public $pathKey		= "path";

	/**	@var	Environment		$env		Environment object */
	protected $env;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->parseFromRequest();
	}

	public function getAbsoluteUri( string $controller = NULL, string $action = NULL, array $arguments = array(), array $parameters = array(), string $fragmentId = NULL ): string
	{
		$uri	= $this->getRelativeUri( $controller, $action, $arguments, $parameters, $fragmentId );
		if( strlen( $uri ) ){
			if( $uri == '.' )
				$uri	= '';
			if( substr( $uri, 0, 2 ) == './' )
				$uri	= substr( $uri, 2 );
		}
		$uri	= $this->env->url.$uri;
		return $uri;
	}

	public function getRelativeUri( string $controller = NULL, string $action = NULL, array $arguments = array(), array $parameters = array(), string $fragmentId = NULL ): string
	{
		$data	= array(
			'controller'	=> $this->env->getRequest()->get( '__controller' ),
			'action'		=> $this->env->getRequest()->get( '__action' ),
			'arguments'		=> array(),
			'parameters'	=> array(),
		);
		$uri	= '.';
		if( !is_null( $controller ) ){
			$uri	.= '/'.$controller;
			if( !is_null( $action ) )
				$uri	.= '/'.$action;
		}

		if( !is_null( $arguments ) && is_array( $arguments )  )
			foreach( $arguments as $key => $value )
				$uri	.= '/'.$value;
		if( !is_null( $parameters ) && is_array( $parameters ) && count( $parameters ) )
			$uri	.= '?'.http_build_query( $parameters, NULL, '&amp;' );
		if( $fragmentId )
			$uri	.= '#'.$fragmentId;
		return $uri;
	}
}
