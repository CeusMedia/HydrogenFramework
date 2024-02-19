<?php
/** @noinspection PhpUnused */
/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Hook, to be called by captain on events, mostly registered by modules.
 *
 *	Copyright (c) 2007-2024 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework;

use BadMethodCallException;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use DomainException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 *	Hook, to be called by captain on events, mostly registered by modules.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Hook
{
	/**	@var	Environment					$env */
	protected Environment $env;

	/**	@var	object|NULL					$context */
	protected ?object $context				= NULL;

	/**	@var	ModuleDefinition|NULL		$module */
	protected ?ModuleDefinition $module		= NULL;

	/** @var	array						$payload */
	protected array $payload				= [];

	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		Environment		$env
	 *	@param		string			$resource		Name of resource (e.G. Page or View)
	 *	@param		string			$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object|NULL		$context		Context object, will be available inside hook as $context
	 *	@param		array			$payload		Map of hook payload data, will be available inside hook as $payload
	 *	@return		bool|NULL						TRUE if hook is chain-breaking, FALSE if hook is disabled or non-chain-breaking, NULL if no modules installed or no hooks defined
	 *	@throws		RuntimeException				if given static class method is not existing
	 *	@throws		RuntimeException				if method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException				if method call is throwing an exception
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 */
	public static function callHook( Environment $env, string $resource, string $event, ?object $context, array & $payload ): ?bool
	{
		return $env->getCaptain()->callHook( $resource, $event, $context, $payload );
	}

	/**
	 *	@param		Environment		$env
	 *	@param		object|NULL		$context		Context object, will be available inside hook as $context
	 */
	public function __construct( Environment $env, ?object $context = NULL )
	{
		$this->setEnv( $env );
		if( NULL !== $context )
			$this->setContext( $context );
	}

	/**
	 *	...
	 *	@param		string		$method			Name of hook method
	 *	@return		bool|null
	 *	@throws		BadMethodCallException		if hook method is not existing or not callable
	 *	@throws		RuntimeException			if no context or environment set
	 *	@throws		ReflectionException
	 */
	public function fetch( string $method ): ?bool
	{
		if( !is_object( $this->context ) )
			throw new RuntimeException( 'No context set' );
		if( !is_object( $this->env ) )
			throw new RuntimeException( 'No environment set' );
		if( !method_exists( $this, $method ) )
			throw new BadMethodCallException( vsprintf(
				'Hook method %s::%s is not existing',
				[get_class( $this ), $method]
			) );

		$reflection = new ReflectionMethod( get_class( $this ), $method );
		if( $reflection->isStatic() ) {
			return $this->fetchStatic( $method );
		}
		else {
			return $this->$method($this->env, $this->context, $this->module, $this->payload);
		}
	}

	/**
	 *	Returns set map of hook payload data.
	 *	@access		public
	 *	@return		array|NULL
	 */
	public function getPayload() : ?array
	{
		return $this->payload;
	}

	/**
	 *	Sets context object.
	 *	@access		public
	 *	@param		object|NULL		$context		Context object
	 *	@return		self
	 */
	public function setContext( ?object $context ): self
	{
		$this->context	= $context;
		return $this;
	}

	/**
	 *	Sets environment object.
	 *	@access		public
	 *	@param		Environment		$env		Environment object
	 *	@return		self
	 */
	public function setEnv( Environment $env ): self
	{
		$this->env	= $env;
		return $this;
	}

	/**
	 *	Sets definition of related module.
	 *	@access		public
	 *	@param		ModuleDefinition|NULL	$module		Definition of related module
	 */
	public function setModule( ?ModuleDefinition $module ): self
	{
		$this->module	= $module;
		return $this;
	}

	/**
	 *	Sets map of hook payload data.
	 *	@access		public
	 *	@param		array|NULL		$payload		Map of hook payload data
	 *	@return		self
	 */
	public function setPayload( ?array & $payload ): self
	{
		$this->payload	= & $payload;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Call hook methods, which are statically defined.
	 *	Static hook methods were the default way until version 0.9.1.
	 *	@access		protected
	 *	@param		string		$method		Name of hook method
	 *	@return		bool|NULL
	 */
	protected function fetchStatic( string $method ): ?bool
	{
		$call	= [get_class( $this ), $method];
		if( !is_callable( $call ) )
			throw new BadMethodCallException( vsprintf('Hook method %s::%s is not callable', [
				get_class( $this ), $method] ) );

		return call_user_func_array( $call, [
			$this->env,
			$this->context,
			$this->module,
			& $this->payload
		]);
	}

	/**
	 *	Returns the set config pairs of a module by its ID within a given environment as dictionary.
	 *	This will NOT return the configuration of the module set within this hook.^
	 *	@param		Environment		$env
	 *	@param		string			$moduleId
	 *	@return		Dictionary
	 */
	protected static function getModuleConfig( Environment $env, string $moduleId ): Dictionary
	{
		$prefix			= 'module.'.strtolower( $moduleId ).'.';
		/** @var Dictionary $moduleConfig */
		$moduleConfig	= $env->getConfig()->getAll( $prefix, TRUE );
		return $moduleConfig;
	}

	/**
	 *	Redirects by calling different Controller and Action.
	 *	Attention: Will only have an effect in hooks called within dispatching.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *	Attention: This is not recommended, please use restart in favour.
	 *	@static
	 *	@access		protected
	 *	@param		WebEnvironment	$env			Instance of environment
	 *	@param		string			$controller		Controller to be called, default: index
	 *	@param		string			$action			Action to be called, default: index
	 *	@param		array			$arguments		List of arguments to add to URL
	 *	@param		array			$parameters		Map of additional parameters to set in request
	 *	@return		void			Always returns TRUE to indicate that dispatching hook is done
	 */
	protected static function redirect( WebEnvironment $env, string $controller = 'index', string $action = "index", array $arguments = [], array $parameters = [] ): void
	{
		$request	= $env->getRequest();
		$request->set( '__controller', $controller );
		$request->set( '__action', $action );
		$request->set( '__arguments', $arguments );
		foreach( $parameters as $key => $value )
			if( !empty( $key ) )
				$request->set( $key, $value );
	}

	/**
	 *	Redirects to given URI, allowing URIs external to current application.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	Alias for restart with parameters $allowForeignHost set to TRUE.
	 *	Similar to: static::restart( 'http://foreign.tld/', NULL, TRUE );
	 *
	 *	HTTP status will be 200 or second parameter.
	 *
	 *	@static
	 *	@access		protected
	 *	@param		WebEnvironment	$env			Instance of environment
	 *	@param		string			$uri				URI to request, may be external
	 *	@param		integer|NULL	$status				HTTP status code to send, default: NULL -> 200
	 *	@return		void
	 *	@todo		check for better HTTP status
	 */
	protected static function relocate( WebEnvironment $env, string $uri, int $status = NULL ): void
	{
		static::restart( $env, $uri, $status, TRUE );
	}

	/**
	 *	Redirects by requesting a URI.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	By default, redirect URIs are request path within the current application, e.g. "./[CONTROLLER]/[ACTION]"
	 *	ATTENTION: For browser compatibility local paths should start with "./"
	 *
	 *	If second parameter is set to a valid HTTP status code, the code and its HTTP status text will be set for response.
	 *
	 *	If third parameter is set to TRUE, redirects to URIs outside the current domain are allowed.
	 *	This would look like this: static::restart( 'http://foreign.tld/', NULL, TRUE );
	 *	There is a shorter alias: static::relocate( 'http://foreign.tld/' );
	 *
	 *	@access		protected
	 *	@static
	 *	@param		WebEnvironment	$env				Instance of Web Environment
	 *	@param		string			$uri				URI to request
	 *	@param		integer|NULL	$status				HTTP status code to send, default: NULL -> 200
	 *	@param		boolean			$allowForeignHost	Flag: allow redirection outside application base URL, default: no
	 *	@param		integer			$modeFrom			How to handle FROM parameter from request or for new request, not handled atm
	 *	@return		void
	 */
	protected static function restart( WebEnvironment $env, string $uri, int $status = NULL, bool $allowForeignHost = FALSE, int $modeFrom = 0 ): void
	{
		$env->restart( $uri, $status, $allowForeignHost, $modeFrom );
	}
}
