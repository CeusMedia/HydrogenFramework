<?php
/**
 *	Generic Controller Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2022 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Obj\MethodFactory as MethodFactory;
use CeusMedia\Common\Alg\Text\CamelCase as CamelCase;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

use DateTime;
use RuntimeException;

/**
 *	Generic Controller Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Controller
{
	const RESTART_FROM_IGNORE		= 0;
	const RESTART_FROM_POP			= 1;
	const RESTART_FROM_APPLY		= 2;
	const RESTART_FROM_CARRY		= 4;
	const RESTART_FROM_SET			= 8;
	const RESTART_FROM_PUSH			= 16;

	public static $moduleId			= '';
	public static $prefixModel		= 'Model_';
	public static $prefixView		= 'View_';

	/**	@var		string								$alias			Optional alternative path for restarting */
	public $alias					= '';

	/**	@var		WebEnvironment						$env			Application Environment Object */
	protected $env;
	/**	@var		string								$defaultPath	Default controller URI path */
	protected $defaultPath;
	/**	@var		string								$path			Preferred controller URI path */
	protected $path;
	/**	@var		View|NULL							$view			View instance for controller */
	protected $view;
	/**	@var		Dictionary							$moduleConfig	Map of module configuration pairs */
	protected $moduleConfig;

	/**	@var		string								$controller		Name of called Controller */
	protected $controller		= "";
	/**	@var		string								$action			Name of called Action */
	protected $action			= "";
	/**	@var		bool								$redirect		Flag for Redirection */
	var $redirect				= FALSE;

	protected $logRestarts		= FALSE;

	/**
	 *	Constructor.
	 *	Will set up related view class by default. Disable this for controllers without views.
	 *	Calls __onInit() in the end.
	 *	@access		public
	 *	@param		WebEnvironment						$env			Application Environment Object
	 *	@param		boolean								$setupView		Flag: auto create view object for controller (default: TRUE)
	 *	@return		void
	 */
	public function __construct( WebEnvironment $env, bool $setupView = TRUE )
	{
		$env->getRuntime()->reach( 'CMF_Controller('.get_class( $this ).')' );
		static::$moduleId	= trim( static::$moduleId );
		$this->setEnv( $env );

//		$env->getRuntime()->reach( 'CMF_Controller('.get_class( $this ).'): env set' );
		if( $setupView )
			$this->setupView( !$env->getRequest()->isAjax() );
		$env->getRuntime()->reach( 'CMF_Controller('.get_class( $this ).'): got view object' );

		$controllerName		= preg_replace( "/^Controller_/", "", get_class( $this ) );				//  get controller name from class name
		$this->defaultPath	= strtolower( str_replace( '_', '/', $controllerName ) );				//  to guess default controller URI path
		$this->path			= $this->defaultPath;													//  and note this as controller path

		$data				= array( 'controllerName' => $controllerName );							//  with cut controller name
		if( $path = $this->callHook( 'Controller', 'onDetectPath', $this, $data ) )					//  to get preferred controller URI path
			$this->path		= $path;																//  and set if has been resolved

//		$arguments		= array_slice( func_get_args(), 1 );										//  collect additional arguments for extended logic classes
//		Alg_Object_MethodFactory::callObjectMethod( $this, '__onInit', $arguments, TRUE, TRUE );	//  invoke possibly extended init method
		try{
			$this->__onInit();																		//  default callback for construction end
		}
		catch( \Exception $e ){
			$payload	= array( 'exception' => $e );
			$this->callHook( 'App', 'onException', $this, $payload );
			throw new RuntimeException( $e->getMessage(), $e->getCode(), $e );
		}
		$env->getRuntime()->reach( 'CMF_Controller('.get_class( $this ).'): done' );				//  log time of construction
	}

	public function getView()
	{
		return $this->view;
	}

	/**
	 *	Returns View Content of called Action.
	 *	@access		public
	 *	@return		string
	 *	@throws		RuntimeException			if no view has been set up
	 *	@throws		RuntimeException			if
	 *	@throws		RuntimeException			if
	 */
	public function renderView(): string
	{
		$this->env->getRuntime()->reach( 'Controller::getView: start' );
		if( !$this->view )
			throw new RuntimeException( 'No view object created in constructor' );
		if( !method_exists( $this->view, $this->action ) )
			throw new RuntimeException( 'View Action "'.$this->action.'" not defined yet', 302 );
		$language		= $this->env->getLanguage();
		$this->env->getRuntime()->reach( 'Controller::getView: got language' );
		if( $language->hasWords( $this->controller ) )
			$this->view->setData( $language->getWords( $this->controller ), 'words' );
		$this->env->getRuntime()->reach( 'Controller::getView: set words' );

		$factory	= new MethodFactory( $this->view, $this->action );
		$result		= $factory->call();
		if( is_string( $result ) ){
			$this->env->getRuntime()->reach( 'Controller::getView: Action called' );
		}
		else if( $this->view->hasTemplate( $this->controller, $this->action ) ){
			$result	= $this->view->loadTemplate( $this->controller, $this->action );
			$this->env->getRuntime()->reach( 'Controller::getView: loadTemplate' );
		}
		else if( $this->view->hasContent( $this->controller, $this->action, 'html/' ) ){
			$result	= $this->view->loadContent( $this->controller, $this->action );
			$this->env->getRuntime()->reach( 'Controller::getView: loadContent' );
		}
		else{
			$message	= 'Neither view template nor content file defined for request path "%s/%s"';
			throw new RuntimeException( sprintf( $message, $this->controller, $this->action ) );
		}
		$this->env->getRuntime()->reach( 'Controller::getView: done' );
		return $result;
	}

	/**
	 *	Set activity of logging of restarts.
	 *	@access		public
	 *	@param		boolean		$log		Flag: Activate logging of restarts (default)
	 *	@return		self
	 */
	public function setLogRestarts( $log = TRUE ): self
	{
		$this->logRestarts	= (bool) $log;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Magic function called at the end of construction.
	 *	Override to implement custom resource construction.
	 *
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit()
	{
	}

	protected function addData( string $key, $value, string $topic = NULL ): self
	{
		$this->view->setData( array( $key => $value ), $topic );
		return $this;
	}

	protected function callHook( string $resource, string $event, $context = NULL, $data = [] )
	{
		$context	= $context ? $context : $this;
		return $this->env->getCaptain()->callHook( $resource, $event, $context, $data );
	}

	/**
	 *	Checks if current request came via AJAX.
	 *	Otherwise returns application main page as HTML by redirection.
	 *	Also notes a failure message in UI messenger.
	 *	@access		public
	 *	@return		void
	 *	@todo		locale support (use main.ini section msg etc.)
	 */
	protected function checkAjaxRequest()
	{
		if( !$this->env->getRequest()->isAjax() ){
			$this->env->getMessenger()->noteFailure( 'Invalid AJAX/AJAJ access attempt.' );
			$this->restart( NULL, FALSE, 401 );
		}
	}

	protected function compactFilterInput( $input )
	{
		if( is_object( $input ) || is_resource( $input ) || is_null( $input ) )						//  input is of invalid type
			return NULL;																			//  break with empty result
		if( is_array( $input ) ){																	//  input is an array
			foreach( $input as $nr => $chunk ){														//  iterate map pairs
				$chunk	= $this->compactFilterInput( $chunk );										//  compact map pair data chunk
				if( !( is_array( $chunk ) && count( $chunk ) ) && !strlen( $chunk ) )				//  chunk is empty
					unset( $input[$nr] );															//  remove chunk pair from map
			}
		}
		return $input;
	}

	/**
	 *	Returns Data for View.
	 *	@access		protected
	 *	@param		string|NULL		$key			Key of data to return
	 *	@param		string|NULL		$fallback		String to return if no data is set by key
	 *	@return		mixed
	 */
	protected function getData( string $key = NULL, string $fallback = NULL )
	{
		return $this->view->getData( $key, $fallback );
	}

	/**
	 *	Tries to find logic class for short logic key and returns instance.
	 *	This protected method can be used within your custom controller to load logic classes.
	 *	Example: $this->getLogic( 'mailGroupMember' ) for instance of class 'Logic_Mail_Group_Member'
	 *
	 *	If no short logic key is given, the logic pool resource of environment will be returned.
	 *	So, you can use $this->getLogic() as shortcut for $this->env->getLogic().
	 *
	 *	@access		protected
	 *	@param		string		$key		Key for logic class (ex: 'mailGroupMember' for 'Logic_Mail_Group_Member')
	 *	@return		Logic					Logic instance or logic pool if no key given
	 *	@throws		RuntimeException		if no logic class could be found for given short logic key
	 */
	protected function getLogic( string $key ): Logic
	{
//		if( is_null( $key ) || !strlen( trim( $key ) ) )
//			return $this->env->getLogic();
		return $this->env->getLogic()->get( $key );
	}

	/**
	 *	Tries to find model class for short model key and returns instance.
	 *	@access		protected
	 *	@param		string		$key		Key for model class (eG. 'mailGroupMember' for 'Model_Mail_Group_Member')
	 *	@return		object					Model instance
	 *	@throws		RuntimeException		if no model class could be found for given short model key
	 *	@todo		create model pool environment resource and apply to created shared single instances instead of new instances
	 *	@todo		change \@return to CMF_Hydrogen_Model after CMF model refactoring
	 *	@see		duplicate code with CMF_Hydrogen_Logic::getModel
	 */
	protected function getModel( string $key )
	{
		if( preg_match( '/^[A-Z][A-Za-z0-9_]+$/', $key ) )
			$className	= self::$prefixModel.$key;
		else{
			$classNameWords	= ucwords( CamelCase::decode( $key ) );
			$className		= str_replace( ' ', '_', 'Model '.$classNameWords );
		}
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Model class "'.$className.'" not found' );
		return ObjectFactory::createObject( $className, array( $this->env ) );
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string		$section	Section in locale file
	 *	@param		string		$topic		Locale file key, eg. test/my, default: current controller
	 *	@return		array
	 */
	protected function getWords( string $section = NULL, string $topic = NULL ): array
	{
		if( empty( $topic )/* && $this->env->getLanguage()->hasWords( $this->controller )*/ )
			$topic = $this->controller;
		if( empty( $section ) )
			return $this->env->getLanguage()->getWords( $topic );
		return $this->env->getLanguage()->getSection( $topic, $section );
	}

	protected function handleJsonResponse( $status, $data, $httpStatusCode = NULL )
	{
		$type			= $status;
		$httpStatusCode	= $httpStatusCode ? $httpStatusCode : 200;
		if( in_array( $status, array( TRUE, 'data', 'success', 'succeeded' ), TRUE ) )
			$type	= "data";
		else if( in_array( $status, array( FALSE, "error", "fail", "failed" ), TRUE ) )
			$type	= "error";
		$response	= (object) array(
			'code'		=> $httpStatusCode,
			'status'	=> $type,
			'data'		=> $data,
			'timestamp'	=> microtime( TRUE ),
		);
		$json	= json_encode( $response, JSON_PRETTY_PRINT );
		if( headers_sent() ){
			print( 'Headers already sent.' );
		}
		else{
			header( 'HTTP/1.1 '.$httpStatusCode.' '.HttpStatus::getText( $httpStatusCode ) );
			header( 'Content-Type: application/json' );
			header( 'Content-Length: '.strlen( $json ) );
			print( $json );
		}
		exit;
	}

	protected function handleJsonErrorResponse( $data, $httpStatusCode = NULL )
	{
		$this->handleJsonResponse( 'error', $data, $httpStatusCode );
	}

	/**
	 *	Redirects by calling different Controller and Action.
	 *	Attention: This will *NOT* effect the URL in browser nor need cURL requests to allow forwarding.
	 *	Attention: This is not recommended, please use restart in favour.
	 *	@access		protected
	 *	@param		string		$controller		Controller to be called, default: index
	 *	@param		string		$action			Action to be called, default: index
	 *	@param		array		$arguments		List of arguments to add to URL
	 *	@param		array		$parameters		Map of additional parameters to set in request
	 *	@return		void
	 */
	protected function redirect( string $controller = 'index', string $action = "index", array $arguments = [], array $parameters = [] )
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.6.4' )
			->setExceptionVersion( '0.8.9' )
			->message( 'Redirecting is usable for hooks within dispatching, only. Please use restart instead!' );
		$request	= $this->env->getRequest();
		$request->set( '__controller', $controller );
		$request->set( '__action', $action );
		$request->set( '__arguments', $arguments );
		foreach( $parameters as $key => $value )
			if( !empty( $key ) )
				$request->set( $key, $value );
		$this->redirect = TRUE;
	}

	/**
	 *	Redirects to given URI, allowing URIs external to current application.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	Alias for restart with parameters $allowForeignHost set to TRUE and $withinModule to FALSE.
	 *	Similar to: $this->restart( 'http://foreign.tld/', FALSE, NULL, TRUE );
	 *
	 *	HTTP status will be 200 or second parameter.
	 *
	 *	@access		protected
	 *	@param		string		$uri				URI to request, may be external
	 *	@param		integer		$status				HTTP status code to send, default: NULL -> 200
	 *	@return		void
	 *	@todo		kriss: check for better HTTP status
	 */
	protected function relocate( string $uri, $status = NULL )
	{
		$this->restart( $uri, FALSE, $status, TRUE );
	}

	/**
	 *	Redirects by requesting a URI.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	By default, redirect URIs are are request path within the current application, eg. "./[CONTROLLER]/[ACTION]"
	 *	ATTENTION: For browser compatibility local paths should start with "./"
	 *
	 *	If seconds parameter is set to TRUE, redirects to a path inside the current controller.
	 *	Therefore the given URI needs to be a path inside the current controller.
	 *	This would look like this: $this->restart( '[ACTION]', TRUE );
	 *	Of course you can append actions arguments and parameters.
	 *
	 *	If third parameter is set to a valid HTTP status code, the code and its HTTP status text will be set for response.
	 *
	 *	If forth parameter is set to TRUE, redirects to URIs outside the current domain are allowed.
	 *	This would look like this: $this->restart( 'http://foreign.tld/', FALSE, NULL, TRUE );
	 *	There is a shorter alias: $this->relocate( 'http://foreign.tld/' );
	 *
	 *	@access		protected
	 *	@param		string		$uri				URI to request
	 *	@param		boolean		$withinModule		Flag: user path inside current controller
	 *	@param		integer		$status				HTTP status code to send, default: NULL -> 200
	 *	@param		boolean		$allowForeignHost	Flag: allow redirection outside application base URL, default: no
	 *	@param		integer		$modeFrom			How to handle FROM parameter from request or for new request, not handled atm
	 *	@return		void
	 *	@link		https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection HTTP status codes
	 *	@todo		kriss: implement automatic lookout for "from" request parameter
	 *	@todo		kriss: implement handling of FROM request parameter, see controller constants
	 *	@todo		kriss: concept and implement anti-loop {@see http://dev.(ceusmedia.de)/cmKB/?MTI}
	 */
	protected function restart( ?string $uri = NULL, bool $withinModule = FALSE, ?int $status = NULL, bool $allowForeignHost = FALSE, int $modeFrom = 0 )
	{
		$mode	= 'ext';
		if( !preg_match( "/^http/", $uri ) ){														//  URI is not starting with HTTP scheme
			$mode	= 'int';
			if( $withinModule ){																	//  redirection is within module
				$mode	= 'mod';
				$controller	= $this->env->getRequest()->get( '__controller' );						//  get current controller
				$controller	= $this->alias ? $this->alias : $controller;							//
				$uri		= $controller.( strlen( $uri ) ? '/'.$uri : '' );						//
			}
		}
		if( $this->logRestarts )
			error_log( vsprintf( '%s %s %s %s'."\n", array(
				date( DateTime::ATOM ),
				$status ? $status : 200,
				$mode,
				$uri
			) ), 3, 'logs/restart.log' );
		$this->env->restart( $uri, $status, $allowForeignHost, $modeFrom );
	}

	/**
	 *
	 *	Sets Data for View.
	 *	@access		protected
	 *	@param		array		$data			Array of Data for View
	 *	@param		string		$topic			Optional: Topic Name of Data
	 *	@return		self
	 */
	protected function setData( array $data, string $topic = '' ): self
	{
		if( $this->view )
			$this->view->setData( $data, $topic );
		return $this;
	}

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		WebEnvironment	$env			Framework Resource Environment Object
	 *	@return		self
	 */
	protected function setEnv( WebEnvironment $env ): self
	{
		$this->env			= $env;
		$this->controller	= $env->getRequest()->get( '__controller' );
		$this->action		= $env->getRequest()->get( '__action' );
		if( $this->env->has( 'language' ) && $this->controller ){
			$language	= $this->env->getLanguage();
			$language->load( $this->controller, FALSE, FALSE );
		}
		//  load module configuration
		$list	= [];
		if( strlen( static::$moduleId ) && $env->getModules()->has( static::$moduleId ) )
			foreach( $env->getModules()->get( static::$moduleId )->config as $entry )
				$list[$entry->key]	= $entry->value;
		$this->moduleConfig	= new Dictionary( $list );
		return $this;
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		boolean		$force			Flag: throw exception if view class is missing, default: yes
	 *	@return		self
	 *	@throws		RuntimeException			in force mode if view class for controller is not existing
	 *	@throws		RuntimeException			if view class is not inheriting Hydrogen view class
	 */
	protected function setupView( bool $force = TRUE ): self
	{
		$name		= str_replace( ' ', '_', ucwords( str_replace( '/', ' ', $this->controller ) ) );
		$class		= self::$prefixView.$name;
		$this->view	= new View( $this->env );
		if( class_exists( $class, TRUE ) ){
			$this->view	= ObjectFactory::createObject( $class, array( &$this->env ) );
			if( !$this->view instanceof View)
				throw new RuntimeException( 'View class is not a Hydrogen view', 301 );
			$this->view->addData( 'moduleConfig', $this->moduleConfig );
		}
		else if( $force )
			throw new RuntimeException( 'View "'.$name.'" is missing', 301 );
		return $this;
	}
}
