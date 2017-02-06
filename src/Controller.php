<?php
/**
 *	Generic Controller Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2016 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Generic Controller Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Controller
{
	public static $prefixModel		= "Model_";
	public static $prefixView		= "View_";
	public $alias					= "";

	const RESTART_FROM_IGNORE		= 0;
	const RESTART_FROM_POP			= 1;
	const RESTART_FROM_APPLY		= 2;
	const RESTART_FROM_CARRY		= 4;
	const RESTART_FROM_SET			= 8;
	const RESTART_FROM_PUSH			= 16;

	/**	@var		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object */
	protected $env;
	/**	@var		string								$defaultPath	Default controller URI path */
	protected $defaultPath;
	/**	@var		string								$path			Preferred controller URI path */
	protected $path;
	/**	@var		CMF_Hydrogen_View					$view			View instance for controller */
	protected $view;
	/**	@var		array								$_data			Collected Data for View */
	protected $_data									= array();

	/**	@var		string								$controller		Name of called Controller */
	protected $controller		= "";
	/**	@var		string								$action			Name of called Action */
	protected $action			= "";
	/**	@var		bool								$redirect		Flag for Redirection */
	var $redirect				= FALSE;

	/**
	 *	Constructor.
	 *	Will set up related view class by default. Disable this for controllers without views.
	 *	Calls __onInit() in the end.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object
	 *	@param		boolean								$setupView		Flag: auto create view object for controller (default: TRUE)
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env, $setupView = TRUE )
	{
		$env->clock->profiler->tick( 'CMF_Controller('.get_class( $this ).')' );
		$this->setEnv( $env );
//		$env->clock->profiler->tick( 'CMF_Controller('.get_class( $this ).'): env set' );
		if( !( $env instanceof CMF_Hydrogen_Environment_Console ) && $setupView )
			$this->setupView( !$env->getRequest()->isAjax() );
		$env->clock->profiler->tick( 'CMF_Controller('.get_class( $this ).'): got view object' );

		$moduleClassName	= preg_replace( "/^Controller_/", "", get_class( $this ) );				//  cut controller class name ...
		$this->defaultPath	= strtolower( str_replace( '_', '/', $moduleClassName ) );				//  to guess default controller URI path
		$this->path			= $this->defaultPath;													//  and note this as controller path

		$captain			= $this->env->getCaptain();												//  prepare hook call
		$data				= array( 'moduleClassName' => $moduleClassName );						//  with cut controller class name
		if( $path = $captain->callHook( 'Controller', 'onDetectPath', $this, $data ) )				//  to get preferred controller URI path
			$this->path		= $path;																//  and set if has been resolved

//		$arguments		= array_slice( func_get_args(), 1 );										//  collect additional arguments for extended logic classes
//		Alg_Object_MethodFactory::callObjectMethod( $this, '__onInit', $arguments, TRUE, TRUE );	//  invoke possibly extended init method
		$this->__onInit();																			//  default callback for construction end
		$env->clock->profiler->tick( 'CMF_Controller('.get_class( $this ).'): done' );				//  log time of construction
	}

	/**
	 *	Empty method which is called after construction and can be customised.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(){}

	protected function addData( $key, $value, $topic = NULL )
	{
		return $this->view->setData( array( $key => $value ), $topic );
	}

	protected function callHook( $resource, $event, $context = NULL, $data = array() ){
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
	protected function checkAjaxRequest(){
		if( !$this->env->getRequest()->isAjax() ){
			$this->messenger->noteFailure( 'Invalid AJAX/AJAJ access attempt.' );
			$this->restart( NULL, FALSE, 401 );
		}
	}

	protected function compactFilterInput( $input ){
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
	 *	@return		array
	 */
	protected function getData( $key = NULL )
	{
		return $this->view->getData( $key );
	}

	public function getView(){
		return $this->view;
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string		$section	Section in locale file
	 *	@param		string		$topic		Locale file key, eg. test/my, default: current controller
	 *	@return		void
	 */
	protected function getWords( $section = NULL, $topic = NULL ){
		if( empty( $topic ) && $this->env->getLanguage()->hasWords( $this->controller ) )
			$topic = $this->controller;
		if( empty( $section ) )
			return $this->env->getLanguage()->getWords( $topic );
		return $this->env->getLanguage()->getSection( $topic, $section );
	}

	protected function handleJsonResponse( $status, $data, $httpStatusCode = NULL ){
		$type			= $status;
		$httpStatusCode	= $httpStatusCode ? $httpStatusCode : 200;
		if( in_array( $status, array( TRUE, "data", "success", "succeeded" ), TRUE ) )
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
			print( "Headers already sent." );
		}
		else{
			header( "HTTP/1.1 ".$httpStatusCode." ".Net_HTTP_Status::getText( $httpStatusCode ) );
			header( "Content-Type: application/json" );
			header( "Content-Length: ".strlen( $json ) );
			print( $json );
		}
		exit;
	}

	protected function handleJsonErrorResponse( $data, $httpStatusCode = NULL ){
		$this->handleJsonResponse( 'error', $data, $httpStatusCode );
	}

	/**
	 *	Redirects by calling different Controller and Action.
	 *	Attention: This will *NOT* effect the URL in browser nor need cURL requests to allow forwarding.
	 *	Attention: This is not recommended, please user restart in favour.
	 *	@access		protected
	 *	@param		string		$controller		Controller to be called, default: index
	 *	@param		string		$action			Action to be called, default: index
	 *	@param		array		$parameters		Map of additional parameters to set in request
	 *	@return		void
	 */
	protected function redirect( $controller = 'index', $action = "index", $arguments = array(), $parameters = array() )
	{
		$request	= $this->env->getRequest();
		$request->set( 'controller', $controller );
		$request->set( 'action', $action );
		$request->set( 'arguments', $arguments );
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
	protected function relocate( $uri, $status = NULL ){
		$this->restart( $uri, FALSE, $status, TRUE );
	}

	/**
	 *	Returns View Content of called Action.
	 *	@access		public
	 *	@return		string
	 */
	public function renderView()
	{
		$this->env->clock->profiler->tick( 'Controller::getView: start' );
		if( !$this->view )
			throw new RuntimeException( 'No view object created in Constructor' );
		if( !method_exists( $this->view, $this->action ) )
			throw new RuntimeException( 'View Action "'.$this->action.'" not defined yet', 302 );
		$language		= $this->env->getLanguage();
		$this->env->clock->profiler->tick( 'Controller::getView: got language' );
		if( $language->hasWords( $this->controller ) )
			$this->view->setData( $language->getWords( $this->controller ), 'words' );
		$this->env->clock->profiler->tick( 'Controller::getView: set words' );
		$result			= Alg_Object_MethodFactory::callObjectMethod( $this->view, $this->action );
		if( is_string( $result ) ){
			$this->env->clock->profiler->tick( 'Controller::getView: Action called' );
		}
		else if( $this->view->hasTemplate( $this->controller, $this->action ) ){
			$result	= $this->view->loadTemplate( $this->controller, $this->action );
			$this->env->clock->profiler->tick( 'Controller::getView: loadTemplate' );
		}
		else if( $this->view->hasContent( $this->controller, $this->action, 'html/' ) ){
			$result	= $this->view->loadContent( $this->controller, $this->action, NULL, 'html/' );
			$this->env->clock->profiler->tick( 'Controller::getView: loadContent' );
		}
		else{
			$message	= 'Neither view template nor content file defined for request path "%s/%s"';
			throw new RuntimeException( sprintf( $message, $this->controller, $this->action ) );
		}
		$this->env->clock->profiler->tick( 'Controller::getView: done' );
		return $result;
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
	 *	@param		string		$withinModule		Flag: user path inside current controller
	 *	@param		integer		$status				HTTP status code to send, default: NULL -> 200
	 *	@param		boolean		$allowForeignHost	Flag: allow redirection outside application base URL, default: no
	 *	@param		integer		$modeFrom			How to handle FROM parameter from request or for new request, not handled atm
	 *	@return		void
	 *	@link		https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection HTTP status codes
	 *	@todo		kriss: implement automatic lookout for "from" request parameter
	 *	@todo		kriss: implement handling of FROM request parameter, see controller constants
	 *	@todo		kriss: concept and implement anti-loop {@see http://dev.(ceusmedia.de)/cmKB/?MTI}
	 */
	protected function restart( $uri, $withinModule = FALSE, $status = NULL, $allowForeignHost = FALSE, $modeFrom = 0 )
	{
		$base	= "";
		if( !preg_match( "/^http/", $uri ) ){														//  URI is not starting with HTTP scheme
			$base	= $this->env->getBaseUrl();														//  get application base URI
			if( $withinModule ){																	//  redirection is within module
				$controller	= $this->env->getRequest()->get( 'controller' );						//  get current controller
				$base	.= $this->alias ? $this->alias : $controller;								//
				$base	.= strlen( $uri ) ? '/' : '';												//
			}
		}
		if( !$allowForeignHost ){																	//  redirect to foreign domain not allowed
			$hostFrom	= parse_url( 'http://'.getEnv( 'HTTP_HOST' ), PHP_URL_HOST );				//  current host domain
			$hostTo		= parse_url( $base.$uri, PHP_URL_HOST );									//  requested host domain
			if( $hostFrom !== $hostTo ){															//  both are not matching
				$message	= 'Redirection to foreign host is not allowed.';						//  error message
				if( $this->env->has( 'messenger' ) ){												//  messenger is available
					$this->env->getMessenger()->noteFailure( $message );							//  note message
					$this->restart( NULL, FALSE, NULL, TRUE );															//  redirect to start
				}
				print( $message );																	//  otherwise print message
				exit;																				//  and exit
			}
		}
	#	$this->dbc->close();																		//  close database connection
	#	$this->session->close();																	//  close session
		if( $status )																				//  a HTTP status code is to be set
			Net_HTTP_Status::sendHeader( (int) $status );											//  send HTTP status code header
		header( "Location: ".$base.$uri );															//  send HTTP redirect header

		$link	= UI_HTML_Tag::create( 'a', $base.$uri, array( 'href' => $base.$uri ) );
		$text	= UI_HTML_Tag::create( 'small', 'Redirecting to '.$link.' ...' );
		$page	= new UI_HTML_PageFrame();
		$page->addMetaTag( 'http-equiv', 'refresh', '0; '.$base.$uri );
		$page->addBody( $text );
		print( $page->build() );
		exit;																						//  and exit application
	}

	/**
	 *
	 *	Sets Data for View.
	 *	@access		protected
	 *	@param		array		$data			Array of Data for View
	 *	@param		string		[$topic]			Topic Name of Data
	 *	@return		void
	 */
	protected function setData( $data, $topic = "" )
	{
		if( $this->view )
			$this->view->setData( $data, $topic );
		return;
		if( !is_array( $data ) )
			throw new InvalidArgumentException( 'Must be array' );
		if( is_string( $topic) && !empty( $topic ) )
		{
			if( !isset( $this->_data[$topic] ) )
				$this->_data[$topic]	= array();
			foreach( $data as $key => $value )
				$this->_data[$topic][$key]	= $value;
		}
		else
		{
			foreach( $data as $key => $value )
				$this->_data[$key]	= $value;
		}
	}

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Framework Resource Environment Object
	 *	@return		void
	 */
	protected function setEnv( CMF_Hydrogen_Environment_Abstract &$env )
	{
		$this->env			= $env;
		$this->controller	= $env->getRequest()->get( 'controller' );
		$this->action		= $env->getRequest()->get( 'action' );
		if( $this->env->has( 'language' ) && $this->controller )
		{
			$language	= $this->env->getLanguage();
			$language->load( $this->controller, FALSE, FALSE );
		}
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@return		void
	 */
	protected function setupView( $force = TRUE )
	{
		$name		= str_replace( ' ', '_', ucwords( str_replace( '/', ' ', $this->controller ) ) );
		$class		= self::$prefixView.$name;
		$this->view	= new CMF_Hydrogen_View( $this->env );
		if( class_exists( $class, TRUE ) )
			$this->view	= Alg_Object_Factory::createObject( $class, array( &$this->env ) );
		else if( $force )
			throw new RuntimeException( 'View "'.$name.'" is missing', 301 );
	}
}
?>
