<?php
/**
 *	Main Class of Motrada V2 to realize Actions and build Views.
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
 *	@extends		Framework_Xenon_Base
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.12.2006
 *	@version		0.3
 */
import( 'de.ceus-media.framework.xenon.Base' );
/**
 *	Main Class of Motrada V2 to realize Actions and build Views.
 *	@category		cmClasses
 *	@package		framework.xenon
 *	@extends		Framework_Xenon_Base
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			11.12.2006
 *	@version		0.3
 */
class Framework_Xenon_ConsoleApplication extends Framework_Xenon_Base
{
	/**	@var	Core_Registry	$registry		Instance of Core_Registry */
	protected $registry		= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		bool		$verbose		Flag: print Information to Console
	 *	@return		void
	 */
	public function __construct( $verbose = TRUE )
	{
		if( getEnv( 'HTTP_HOST' ) )
			die( '<b>Access denied:</b> This is a console application.' );
#		self::$configFile	= "config.xml";
		$this->initRegistry();							//  must be first
		$this->initConfiguration();						//  must be one of the first
		$this->initEnvironment();						//  must be one of the first
		$this->initDatabase();
		$this->initRequest();
		$this->initLanguage( FALSE );
		self::evaluateArguments();
		self::run( $verbose );
	}

	/**
	 *	Evaluates Arguments.
	 *	@access		protected
	 *	@return		void
	 */
	protected function evaluateArguments()
	{
		//  --  SHOW SYNTAX  --  //
		$request	= Framework_Xenon_Core_Registry::getStatic( 'request' );
		if( count( $request->getAll() ) < 3 )
			die( "Syntax:  runJob <class> [parameter]\nExample: runJob Job_Test session" );
		$request->set( 'class', $request->get( 1 ) );
		$request->set( 'command', $request->get( 'cmd' ) );
		$request->remove( 0 );
		$request->remove( 1 );
		$request->remove( 'cmd' );
	}

	/**
	 *	Runs Job Class as Console Application.
	 *	@access		protected
	 *	@static
	 *	@param		bool		$verbose		Flag: print Information to Console
	 *	@return		void
	 */
	protected static function run( $verbose = TRUE )
	{
		$request	= Framework_Xenon_Core_Registry::getStatic( 'request' );
		$className	= $request->get( 'class' );
		$command	= $request->get( 'command' );

		//  --  RUN JOB  --  //
		try
		{
			$classPath	= self::getPathNameOfClass( $className );
			import( 'classes.'.$classPath );
			$job		= new $className();

			if( $verbose )
				remark( "Now running: ".$className );

			$result		= $job->run( $command );
			error_log( time()." ".$className."[".$command."]: ".(string)$result."\n", 3, LOG_JOBS );
		}
		catch( Exception $e )
		{
			self::logException( $e, $className, $verbose );
		}
		if( $verbose )
			echo "\n";
	}

	/**
	 *	Logs Exception Information into Log File.
	 *	@access		protected
	 *	@param		Exception	$e				Exception to log
	 *	@param		string		$className		Name of Job Class
	 *	@param		bool		$verbose		Flag: print Information to Console
	 *	@return		void
	 */
	protected function logException( $e, $className, $verbose = TRUE )
	{
		$trace		= serialize( $e->getTrace() );
		$file		= $e->getFile();
		$line		= $e->getLine();
		$message	= $e->getMessage();
		error_log( time()."|".$className."(".$file."[".$line."]):".$message."|trace:".$trace."\n", 3, LOG_JOBERROR );
		if( $verbose )
			remark( "Unhandled Exception catched. See Job Error Log Reader or ".LOG_JOBERROR."." );
	}
}
?>