<?php
/**
 *	Shows an Exception and quits.
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
 *	@since			22.04.2007
 *	@version		0.4
 */
import( 'de.ceus-media.ui.DevOutput' );
import( 'de.ceus-media.ui.Template' );
/**
 *	Shows an Exception and quits.
 *	@category		cmClasses
 *	@package		framework.xenon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@uses			Net_Mail_PlainMail
 *	@uses			Net_Mail_Sender
 *	@uses			UI_Template
 *	@since			22.04.2007
 *	@version		0.4
 */
class Framework_Xenon_ExceptionHandler
{
	/**	@var		string			$errorPage		File Name of Error Page Template */
	public static $errorPage		= "";

	/**	@var		string			$mail			Mail Address to send Report to */
	public static $mailReceiver		= "";

	/**	@var		string			$template		File Name of Template to load */
	public static $template			= "contents/templates/exception.html";

	/**	@var		string			$template		File Name of Template to load */
	public static $logPath			= "";

	public static $logDbErrors		= NULL;
	public static $logDbStatements	= NULL;
	

	/**
	 *	Constructur, shows Exception and quits.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( Exception $e = NULL )
	{
		if( $e )
			die( self::handleException( $e ) );
	}
	
	/**
	 *	Builds Report from Exception.
	 *	@access		protected
	 *	@static
	 *	@param		Exception		$e				Exception to handle
	 *	@param		string			$logPath		Path to write Report to, disabled if empty
	 *	@return		string
	 */
	protected static function buildReport( $e )
	{
		$dev	= "";
		while( ob_get_level() )
			$dev	.= ob_get_clean();
		$uiData	= array(
			'exception.type'	=> get_class( $e ),
			'exception.message'	=> $e->getMessage(),
			'exception.file'	=> $e->getFile(),
			'exception.line'	=> $e->getLine(),
			'exception.trace'	=> nl2br( $e->getTraceAsString() ),
			'log.db.errors'		=> self::$logDbErrors ? self::dumpLog( self::$logDbErrors,  5, "No errors logged." ) : "Reporting disabled.",
			'log.db.queries'	=> self::$logDbStatements ? self::dumpLog( self::$logDbStatements, 5, "No queries logged." ) : "Reporting disabled.",
			'resource.remarks'	=> $dev ? $dev : "No remarks.",
			'resource.request'	=> !empty( $_REQUEST ) ? self::dumpResource( $_REQUEST ) : "No request set.",
			'resource.session'	=> !empty( $_SESSION ) ? self::dumpResource( $_SESSION ) : "No session opened.",
		);			
		$template	= new UI_Template( self::$template, $uiData );
		return $template->create();
	}
	
	/**
	 *	Builds List of Log Lines.
	 *	@access		protected
	 *	@static
	 *	@param		string			$fileName		File Name of Log File
	 *	@param		int				$limit			Amount of Lines from the end
	 *	@param		string			$default		String to return if List is empty
	 *	@return		void
	 */
	protected static function dumpLog( $fileName, $limit, $default = "" )
	{
		if( !file_exists( $fileName ) )																//  File not existing
			return $default;																		//  return default Message if set
		$content	= trim( file_get_contents( $fileName ) );										//  load Log Content
		$lines		= explode( "\n", $content );													//  split in Lines
		if( !$lines )																				//  Log is empty
			return $default;																		//  return default Message if set
		if( $limit )																				//  a Limit is set
			$lines	= array_slice( $lines, -1 * abs( $limit ) );									//  reduce Lines to Limit
		$function	= create_function( '$matches', 'return date( "Y-m-d H:i:s", $matches[1] );' );	//  create Callback Function
		foreach( $lines as $nr => $line )															//  iterate Lines
			$lines[$nr]	= preg_replace_callback( "@^([0-9]{10,12})@", $function, $line );			//  replace Timestamp by Datetime
		return implode( "<br/>", $lines );															//  glue Lines to List an return
	}

	/**
	 *	Builds List of Resource Parameters.
	 *	@access		protected
	 *	@param		array			$resource		Resource Array
	 *	@return		void
	 */
	protected function dumpResource( $resource )
	{
		ob_start();
		print_m( $resource );
		return preg_replace( "@^<br/>@", "", ob_get_clean() );
	}
	
	/**
	 *	Handles Exception by creating, storing and mailing a Report and showing the Report or a static Error Page.
	 *	@access		public
	 *	@static
	 *	@param		Exception		$e				Exception to handle
	 *	@param		string			$logPath		Path to write Report to, disabled if empty
	 *	@return		string
	 */
	public static function handleException( $e )
	{
		$report	= self::buildReport( $e );
		self::logReport( $e, $report );
		self::mailReport( $e, $report );
		
		if( self::$errorPage )
		{
			if( !file_exists( self::$errorPage ) )
				throw new Exception( 'Template "'.self::$errorPage.'" is not existing.' );
			$report	= require_once( self::$errorPage );
		}
		return $report;
	}

	/**
	 *	Saves Report in Log Path and returns number of written Byt.
	 *	@access		protected
	 *	@static
	 *	@param		string			$report			Report to save
	 *	@param		string			$logPath		Path to write Report to
	 *	@return		int
	 */
	protected static function logReport( $e, $report )
	{
		if( !self::$logPath )
			return 0;
		$fileName	= date( "ymdHis" )."_".getEnv( 'REMOTE_ADDR' ).".html";
		$fileUrl	= self::$logPath.$fileName;
		if( !file_exists( self::$logPath ) )
			mkdir( self::$logPath, 0700, TRUE );
		return file_put_contents( $fileUrl, $report );
	}
	
	/**
	 *	Saves Report in Log Path and returns number of written Byt.
	 *	@access		protected
	 *	@static
	 *	@param		Exception		$e				Exception to handle
	 *	@param		string			$report			Report to save
	 *	@return		bool
	 */
	protected static function mailReport( $e, $report )
	{
		if( !self::$mailReceiver )
			return FALSE;
		import( 'de.ceus-media.net.mail.PlainMail' );
		import( 'de.ceus-media.net.mail.Sender' );
		$mail	= new Net_Mail_PlainMail();
		$mail->setSender( self::$mailReceiver );
		$mail->setReceiver( self::$mailReceiver );
		$mail->setSubject( "[".getEnv( 'HTTP_HOST' )."] Exception: ".$e->getMessage() );
		$mail->setBody( $report );
		try
		{
			return @Net_Mail_Sender::sendMail( $mail );
		}
		catch( Exception $e )
		{
			remark( "<b>Warning! </b>Report Mail has not been sent." );
			return FALSE;
		}
	}
}
?>