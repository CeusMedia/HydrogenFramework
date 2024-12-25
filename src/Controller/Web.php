<?php /** @noinspection PhpUnused */
/** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Generic Controller Class of Framework Hydrogen.
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

namespace CeusMedia\HydrogenFramework\Controller;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Obj\MethodFactory as MethodFactory;
use CeusMedia\Common\Alg\Text\CamelCase as CamelCase;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Dispatcher\General;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Model;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\HydrogenFramework\View;
use DateTimeInterface;
use DomainException;
use Exception;
use JsonException;
use ReflectionException;
use RuntimeException;

/**
 *	Generic Controller Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class Web extends Abstraction
{
	public const RESTART_FROM_IGNORE		= 0;
	public const RESTART_FROM_POP			= 1;
	public const RESTART_FROM_APPLY			= 2;
	public const RESTART_FROM_CARRY			= 4;
	public const RESTART_FROM_SET			= 8;
	public const RESTART_FROM_PUSH			= 16;


	/**	@var	WebEnvironment				$env			Application Environment Object */
	protected WebEnvironment $env;

	/**	@var	View|NULL					$view			View instance for controller */
	protected ?View $view					= NULL;

	/**	@var	bool						$redirect		Flag for Redirection */
	public bool $redirect					= FALSE;

	/**	@var	string						$defaultPath	Default controller URI path */
	protected string $defaultPath;

	/**	@var	string						$path			Preferred controller URI path */
	protected string $path;

	/**
	 *	Constructor.
	 *	Will set up related view class by default. Disable this for controllers without views.
	 *	Calls __onInit() in the end.
	 *	@access		public
	 *	@param		WebEnvironment						$env			Application Environment Object
	 *	@param		boolean								$setupView		Flag: auto create view object for controller (default: TRUE)
	 *	@return		void
	 *	@throws		RuntimeException
	 *	@throws		ReflectionException
	 */
	public function __construct( WebEnvironment $env, bool $setupView = TRUE )
	{
		$env->getRuntime()->reach( 'Controller('.static::class.')' );
		static::$moduleId	= trim( static::$moduleId );
		$this->setEnv( $env );

//		$env->getRuntime()->reach( 'Controller('.static::class.'): env set' );
		if( $setupView )
//			$this->setupView( !$env->getRequest()->isAjax() );
			$this->setupView( FALSE );
		$env->getRuntime()->reach( 'Controller('.static::class.'): got view object' );

		/** @var string $controllerName */
		$controllerName		= preg_replace( "/^Controller_/", "", static::class );				//  get controller name from class name
		$this->defaultPath	= strtolower( str_replace( '_', '/', $controllerName ) );				//  to guess default controller URI path
		$this->path			= $this->defaultPath;													//  and note this as controller path

		$data				= ['controllerName' => $controllerName, 'path' => ''];					//  with cut controller name
		$this->callHook( 'Controller', 'onDetectPath', $this, $data );								//  to get preferred controller URI path
		if( strlen( trim( $data['path'] ) ) !== 0 )
			$this->path		= $data['path'];														//  and set if has been resolved

//		$arguments		= array_slice( func_get_args(), 1 );										//  collect additional arguments for extended logic classes
//		Alg_Object_MethodFactory::callObjectMethod( $this, '__onInit', $arguments, TRUE, TRUE );	//  invoke possibly extended init method
		try{
			$this->__onInit();																		//  default callback for construction end
		}
		catch( Exception $e ){
			$payload	= ['exception' => $e];
			$this->callHook( 'App', 'onException', $this, $payload );
			throw new RuntimeException( $e->getMessage(), (int) $e->getCode(), $e );
		}
		$env->getRuntime()->reach( 'Controller('.static::class.'): done' );				//  log time of construction
	}

	public function getView(): ?View
	{
		return $this->view;
	}

	/**
	 *	Returns View Content of called Action.
	 *	@access		public
	 *	@return		string
	 *	@throws		RuntimeException			if no view has been set up
	 *	@throws		RuntimeException
	 *	@throws		RuntimeException
	 *	@throws		ReflectionException
	 */
	public function renderView(): string
	{
		$this->env->getRuntime()->reach( 'Controller::getView: start' );
		$language		= $this->env->getLanguage();
		$this->env->getRuntime()->reach( 'Controller::getView: got language' );

		$result	= NULL;
		if( NULL !== $this->view ){
			if( $language->hasWords( $this->controller ) )
				$this->view->setData( $language->getWords( $this->controller ), 'words' );
			$this->env->getRuntime()->reach( 'Controller::getView: set words' );

			if( $this->view::class !== View::class ){
				if( !method_exists( $this->view, $this->action ) )
					throw new RuntimeException( 'View Action "'.$this->action.'" not defined yet', 302 );
				$factory	= new MethodFactory( $this->view, $this->action );
				$result		= $factory->call();
			}
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
		}

		$this->env->getRuntime()->reach( 'Controller::getView: done' );
		return $result ?? '';
	}

	//  --  PROTECTED  --  //

	/**
	 *	Magic function called at the end of construction.
	 *	Override to implement custom resource construction.
	 *
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(): void
	{
	}

	/**
	 *	Checks if current request came via AJAX.
	 *	Otherwise, returns application main page as HTML by redirection.
	 *	Also notes a failure message in UI messenger.
	 *	@access		public
	 *	@return		void
	 *	@todo		locale support (use main.ini section msg etc.)
	 */
	protected function checkAjaxRequest(): void
	{
		if( !$this->env->getRequest()->isAjax() ){
			$this->env->getMessenger()?->noteFailure( 'Invalid AJAX/AJAJ access attempt.' );
			$this->restart( NULL, FALSE, 401 );
		}
	}

	/**
	 *	@param		mixed|NULL		$input
	 *	@return		mixed|null
	 *	@todo		remove if not used, purpose unclear
	 */
	protected function compactFilterInput( mixed $input ): mixed
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
	protected function getData( string $key = NULL, string $fallback = NULL ): mixed
	{
		return $this->view?->getData( $key, $fallback );
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
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 */
	protected function getLogic( string $key ): Logic
	{
//		if( is_null( $key ) || !strlen( trim( $key ) ) )
//			return $this->env->getLogic();
		/** @var Logic $logic */
		$logic	= $this->env->getLogic()->get( $key );
		return $logic;
	}

	/**
	 *	Tries to find model class for short model key and returns instance.
	 *	@access		protected
	 *	@param		string		$key		Key for model class (eG. 'mailGroupMember' for 'Model_Mail_Group_Member')
	 *	@return		Model					Model instance
	 *	@throws		RuntimeException		if no model class could be found for given short model key
	 *	@throws		ReflectionException
	 *	@todo		create model pool environment resource and apply to created shared single instances instead of new instances
	 *	@see		duplicate code with Logic::getModel
	 */
	protected function getModel( string $key ): Model
	{
		if( preg_match( '/^[A-Z][A-Za-z0-9_]+$/', $key ) )
			$className	= self::$prefixModel.$key;
		else{
			$classNameWords	= ucwords( CamelCase::decode( $key ) );
			$className		= str_replace( ' ', '_', 'Model '.$classNameWords );
		}
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Model class "'.$className.'" not found' );
		/** @var Model $model */
		$model	= ObjectFactory::createObject( $className, [$this->env] );
		return $model;
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string|NULL		$section	Section in locale file
	 *	@param		string|NULL		$topic		Locale file key, e.g. test/my, default: current controller
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

	/**
	 *	@param		string|bool		$status
	 *	@param		mixed			$data
	 *	@param		int|null		$httpStatusCode
	 *	@return		void
	 *	@throws		JsonException
	 */
	protected function handleJsonResponse(string|bool $status, mixed $data, ?int $httpStatusCode = NULL ): void
	{
		$type			= $status;
		$httpStatusCode	= $httpStatusCode ?: 200;
		if( in_array( $status, [TRUE, 'data', 'success', 'succeeded'], TRUE ) )
			$type	= "data";
		else if( in_array( $status, [FALSE, 'error', 'fail', 'failed'], TRUE ) )
			$type	= "error";
		$response	= (object) [
			'code'		=> $httpStatusCode,
			'status'	=> $type,
			'data'		=> $data,
			'timestamp'	=> microtime( TRUE ),
		];
		$json	= json_encode( $response, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR );
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

	/**
	 *	@param		mixed			$data
	 *	@param		int|null		$httpStatusCode
	 *	@return		void
	 *	@throws		JsonException
	 */
	protected function handleJsonErrorResponse( mixed $data, ?int $httpStatusCode = NULL ): void
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
	protected function redirect( string $controller = 'index', string $action = "index", array $arguments = [], array $parameters = [] ): void
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
	 *	@param		string			$uri			URI to request, may be external
	 *	@param		integer|NULL	$status			HTTP status code to send, default: NULL -> 200
	 *	@return		void
	 *	@todo		check for better HTTP status
	 */
	protected function relocate( string $uri, ?int $status = NULL ): void
	{
		$this->restart( $uri, FALSE, $status, TRUE );
	}

	/**
	 *	Redirects by requesting a URI.
	 *	Attention: This *WILL* effect the URL displayed in browser / need request clients (eG. cURL) to allow forwarding.
	 *
	 *	By default, redirect URIs are a request path within the current application, e.g. "./[CONTROLLER]/[ACTION]"
	 *	ATTENTION: For browser compatibility local paths should start with "./"
	 *
	 *	If seconds parameter is set to TRUE, redirects to a path inside the current controller.
	 *	Therefore, the given URI needs to be a path inside the current controller.
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
	 *	@param		string|NULL		$uri				URI to request
	 *	@param		boolean			$withinModule		Flag: user path inside current controller
	 *	@param		integer|NULL	$status				HTTP status code to send, default: NULL -> 200
	 *	@param		boolean			$allowForeignHost	Flag: allow redirection outside application base URL, default: no
	 *	@param		integer			$modeFrom			How to handle FROM parameter from request or for new request, not handled atm
	 *	@return		void
	 *	@link		https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection HTTP status codes
	 *	@todo		implement automatic lookout for "from" request parameter
	 *	@todo		implement handling of FROM request parameter, see controller constants
	 *	@todo		concept and implement anti-loop {@see http://dev.(ceusmedia.de)/cmKB/?MTI}
	 */
	protected function restart( ?string $uri = NULL, bool $withinModule = FALSE, ?int $status = NULL, bool $allowForeignHost = FALSE, int $modeFrom = 0 ): void
	{
		$mode	= 'ext';
		if( !str_starts_with( $uri ?? '', 'http' ) ){														//  URI is not starting with HTTP scheme
			$mode	= 'int';
			if( $withinModule ){																	//  redirection is within module
				$mode	= 'mod';
				$controller	= $this->env->getRequest()->get( '__controller', '' );				//  get current controller
				$controller	= $this->alias ?: $controller;							//
				$uri		= $controller.( strlen( $uri ?? '' ) ? '/'.$uri : '' );						//
			}
		}
		if( $this->logRestarts )
			error_log( vsprintf( '%s %s %s %s'."\n", [
				date( DateTimeInterface::ATOM ),
				$status ?: 200,
				$mode,
				$uri
			] ), 3, 'logs/restart.log' );
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
		$this->view?->setData($data, $topic);
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
			$language->load( $this->controller );
		}
		//  load module configuration
		$this->moduleConfig	= new Dictionary();
		if( strlen( static::$moduleId ) && $env->getModules()->has( static::$moduleId ) ){
			/** @var ModuleDefinition $module */
			$module	= $env->getModules()->get( static::$moduleId );
			$this->moduleConfig	= $module->getConfigAsDictionary();
		}
		return $this;
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		boolean		$force			Flag: throw exception if view class is missing, default: yes
	 *	@return		static						Self controller instance, possibly extended by a view class instance
	 *	@throws		RuntimeException			in force mode if view class for controller is not existing
	 *	@throws		RuntimeException			if view class is not inheriting Hydrogen view class
	 *	@throws		ReflectionException
	 */
	protected function setupView( bool $force = TRUE ): static
	{
		$this->view = new View( $this->env );
		$this->view->addData( 'moduleConfig', $this->moduleConfig );

		$firstGuessOrInstance	= General::getPrefixedClassInstanceByPathOrFirstClassNameGuess(
			$this->env,
			self::$prefixView,
			$this->controller
		);
		if( is_object( $firstGuessOrInstance ) ){
			$instance	= $firstGuessOrInstance;
			if( !$instance instanceof View )
				throw new RuntimeException( 'View class "'.$instance::class.'" is not a Hydrogen view', 301 );
			$this->view = $instance;
			return $this;
		}
		if( $force )
			throw new RuntimeException( 'View "'.$firstGuessOrInstance.'" is missing' );
        return $this;
	}
}
