<?php
/**
 *	Message Output Handler within a Session.
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
 *	@package		framework.xenon.core
 *	@uses			Core_Registry
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.7
 */
import( 'de.ceus-media.framework.xenon.core.Registry' );
import( 'de.ceus-media.alg.TimeConverter' );
/**
 *	Message Output Handler within a Session.
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@uses			Core_Registry
 *	@uses			Alg_Time_Converter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.7
 */
class Framework_Xenon_Core_Messenger
{
	const TYPE_FAILURE		= 0;
	const TYPE_ERROR		= 1;
	const TYPE_NOTICE		= 2;
	const TYPE_SUCCESS		= 3;
	
	/**	@var	object			$registry			Registry for Objects */
	protected $registry;

	/**	@var	array			$classes			CSS Classes of Message Types */
	protected $classes	= array(
		'0'	=> 'failure',
		'1'	=> 'error',
		'2'	=> 'notice',
		'3'	=> 'success',
	);

	/**	@var		string		$headingSeparator	Separator of Headings */
	public $headingSeparator	= " / ";

	/**	@var		string		$keyHeadings		Key of Headings within Session */
	public $keyHeadings			= "messenger_headings";

	/**	@var		string		$keyMessages		Key of Messages within Session */
	public $keyMessages			= "messenger_messages";

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$keyMessages		Key of Messages within Session
	 *	@return		void
	 */
	public function __construct( $keyMessages = NULL )
	{
		if( !empty( $keyMessages ) )
			$this->keyMessages			= $keyMessages;
	}

	/**
	 *	Adds a Heading Text to Message Block.
	 *	@access		public
	 *	@param		string		$heading			Text of Heading
	 *	@return		void
	 */
	public function addHeading( $heading )
	{
		$session	= Framework_Xenon_Core_Registry::getStatic( 'session' );
		$headings	= $session->get( $this->keyHeadings );
		if( !is_array( $headings ) )
			$headings	= array();
		$headings[]	= $heading;
		$session->set( $this->keyHeadings, $headings );
	}
	
	/**
	 *	Build Headings for Message Block.
	 *	@access		public
	 *	@return		string
	 */
	public function buildHeadings()
	{
		$session	= Framework_Xenon_Core_Registry::getStatic( 'session' );
		$headings	= $session->get( $this->keyHeadings );
		$heading	= implode( $this->headingSeparator, $headings );
		return $heading;
	}

	/**
	 *	Builds Output for each Message on the Message Stack.
	 *	@access		public
	 *	@return		string
	 *	@todo		create HTML with UI_HTML_* or use Templates
	 */
	public function buildMessages( $autoClear = TRUE )
	{
		$config		= Framework_Xenon_Core_Registry::getStatic( 'config' );
		$session	= Framework_Xenon_Core_Registry::getStatic( 'session' );
		$tc			= new Alg_Time_Converter;
		$messages	= (array) $session->get( $this->keyMessages );

		$list		= "";
		if( count( $messages ) )
		{
			$list	= array();
			foreach( $messages as $message )
			{
				$time	= $message['timestamp'] ? "[".$tc->convertToHuman( $message['timestamp'], $config['layout.format.timestamp'] )."] " : "";
				$class	= $this->classes[$message['type']];
				$list[] = "<div class='".$class."'><span class='info'>".$time."</span><span class='message'>".$message['message']."</span></div>";
			}
			$list	= "<div class='messageList'>".implode( "\n", $list )."</div>";
			if( $autoClear )
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
		$session	= Framework_Xenon_Core_Registry::getStatic( 'session' );
		$session->set( $this->keyHeadings, array() );
		$session->set( $this->keyMessages, array() );
	}
	
	/**
	 *	Adds a Message of a given Message Type to the Message Stack.
	 *	@access		public
	 *	@param		int			$type			Message Type (0-Failure|1-Error|2-Notice|3-Success)
	 *	@param		string		$message		Message to display
	 *	@param		string		$arg1			Argument to be set into Message
	 *	@param		string		$arg2			Argument to be set into Message
	 *	@return		void
	 */
	public function note( $type, $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( $type, $message);
	}
	
	/**
	 *	Adds an Error Message to the Message Stack.
	 *	@access		public
	 *	@param		string		$message		Message to display
	 *	@param		string		$arg1			Argument to be set into Message
	 *	@param		string		$arg2			Argument to be set into Message
	 *	@return		void
	 */
	public function noteError( $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( 1, $message);
	}

	/**
	 *	Adds a Failure Message to the Message Stack.
	 *	@access		public
	 *	@param		string		$message		Message to display
	 *	@param		string		$arg1			Argument to be set into Message
	 *	@param		string		$arg2			Argument to be set into Message
	 *	@return		void
	 */
	public function noteFailure( $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( 0, $message);
	}
	
	/**
	 *	Adds a Message to the Message Stack.
	 *	@access		protected
	 *	@param		int			$type			Message Type (0-Failure|1-Error|2-Notice|3-Success)
	 *	@param		string		$message		Message to display
	 *	@return		void
	 */
	protected function noteMessage( $type, $message )
	{
		$session	= Framework_Xenon_Core_Registry::getStatic( 'session' );
		$messages	= (array) $session->get( $this->keyMessages );
		$messages[]	= array( "message" => $message, "type" => $type, "timestamp" => time() );
		$session->set( $this->keyMessages, $messages );
	}
	
	/**
	 *	Adds a Notice Message to the Message Stack.
	 *	@access		public
	 *	@param		string		$message		Message to display
	 *	@param		string		$arg1			Argument to be set into Message
	 *	@param		string		$arg2			Argument to be set into Message
	 *	@return		void
	 */
	public function noteNotice( $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->setIn( $message, $arg1, $arg2 );
		$this->noteMessage( 2, $message);
	}
	
	/**
	 *	Adds a Success Message to the Message Stack.
	 *	@access		public
	 *	@param		string		$message		Message to display
	 *	@param		string		$arg1			Argument to be set into Message
	 *	@param		string		$arg2			Argument to be set into Message
	 *	@return		void
	 */
	public function noteSuccess( $message, $arg1 = NULL, $arg2 = NULL )
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
		$session	= Framework_Xenon_Core_Registry::getStatic( 'session' );
		$messages	= (array) $session->get( $this->keyMessages );
		foreach( $messages as $message )
			if( $message['type'] < 2 )
				return TRUE;
		return FALSE;
	}

	/**
	 *	Inserts arguments into a Message, supports printf and own Pattern 'a {1} b {2} c'.
	 *	@access		protected
	 *	@param		string		$message		Message to display
	 *	@param		string		$arg1			Argument to be set into Message
	 *	@param		string		$arg2			Argument to be set into Message
	 *	@return		string
	 *	@todo		remove own Pattern in 0.6.7
	 */
	protected function setIn( $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= sprintf( $message, (string) $arg1, (string) $arg2 );
		
		if( $arg2 !== NULL )
			$message	= preg_replace( "@(.*)\{\S+\}(.*)\{\S+\}(.*)@si", "$1".$arg1."$2".$arg2."$3", $message );
		else if( $arg1 !== NULL )
			$message	= preg_replace( "@(.*)\{\S+\}(.*)@si", "$1###".$arg1."###$2", $message );
//		$message		= preg_replace( "@\{\S+\}@i", "", $message );
		$message		= str_replace( "###", "", $message );
		return $message;
	}
}
?>