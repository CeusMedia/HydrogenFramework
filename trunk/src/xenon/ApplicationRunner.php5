<?php
/**
 *	Runs an Xenon Application within a Container secured by an Exception Handler for uncatched Exceptions.
 *	Furthermore all PHP Messages (Warnings, Notices etc) can be converted to Exceptions.
 *	If the Exception Handling fails itself, the resulting Exception with be shown with an Exception Trace Viewer.
 *	This class is abstract and needs to be extended in run().
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
 *	@package		framework.xenon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			29.03.2009
 *	@version		0.1
 */
/**
 *	Runs an Xenon Application within a Container secured by an Exception Handler for uncatched Exceptions.
 *	Furthermore all PHP Messages (Warnings, Notices etc) can be converted to Exceptions.
 *	If the Exception Handling fails itself, the resulting Exception with be shown with an Exception Trace Viewer.
 *	This class is abstract and needs to be extended in run().
 *
 *	@category		cmClasses
 *	@package		framework.xenon
 *	@abstract
 *	@uses			File_Configuration_Reader
 *	@uses			Framework_Xenon_ExceptionHandler
 *	@uses			Framework_Xenon_FatalExceptionHandler
 *	@uses			UI_HTML_Exception_TraceViewer
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			29.03.2009
 *	@version		0.1
 */
abstract class Framework_Xenon_ApplicationRunner
{
	public static $configKeyDatabaseLogPath			= 'database.log.path';
	public static $configKeyDatabaseLogErrors		= 'database.log.errors';
	public static $configKeyDatabaseLogStatements	= 'database.log.statements';
	public static $configKeyLanguages				= 'languages.allowed';
	public static $configKeyPathHtml				= 'paths.html';
	public static $errorTemplate					= 'errors/error.html';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		bool		$initErrorHandler	Set alternative PHP Error Handler in initErrorHandler().
	 *	@return		void
	 */
	public function __construct( $initErrorHandler = FALSE )
	{
		try
		{
			if( $initErrorHandler )
				$this->initErrorHandler();
			$this->run();
		}
		catch( Exception $e )
		{
			$this->handleException( $e );
		}
	}

	/**
	 *	Handles uncatched Exceptions with Xenon's Exception Handler (or FatalExceptionHandler below cmClasses 0.6.6).
	 *	If the Exception Handling fails itself, the resulting Exception with be shown with an Exception Trace Viewer.
	 *	Attention: Needs defined constants set by Xenon's Base::loadConstants.	
	 *	@access		protected
	 *	@param		Exception	$e					Exception to handle.
	 *	@return		void
	 */
	protected function handleException( Exception $e )
	{
		throw $e;
		$isConsole	= !getEnv( 'HTTP_HOST' );
		try
		{
			import( 'de.ceus-media.file.configuration.Reader' );
			import( 'de.ceus-media.net.http.LanguageSniffer' );

			import( 'de.ceus-media.framework.xenon.ExceptionHandler' );
			if( defined( 'CMC_XENON_CONFIG_PATH' ) )
			{
				$configPath	= defined( 'CMC_XENON_CONFIG_PATH' ) ? CMC_XENON_CONFIG_PATH : "config/";		//  get Path of Configuration File from System Constants (config/constansts.ini)
				$configFile	= defined( 'CMC_XENON_CONFIG_FILE' ) ? CMC_XENON_CONFIG_FILE : "config.ini";	//  get Name of Configuration File from System Constants (config/constansts.ini)
				$config		= new File_Configuration_Reader( $configPath.$configFile, NULL );

				if( $config->has( self::$configKeyDatabaseLogErrors ) )											//  Database Error Log is defined
				{
					$logPath	= $config[self::$configKeyDatabaseLogPath];										//  get Path of Log from Configuration
					$logUri		= $config[self::$configKeyDatabaseLogErrors];									//  get Name of Log from Configuration
					Framework_Xenon_ExceptionHandler::$logDbErrors		= $logPath.$logUri;					//  set Log in Exception Handler
				}
				if( $config->has( self::$configKeyDatabaseLogStatements ) )
				{
					$logPath	= $config[self::$configKeyDatabaseLogPath];
					$logUri		= $config[self::$configKeyDatabaseLogStatements];
					Framework_Xenon_ExceptionHandler::$logDbStatements	= $logPath.$logUri;
				}

				if( defined( 'CMC_EXCEPTION_MAIL_TO' ) && CMC_EXCEPTION_MAIL_TO )
				{
					Framework_Xenon_ExceptionHandler::$mailReceiver		= CMC_EXCEPTION_MAIL_TO;
				}

				if( defined( 'CMC_EXCEPTION_LOG_PATH' ) && CMC_EXCEPTION_LOG_PATH )
				{
					Framework_Xenon_ExceptionHandler::$logPath			= CMC_EXCEPTION_LOG_PATH;
				}
				if( !defined( 'CMC_XENON_MODE' ) || !CMC_XENON_MODE && !$isConsole )	
				{
					$languages	= $config[self::$configKeyLanguages];
					$languages	= explode( ",", $languages );
					$language	= Net_HTTP_LanguageSniffer::getLanguage( $languages );
					$template	= $config[self::$configKeyPathHtml].$language."/".self::$errorTemplate;
					Framework_Xenon_ExceptionHandler::$errorPage		= $template;
				}
			}
			$report	= Framework_Xenon_ExceptionHandler::handleException( $e );
			if( !$isConsole )
				die( $report );
			
			$message	= $e->getMessage();
			if( $e->getCode() )
				$message	.=" (Code:".$e->getCode().")";
			die( "\nException: ".$message );

		}
		catch( Exception $e )
		{
			import( 'de.ceus-media.ui.html.exception.TraceViewer' );
			new UI_HTML_Exception_TraceViewer( $e );
		}
	}

	/**
	 *	Sets an alternative PHP Error Handler, either with a Lambda Function or a Callback Function.
	 *	@access		protected
	 *	@param		bool		$lambda				Set a Lambda Function as Callback Function.
	 *	@return		void
	 */
	protected function initErrorHandler( $lamba = FALSE )
	{
		if( $lamba )
		{
			$function	= create_function( '$a, $b, $c, $d', 'throw new ErrorException($b, 0, $a, $c, $d);' );
			set_error_handler( $function, E_ALL );
		}
		else
			set_error_handler( array( $this, 'throwErrorException' ), E_ALL );
	}

	/**
	 *	Runs Xenon Application itself and needs to be overwritten with the pure execution lines.
	 *	@abstract
	 *	@access		protected
	 *	@return		void
	 */
	abstract protected function run();

	/**
	 *	Callback Function for PHP Error Handler set in initErrorHandler().
	 *	Converts every PHP Message to an Error Exception and throws it.
	 *	@access		public
	 *	@param		int			$errno				Error Number
	 *	@param		string		$errstr				Error Message
	 *	@param		string		$errfile			File of Error
	 *	@param		int			$errline			Line in File
	 *	@return		void
	 */
	public function throwErrorException( $errno, $errstr, $errfile, $errline )
	{
		throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
	}
}
?>