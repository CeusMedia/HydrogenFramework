<?php

namespace CeusMedia\HydrogenFramework;

use CeusMedia\Common\Alg\Obj\MethodFactory;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class Hook
{
	/** @var object|NULL $context */
	protected $context;

	/** @var Environment $env */
	protected $env;

	/** @var object|NULL $module */
	protected $module;

	/** @var array $payload */
	protected $payload		= [];

	public function __construct( Environment $env, ?object $context = NULL )
	{
		$this->setEnv( $env );
		if( NULL !== $context )
			$this->setContext( $context );
	}

	public function getPayload() : ?array
	{
		return $this->payload;
	}

	public function setEnv( Environment $env ): self
	{
		$this->env	= $env;
		return $this;
	}

	public function setContext( object $context ): self
	{
		$this->context	= $context;
		return $this;
	}

	public function setModule( object $module ): self
	{
		$this->module	= $module;
		return $this;
	}

	public function setPayload( array $payload ): self
	{
		$this->payload	= $payload;
		return $this;
	}

	public function fetch( $method )
	{
		if (!is_object($this->context))
			throw new \RuntimeException('No context set');
		if (!is_object($this->env))
			throw new \RuntimeException('No environment set');
		$factory = new MethodFactory();
		return call_user_func_array( [get_class( $this ), $method], [
			$this->env,
			$this->context,
			$this->module,
			& $this->payload
		] );
	}

	public static function callHook( Environment $env, string $resource, string $event, ?object $context, array & $payload ): ?bool
	{
		return $env->getCaptain()->callHook( $resource, $event, $context, $payload );
	}

	//  --  PROTECTED  --  //

	protected static function getModuleConfig( Environment $env, $module )
	{
		return $env->getConfig()->get( 'module.'.strtolower( $module ).'.', TRUE );
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
	protected static function redirect( WebEnvironment $env, string $controller = 'index', string $action = "index", array $arguments = [], array $parameters = [] )
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
	protected static function relocate( WebEnvironment $env, string $uri, int $status = NULL )
	{
		static::restart( $env, $uri, $status, TRUE );
	}

	/**
	 *	Redirects by requesting a URI.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	By default, redirect URIs are request path within the current application, eg. "./[CONTROLLER]/[ACTION]"
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
	protected static function restart( WebEnvironment $env, string $uri, int $status = NULL, bool $allowForeignHost = FALSE, int $modeFrom = 0 )
	{
		$env->restart( $uri, $status, $allowForeignHost, $modeFrom );
	}
}
