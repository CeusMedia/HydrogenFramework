<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Application class for an MVC website.
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
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Application\Web;

use CeusMedia\Common\Net\HTTP\Header\Field as HttpHeaderField;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\OutputBuffer;
use CeusMedia\HydrogenFramework\ApplicationInterface;
use CeusMedia\HydrogenFramework\Application\WebAbstraction;
use CeusMedia\HydrogenFramework\Dispatcher\General as GeneralDispatcher;
use CeusMedia\HydrogenFramework\Environment\Resource\Database\PDO;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use Exception;
use Throwable;

/**
 *	Application class for an MVC website.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class Site extends WebAbstraction implements ApplicationInterface
{
	public static bool $checkClassActionArguments	= TRUE;

	/**	@var		string			$content		Collected Content to respond */
	protected string $content		= '';

	protected ?OutputBuffer	$devBuffer	= NULL;

	/**
	 *	General main application method.
	 *	You can copy and modify this method in your application to handle exceptions your way.
	 *	NOTE: You need to execute $this->respond( $this->main() ) in order to start dispatching, controlling and rendering.
	 *	@access		public
	 *	@return		int|NULL
	 */
	public function run(): ?int
	{
		$this->devBuffer	= new OutputBuffer();

		$displayErrors	= $this->env->getConfig()->get( 'system.display.errors' );				//  get error mode from config
		$displayErrors	= is_null( $displayErrors ) || $displayErrors;								//  if not set: enable error display by default
		error_reporting( $displayErrors ? E_ALL : 0 );										//  set error reporting
		try{
			$this->respond( $this->main() );														//	send rendered result of dispatched controller action
			$this->logOnComplete();																	//  handle logging after responding
			$this->env->close();																	//  teardown environment and quit application execution
			return 0;
		}
		catch( Exception $e ){
			HtmlExceptionPage::display( $e );
			return 1;
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	Executes called Controller and stores generated View.
	 *	@access		protected
	 *	@param		string|NULL		$defaultController	Controller if none is set and not 'index'
	 *	@param		string|NULL		$defaultAction		Action if none is set and not 'index'
	 *	@return		string			Content generated by view triggered by controller
	 *	@throws		Exception		if an exception is caught and neither error view not messenger is available
	 *	@todo		handle exception by hook call "App@onDispatchException", see below
	 */
	protected function control( ?string $defaultController = NULL, ?string $defaultAction = NULL ): string
	{
		$request	= $this->env->getRequest();
		$captain	= $this->env->getCaptain();
		$captain->callHook( 'App', 'onControl', $this );
		$output		= '';
		try{
			$payload	= ['content' => NULL];
			$captain->callHook( 'App', 'onDispatch', $this, $payload );
			if( is_string( $payload['content'] ) && strlen( trim( $payload['content'] ) ) )
				return $payload['content'];
			$dispatcher	= new GeneralDispatcher( $this->env );
			$dispatcher->checkClassActionArguments	= self::$checkClassActionArguments;
			if( $defaultController )
				$dispatcher->defaultController	= $defaultController;
			if( $defaultAction )
				$dispatcher->defaultAction		= $defaultAction;
			$output	= $dispatcher->dispatch( $this->devBuffer );									//  get requested content
			$this->setViewComponents( [																//  note for main template
				'controller'	=> $request->get( '__controller' ),							//  called controller
				'action'		=> $request->get( '__action' )									//  called action
			] );
		}
/*		catch( ErrorException $e ){
			if( getEnv( 'HTTP_HOST' ) ){
				if( $this->env->getModules()->has( 'ErrorException' ) ){
					$view	= new View_ErrorException( $this->env );
					return $view->handle( $e );
				}
				return HtmlExceptionPage::render( $e );
			}
			else{
				print( $e->getMessage().PHP_EOL );
				print( $e->getTraceAsString().PHP_EOL.PHP_EOL );
				exit;
			}
		}*/
		catch( Exception $e ){
			$captain	= $this->env->getCaptain();
			$payload	= ['exception' => $e];
			$captain->callHook( 'App', 'onException', $this, $payload );

			if( $this->env->getRequest()->has( 'showException' ) ){									//  @todo: you need to secure this view by a configurable run mode etc.
				$this->env->getResponse()->setBody( HtmlExceptionPage::render( $e ) );			//  fill response with exception page
				$this->env->getResponse()->setStatus( 500 );										//  indicate HTTP status 500 - internal server error
				$this->env->getResponse()->send();													//  send response
				exit;																				//  and quit
			}
			else if( $this->env->getMessenger() ){
				$this->env->getMessenger()->noteFailure( $e->getMessage() );						//  fill messenger with exception message
				$this->env->getResponse()->setStatus( 500 );										//  indicate HTTP status 500 - internal server error
				$controller	= trim( $request->get( '__controller' ) );
				if( strlen( $controller ) && $controller !== 'index' ){								//  a controller has been set
					header( 'Location: '.$this->env->getBaseUrl() );								//  redirect to home
					exit;																			//  and quit
				}
				$this->env->getResponse()->setBody( 'Error: '.$e->getMessage() );					//  fill response with exception page
				$this->env->getResponse()->send();													//  send response
				exit;																				//  and quit
			}
//			throw new RuntimeException( "Unhandled exception: ".$e->getMessage(), 0, $e );			//  last call: throw exception with unhandled exception nested
		}
		return $output;																				//  return generated output
	}

	/**
	 *	Main Method of Framework calling Controller (and View) and Master View.
	 *	@access		protected
	 *	@return		string
	 *	@todo		use UI_OutputBuffer
	 *	@throws		Exception
	 */
	protected function main(): string
	{
		$request	= $this->env->getRequest();

		$data		= [
			'page'		=> $this->env->getPage(),													//  HTML
			'config'	=> $this->env->getConfig(),													//  configuration object
			'request'	=> $request,																//  request object
		];
		if( $this->env->has( 'messenger' ) )
			$data['messenger']	= $this->env->getMessenger();										//  UI messages for user

		if( $this->env->has( 'language' ) ){													//  language support is available
			$language			= $this->env->getLanguage();
			$data['language']	= $language->getLanguage();											//  note document language
			$data['words']		= $language->getWords( 'main', FALSE, FALSE );		//  note main UI word pairs
		}
		try{
			$content	= $this->control();															//  dispatch and run request
			if( $request->isAjax() || $request->has( '__contentOnly' ) )						//  this is an AJAX request
				return $content;																	//  deliver content only

			$data['content']	= $content;															//  rendered response page view content
			$templateFile		= 'master.php';
			$templateHook		= 'getMasterTemplate';
		}
		catch( Throwable $e ){
			$this->env->getLog()?->logException( $e, $this );
			$data['e']			= $e;
			$templateFile		= 'error.php';
			$templateHook		= 'getErrorTemplate';
		}

		if( $this->env->has( 'database' ) ){													//  database support is available
			/** @var PDO $database */
			$database	= $this->env->getDatabase();
			$data['dbQueries']		= $database->numberExecutes;									//  note number of SQL queries executed
			$data['dbStatements']	= $database->numberStatements;									//  note number of SQL statements sent
		}

		$this->setViewComponents( array_merge( $data, [												//  set up information resources for main template
			'runtime'		=> $this->env->getRuntime(),											//  system clock for performance measure
			'clock'			=> $this->env->getRuntime(),											//  legacy: alias for runtime @todo remove
			'dev'			=> $this->devBuffer?->get(),											//  warnings, notices or development messages
		] ) );
		$this->devBuffer?->close();

		return $this->view( $templateFile, $templateHook );											//  render and return main template to constructor
	}

	/**
	 *	Simple implementation of content response. Can be overridden for special moves.
	 *	@access		public
	 *	@param		string		$body		Response content body
	 *	@param		array		$headers	List of additional headers to be set on response
	 *	@return		object		Map of final response and number of sent bytes (members: bytesSent, compression, response)
	 *	@todo		use UI_OutputBuffer
	 */
	protected function respond( string $body, array $headers = [] ): object
	{
		$response	= $this->env->getResponse();
		if( NULL !== $this->devBuffer && $this->devBuffer->isOpen() ){
			$body	= $this->devBuffer->get().$body;
			$this->devBuffer->close();
		}
		if( $body )
			$response->setBody( $body );

		foreach( $headers as $key => $value ){
			if( $value instanceof HttpHeaderField )
				$response->addHeader( $value );
			else
				$response->addHeaderPair( $key, $value );
		}

		$compression	= NULL;
//		$encodings		= $this->env->getRequest()->headers->getField( 'Accept-Encoding' );
//		$isAjax			= $this->env->request->isAjax();
		$nrBytes		= $response->send( $compression, TRUE, FALSE );
		return (object) [
			'bytesSent'		=> $nrBytes,
			'compression'	=> $compression,
			'response'		=> $response,
		];
	}
}
