<?php
/**
 *	...
 *
 *	Copyright (c) 2011-2020 Christian Würker (ceusmedia.de)
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
 *	@copyright		2011-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Router
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class CMF_Hydrogen_Environment_Router_Abstract implements CMF_Hydrogen_Environment_Router_Interface
{
	/**	@var		string		Key of path in request, default: path */
	static public $pathKey		= "path";

	/**	@var	CMF_Hydrogen_Environment			$env		Environment object */
	protected $env;
#	public $configKeyBaseHref	= 'app.base.url';
#	public $configKeyBaseHref	= 'app.base.href';
	public $configKeyBaseHref	= 'application.base';

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
		$this->parseFromRequest();
	}

	public function getRelativeUri( $controller = NULL, $action = NULL, $arguments = NULL, $parameters = NULL, $fragmentId = NULL )
	{
		$data	= array(
			'controller'	=> $this->env->request->get( 'controller' ),
			'action'		=> $this->env->request->get( 'action' ),
			'arguments'		=> array(),
			'parameters'	=> array(),
		);
		$uri	= '.';
		if( !is_null( $controller ) )
		{
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

	public function getAbsoluteUri( $controller = NULL, $action = NULL, $arguments = NULL, $parameters = NULL, $fragmentId = NULL )
	{
		$uri	= $this->getRelativeUri( $controller, $action, $arguments, $parameters, $fragmentId );
		if( strlen( $uri ) )
		{
			if( $uri == '.' )
				$uri	= '';
			if( substr( $uri, 0, 2 ) == './' )
				$uri	= substr( $uri, 2 );
		}
		$uri	= $this->env->config->get( $this->optionKeyBaseHref ).$uri;
		return $uri;
	}

	public function realizeInResponse(){
		if( !$this->env->response )
			throw new RuntimeException( 'Route filtering needs a registered response resource' );
		$body	= $this->env->response->getBody();
		xmp( $body );
		die;
	}}
