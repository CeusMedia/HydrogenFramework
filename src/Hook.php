<?php
class CMF_Hydrogen_Hook
{
	public static function callHook( CMF_Hydrogen_Environment $env, string $resource, string $event, $context, $module, $data )
	{
		return $env->getCaptain()->callHook( $resource, $event, $context, $data );
	}

	//  --  PROTECTED  --  //

	protected static function getModuleConfig( CMF_Hydrogen_Environment $env, $module )
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
	 *	@param		object		$env				Instance of CMF_Hydrogen_Environment
	 *	@param		string		$controller		Controller to be called, default: index
	 *	@param		string		$action			Action to be called, default: index
	 *	@param		array		$arguments		List of arguments to add to URL
	 *	@param		array		$parameters		Map of additional parameters to set in request
	 *	@return		boolean		Always returns TRUE to indicate that dispatching hook is done
	 *	@todo		remove first 2 lines after Env::redirect has been deprecated
	 */
	protected static function redirect( CMF_Hydrogen_Environment $env, string $controller = 'index', string $action = "index", array $arguments = array(), array $parameters = array() )
	{
//		$env->redirect( $controller, $action, $arguments, $parameters );
//		return TRUE;

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
	 *	@param		object		$env				Instance of CMF_Hydrogen_Environment
	 *	@param		string		$uri				URI to request, may be external
	 *	@param		integer		$status				HTTP status code to send, default: NULL -> 200
	 *	@return		void
	 *	@todo		kriss: check for better HTTP status
	 */
	protected static function relocate( string $uri, int $status = NULL )
	{
		static::restart( $uri, $status, TRUE );
	}

	/**
	 *	Redirects by requesting a URI.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	By default, redirect URIs are are request path within the current application, eg. "./[CONTROLLER]/[ACTION]"
	 *	ATTENTION: For browser compatibility local paths should start with "./"
	 *
	 *	If second parameter is set to a valid HTTP status code, the code and its HTTP status text will be set for response.
	 *
	 *	If third parameter is set to TRUE, redirects to URIs outside the current domain are allowed.
	 *	This would look like this: static::restart( 'http://foreign.tld/', NULL, TRUE );
	 *	There is a shorter alias: static::relocate( 'http://foreign.tld/' );
	 *
	 *	@static
	 *	@access		protected
	 *	@param		object		$env				Instance of CMF_Hydrogen_Environment
	 *	@param		string		$uri				URI to request
	 *	@param		integer		$status				HTTP status code to send, default: NULL -> 200
	 *	@param		boolean		$allowForeignHost	Flag: allow redirection outside application base URL, default: no
	 *	@param		integer		$modeFrom			How to handle FROM parameter from request or for new request, not handled atm
	 *	@return		void
	 */
	protected static function restart( CMF_Hydrogen_Environment $env, string $uri, int $status = NULL, bool $allowForeignHost = FALSE, int $modeFrom = 0 )
	{
		$env->restart( $uri, $status, $allowForeignHost, $modeFrom );
	}

	static protected function sendMail( CMF_Hydrogen_Environment $env, Mail_Abstract $mail, array $receivers = array() )
	{
		$language	= $env->getLanguage()->getLanguage();										// @todo apply user language
		foreach( $receivers as $receiver ){
			if( is_string( $receiver ) )
 				$receiver	= (object) array( 'email' => $receiver );
			if( is_array( $receiver ) )
 				$receiver	= (object) $receiver;
			if( !property_exists( $receiver, 'email' ) )
				throw new InvalidArgumentException( 'Given receiver is missing email address' );
			$env->getLogic()->mail->handleMail( $mail, $receiver, $language );
		}
	}
}
