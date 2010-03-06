<?php
/**
 *	Message Output Handler of Framework Hydrogen.
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
 *	@package		framework.hydrogen
 *	@extends		ADT_OptionObject
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.5
 */
import( 'de.ceus-media.adt.OptionObject' );
import( 'de.ceus-media.alg.time.Converter' );
/**
 *	Message Output Handler of Framework Hydrogen.
 *	@category		cmClasses
 *	@package		framework.hydrogen
 *	@extends		ADT_OptionObject
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.5
 */
class Framework_Hydrogen_Messenger extends ADT_OptionObject
{
	/**	@var		Net_HTTP_PartitionSession		$session			Instance of any Session Handler */
	protected $session;
	/**	@var		array							$classes			CSS Classes of Message Types */
	protected $classes	= array(
		'0'	=> 'failure',
		'1'	=> 'error',
		'2'	=> 'notice',
		'3'	=> 'success',
	);

	/**
	 *	Constructor.
	 *	Needs Session Handler of Net_HTTP_PartitionSession.
	 *	@access		public
	 *	@param		Net_HTTP_PartitionSession		$session			Instance of any Session Handler
	 *	@param		string							$key_messages		Key of Messages within Session
	 *	@return		void
	 */
	public function __construct( Net_HTTP_PartitionSession $session, $key_messages = "messenger_messages" )
	{
		parent::__construct();
		$this->setOption( 'key_headings', "messenger_headings" );
		$this->setOption( 'key_messages', $key_messages );
		$this->setOption( 'heading_separator', " / " );
		$this->session	= $session;
	}

	/**
	 *	Adds a Heading Text to Message Block.
	 *	@access		public
	 *	@param		string		$heading			Text of Heading
	 *	@return		void
	 */
	public function addHeading( $heading )
	{
		$headings	= $this->session->get( $this->getOption( 'key_headings' ) );
		if( !is_array( $headings ) )
			$headings	= array();
		$headings[]	= $heading;
		$this->session->set( $this->getOption( 'key_headings' ), $headings );
	}
	
	/**
	 *	Build Headings for Message Block.
	 *	@access		public
	 *	@return		string
	 */
	public function buildHeadings()
	{
		$headings	= $this->session->get( $this->getOption( 'key_headings' ) );
		$heading		= implode( $this->getOption( 'heading_separator' ), $headings );
		return $heading;
	}

	/**
	 *	Builds Output for each Message on the Message Stack.
	 *	@access		public
	 *	@return		string
	 */
	public function buildMessages( $format_time = false, $auto_clear = true )
	{
		$tc		= new Alg_Time_Converter;
		$messages	= (array) $this->session->get( $this->getOption( 'key_messages' ) );
		$list	= "";
		if( count( $messages ) )
		{
			$list	= array();
			foreach( $messages as $message )
			{
				$time	= $message['timestamp'] ? "[".$tc->convertToHuman( $message['timestamp'], $format_time )."] " : "";
				$class	= $this->classes[$message['type']];
				$list[] = "<div class='".$class."'><span class='info'>".$time."</span><span class='message'>".$message['message']."</span></div>";
			}
			$list	= "<div id='list'>".implode( "\n", $list )."</div>";
			if( $auto_clear )
				$this->clear();
		}
		return $list;
	}
	
	/**
	 *	Clears stack of Messages.
	 *	@access		public
	 *	@return		void
	 */
	public function clear()
	{
		$this->session->set( $this->getOption( 'key_headings' ), array() );
		$this->session->set( $this->getOption( 'key_messages' ), array() );
	}

	/**
	 *	Saves a Error Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		$arg1				Argument to be set into Message
	 *	@param		string		$arg2				Argument to be set into Message
	 *	@return		void
	 */
	public function noteError( $message, $arg1 = false, $arg2 = false )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( 1, $message);
	}

	/**
	 *	Saves a Failure Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		$arg1				Argument to be set into Message
	 *	@param		string		$arg2				Argument to be set into Message
	 *	@return		void
	 */
	public function noteFailure( $message, $arg1 = false, $arg2 = false )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( 0, $message);
	}
	
	/**
	 *	Saves a Notice Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		$arg1				Argument to be set into Message
	 *	@param		string		$arg2				Argument to be set into Message
	 *	@return		void
	 */
	public function noteNotice( $message, $arg1 = false, $arg2 = false )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( 2, $message);
	}
	
	/**
	 *	Saves a Success Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		$arg1				Argument to be set into Message
	 *	@param		string		$arg2				Argument to be set into Message
	 *	@return		void
	 */
	public function noteSuccess( $message, $arg1 = false, $arg2 = false )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( 3, $message);
	}
	
	/**
	 *	Indicates wheteher an Error or a Failure has been reported.
	 *	@access		public
	 *	@return		bool
	 */
	public function gotError()
	{
		foreach( $messages as $message )
			if( $message['type'] < 2 )
				return true;
		return false;
	}

	//  --  PRIVATE METHODS
	/**
	 *	Inserts arguments into a Message.
	 *	@access		protected
	 *	@param		string		$message			Message to display
	 *	@param		string		$arg1				Argument to be set into Message
	 *	@param		string		$arg2				Argument to be set into Message
	 *	@return		string
	 */
	protected function setIn( $message, $arg1, $arg2 )
	{
		if( $arg2 )
			$message	= preg_replace( "@(.*)\{\S+\}(.*)\{\S+\}(.*)@si", "$1".$arg1."$2".$arg2."$3", $message );
		else if( $arg1 )
			$message	= preg_replace( "@(.*)\{\S+\}(.*)@si", "$1###".$arg1."###$2", $message );
//		$message		= preg_replace( "@\{\S+\}@i", "", $message );
		$message		= str_replace( "###", "", $message );
		return $message;
	}
	
	/**
	 *	Saves a Message on the Message Stack.
	 *	@access		protected
	 *	@param		int			$type				Message Type (0-Failure|1-Error|2-Notice|3-Success)
	 *	@param		string		$message			Message to display
	 *	@return		void
	 */
	protected function noteMessage( $type, $message)
	{
		$messages	= (array) $this->session->get( $this->getOption( 'key_messages' ) );
		$messages[]	= array( "message" => $message, "type" => $type, "timestamp" => time() );
		$this->session->set( $this->getOption( 'key_messages' ), $messages );
	}
}
?>