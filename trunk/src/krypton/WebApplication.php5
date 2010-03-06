<?php
/**
 *	Main Class for Web Applications.
 *	This Class need to be called within an existing Web Project.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.krypton
 *	@extends		Framework_Krypton_Base
 *	@uses			Net_HTTP_Request_Response
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.04.2008
 *	@version		0.1
 */
import( 'de.ceus-media.framework.krypton.Base' );
import( 'de.ceus-media.net.http.request.Response' );
/**
 *	Main Class for Web Applications.
 *	This Class need to be called within an existing Web Project.
 *	@category		cmClasses
 *	@package		framework.krypton
 *	@extends		Framework_Krypton_Base
 *	@uses			Framework_Krypton_Core_FormDefinitionReader
 *	@uses			Framework_Krypton_Core_PageController
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.04.2008
 *	@version		0.1
 */
class Framework_Krypton_WebApplication extends Framework_Krypton_Base
{
	protected $responseHeaders	= array(
		'Cache-Control'	=> "no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0",
		'Pragma'		=> "no-cache"
	);

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		//  --  ENVIRONMENT  --  //
		$this->initRegistry();										//  must be first
		$this->initConfiguration();									//  must be one of the first
		$this->initEnvironment();									//  must be one of the first
#		$this->initCookie();
		$this->initSession();
		$this->initRequest();

		//  --  RESOURCE SUPPORT  --  //
		$this->initDatabase();										//  needs Configuration
		$this->initLanguage();										//  needs Request and Session
		$this->initFormDefinition();								//  needs Configuration
		$this->initThemeSupport();									//  needs Configuration, Request and Session
		$this->initPageController();								//  needs Configuration

		//  --  AUTHENTICATION LOGIC  --  //
		$this->initAuthentication();								//  needs Implementation of Authentication Logic in Project Classes
		$this->initUserInterface();

		ob_start();
		if( !defined( 'CMC_KRYPTON_LIVE_MODE' ) )
			define( 'CMC_KRYPTON_LIVE_MODE', 1 );
	}

	/**
	 *	Sets up Theme Support.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initThemeSupport()
	{
		if( !$this->registry->has( 'config' ) )
			throw new Exception( 'Configuration has not been set up.' );
		if( !$this->registry->has( 'request' ) )
			throw new Exception( 'Request Handler has not been set up.' );
		if( !$this->registry->has( 'session' ) )
			throw new Exception( 'Session Support has not been set up.' );

		$config		= $this->registry->get( "config" );
		$request	= $this->registry->get( "request" );
		$session	= $this->registry->get( "session" );

		if( $config['layout.theme.switchable'] )
		{
			if( $request->has( 'switchThemeTo' ) )
				$session->set( 'theme', $request->get( 'switchThemeTo' ) );
			if( $session->get( 'theme' ) )
				$config['layout.theme'] =  $session->get( 'theme' );
			else
				$session->set( 'theme', $config['layout.theme'] );
		}
		else
			$session->set( 'theme', $config['layout.theme'] );
	}

	/**
	 *	Loads Class of User Interface.
	 *	@access		protected
	 *	@return		void
	 */
	protected function initUserInterface()
	{
		import( 'de.ceus-media.framework.krypton.view.Interface' );
		$this->interface	= new Framework_Krypton_View_Interface;
	}

	/**
	 *	Runs called Actions.
	 *	@access		public
	 *	@return		void
	 */
	public function act()
	{
		$request	= $this->registry->get( "request" );
		$session	= $this->registry->get( "session" );
		$controller	= $this->registry->get( "controller" );
		
		//  --  VALIDATE LINK  --  //
		$link	= $this->validateLink( $request->get( 'link' ) );
		$request->set( 'link', $link );

		//  --  REFERER (=last site)  --  //
		if( $referer = getEnv( 'HTTP_REFERER' ) )
			if( !preg_match( "@(=|&)(log(in|out))|register@i", $referer ) )
				$session->set( 'referer', $referer );

		//  --  ACTION CALL  --  //
		if( !$controller->checkPage( $link ) )
			return;
		if( !$controller->isDynamic( $link ) )
			return;

		$className	= "Action_".$controller->getClassname( $link );
		$fileName	= $this->getFileNameOfClass( $className );
		if( !file_exists( $fileName ) )
			return;

		require_once( $fileName );
		$action	= new $className;
		try
		{
			$action->performBeforeRegisteredActions();
			$action->performActions();
			$action->act();											//  @deprecated
			$action->performAfterRegisteredActions();
		}
		catch( Exception $e )
		{
			$action->handleException( $e );
		}
	}
	
	/**
	 *	Log Performance (Time needed to run Application).
	 *	@access		protected
	 *	@param		int			$length			Number of sent Bytes of Content
	 *	@param		string		$fileName		File Name of Log File
	 *	@return		bool
	 */
	protected function logPerformance( $fileName = "logs/performance.log" )
	{
		$watch	= $this->registry->get( 'stopwatch' );
		$time	= $watch->stop( 6, 0 );
		$line	= time()." [".$time."] ".getEnv( "REQUEST_URI" )." ".getEnv( "HTTP_REFERER" )."\n";
		return error_log( $line, 3, $fileName );
	}

	/**
	 *	Log sent Content Length.
	 *	@access		protected
	 *	@param		int			$length			Number of sent Bytes of Content
	 *	@param		string		$fileName		File Name of Log File
	 *	@return		void
	 */
	protected function logTraffic( $length, $fileName = "logs/traffic.log" )
	{
		$ip		= getEnv( "REMOTE_ADDR" );
		$uri	= getEnv( "REQUEST_URI" );
		$line	= time()." [".$length."] ".$ip." ".$uri."\n";
		error_log( $line, 3, $fileName );
	}

	/**
	 *	Creates Views by called Link and Rights of current User and returns HTML.
	 *	@access		public
	 *	@return		string
	 */
	public function respond()
	{
		$config		= $this->registry->get( "config" );
		$request	= $this->registry->get( "request" );
		$controller	= $this->registry->get( "controller" );
		$messenger	= $this->registry->get( "messenger" );
		$words		= $this->registry->get( "words" );

		$extra		= "";
		$field		= "";
		$control	= "";
		$content	= "";
		$link		= $request->get( 'link' );

		if( !$content )
		{
			if( $controller->checkPage( $link ) )
			{
				if( $controller->isDynamic( $link ) )
				{
					$className	= "View_".$controller->getClassname( $link );
					$fileName	= $this->getFileNameOfClass( $className );
					if( file_exists( $fileName ) )
					{
						require_once( $fileName );
						$view		= new $className;
						if( !CMC_KRYPTON_MODE && method_exists( $view, 'handleException' ) )
						{
							try
							{
								$content	= $view->buildContent();
								$control	.= $view->buildControl();
								$extra		.= $view->buildExtra();
							}
							catch( Exception $e )
							{
								$view->handleException( $e, 'main', 'exceptions' );
							}
						}
						else
						{
							$content	= $view->buildContent();
							$control	.= $view->buildControl();
							$extra		.= $view->buildExtra();
						}
					}
					else
						$messenger->noteFailure( "Class '".$className."' is not existing." );
				}
				else
				{
					$source	= $controller->getSource( $link );
					if( $this->interface->hasContent( $source ) )
					{
						$content	= $this->interface->loadContent( $source );
						if( method_exists( $this->interface, 'setTitleByLink' ) )
							$this->interface->setTitleByLink( $link );
						if( method_exists( $this->interface, 'setKeywordsByLink' ) )
							$this->interface->setKeywordsByLink( $link );
						if( method_exists( $this->interface, 'setDescriptionByLink' ) )
							$this->interface->setDescriptionByLink( $link );
					}
					else
						$messenger->noteFailure( str_replace( "#URI#", $source, $words['main']['msg']['error_no_content'] ) );
				}
			}
		}

		if( isset( $GLOBALS['length'] ) )
		{
			remark( "<b>Class Length: </b>".$GLOBALS['length']['total'] );
			arsort( $GLOBALS['length']['classes'] );
			print_m( array_flip( $GLOBALS['length']['classes'] ) );
		}

		$dev		= ob_get_clean();
		$content	= $this->interface->buildInterface( $content, $control, $extra, $dev );
		$length		= $this->respondContent( $content );
		$this->logRemarks( $dev );
		$this->logTraffic( strlen( $content ) );
		$this->logPerformance();
	}

	/**
	 *	Sends HTTP Response and returns Length of sent Content.
	 *	@access		protected
	 *	@param		string		$content		HTML to respond
	 *	@return		int
	 */
	protected function respondContent( $content )
	{
		$config		= $this->registry->get( 'config' );
		$zipMethod	= $config['config.http_compression'];
		$zipLogFile	= $config['config.http_compression_log'];
		$response	= new Net_HTTP_Request_Response();
		foreach( $this->responseHeaders as $key => $value )
			$response->addHeader( $key, $value );			
		$response->write( $content );
		return $response->send( $zipMethod, $zipLogFile );
	}

	/**
	 *	Validate requested Link (can be overwritten for another Validation).
	 *	@access		protected
	 *	@param		string		$link		Requested Link to validate
	 *	@return		string
	 */
	protected function validateLink( $link )
	{
		$auth		= $this->registry->get( "auth" );
		if( !( $link && $auth->hasAccessToPage( $link ) ) )
			return $auth->getFirstAccessiblePage();
		return $link;
	}
}
?>