<?php
/**
 *	Sends Mails.
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
 *	@package		framework.krypton.core
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			19.02.2007
 *	@version		0.6
 */
/**
 *	Sends Mails.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			19.02.2007
 *	@version		0.6
 *	@deprecated		use Net_Mail_Sender instead
 */
class Framework_Krypton_Core_MailSender
{
	/**	@var	string		$body			Body of Mail */
	protected $body;
	/**	@var	array		$headers		Headers of Mail */
	protected $headers	= array();
	/**	@var	string		$receiver		Receiver Address of Mail */
	protected $receiver;
	/**	@var	string		$subject		Subject of Mail */
	protected $subject;

	/**
	 *	Adds a Header.
	 *	@access		public
	 *	@param		string		$name		Name of Header
	 *	@param		string		$value		Value of Header, empty Vaue will remove a set Header
	 *	@return		void
	 */
	public function addHeader( $name, $value )
	{
		if( $value)
		{
			$name	= $this->formatHeaderName( $name );
			$name	= ucfirst( $name );
			if( eregi( "(\r|\n)", $value ) )
				throw new Exception( "Mail Injection detected." );
			$this->headers[$name]	= $value;
		}
		else
			unset( $this->headers[$name] );
	}

	/**
	 *	Formats Header Name.
	 *	@access		public
	 *	@param		string		$name		Name of Header
	 *	@return		string
	 */
	protected function formatHeaderName( $name )
	{
		$parts	= explode( "-", $name );
		for( $i=0; $i<count( $parts ); $i++ )
			$parts[$i]	= ucfirst( strtolower( $parts[$i] ) );
		$name	= implode( "-", $parts );
		return $name;
	}

	/**
	 *	Sets Mail Body.
	 *	@access		public
	 *	@param		string		$body		Body of Mail
	 *	@return		void
	 */
	public function setBody( $body )
	{
		$this->body	= $body;
	}

	/**
	 *	Sets Receiver Address.
	 *	@access		public
	 *	@param		string		$receiver	Receiver Address of Mail
	 *	@return		void
	 */
	public function setReceiver( $receiver )
	{
		$receiver = urldecode( $receiver );
		if( eregi( "(\r|\n)", $receiver ) )
			throw new Exception( "Mail Injection detected." );
		$this->receiver	= $receiver;
	}

	/**
	 *	Sets Sender Address.
	 *	@access		public
	 *	@param		string		$body		Sender Address of Mail
	 *	@return		void
	 *	@throws		Exception
	 */
	public function setSender( $sender )
	{
		$sender = urldecode( $sender );
		if( eregi( "(\r|\n)", $sender ) )
			throw new Exception( "Mail Injection detected." );
		$this->addHeader( "From", $sender );
	}

	/**
	 *	Sets Mail Subject.
	 *	@access		public
	 *	@param		string		$subject		Subject of Mail
	 *	@return		void
	 */
	public function setSubject( $subject )
	{
		$this->subject	= $subject;
	}

	/**
	 *	Sends Mail.
	 *	@access		public
	 *	@return		bool
	 *	@throws		Exception
	 */
	public function send( $test )
	{
		if( !array_key_exists( "From", $this->headers ) )
			throw new Exception( "No mail sender defined." );
		if( !$this->receiver )
			throw new Exception( "No mail receiver defined." );
		if( !$this->subject )
			throw new Exception( "No mail subject defined." );
		if( !$this->body )
			throw new Exception( "No mail body defined." );

		$headers	= array();
		foreach( $this->headers as $name => $value )
			$headers[]	= $name.":".$value;
		$headers	= implode( "\n", $headers );
		if( $test )
		{
			$message	= time()." <".$this->receiver."> ".$this->subject."\n".$this->body."\n";
			error_log( $message, 3, "logs/mails.log" );
			if( $test == 2 )
				return true;
		}
		if( !mail( $this->receiver, $this->subject, $this->body, $headers ) )
			throw new Exception( "Mail could not been sent." );
		return true;
	}

	/**
	 *	Public Method for PHPUnit-Tests of protected or private Methods.
	 *	@access		public
	 *	@param		string		$method			Name of Method
	 *	@param		mixed		$argument1		Argument 1 of Method (optional)
	 *	@param		mixed		$argument1		Argument 2 of Method (optional)
	 *	@param		mixed		$argument1		Argument 3 of Method (optional)
	 *	@return		mixed
	 */
	public function testMethod( $method, $argument1 = null, $argument2 = null, $argument3 = null )
	{
		return $this->$method( $argument1, $argument2, $argument3 );
	}

	/**
	 *	Public Method for PHPUnit-Tests to get protected or private Members Variables.
	 *	@access		public
	 *	@param		string		$member			Name of Member Variable
	 *	@return		mixed
	 */
	public function testGet( $member )
	{
		return $this->$member;
	}
}
?>