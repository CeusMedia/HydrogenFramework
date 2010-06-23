<?php
/**
 *	Message Output Handler of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
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
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Message Output Handler of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@uses			Alg_Time_Converter
 *	@uses			UI_HTML_Tag
 *	@uses			UI_HTML_Elements
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class Framework_Hydrogen_Messenger
{
	/**	@var		Framework_Hydrogen_Environment	$env			Application Environment Object */
	protected $env;
	/**	@var		array							$classes		CSS Classes of Message Types */
	protected $classes	= array(
		'0'	=> 'failure',
		'1'	=> 'error',
		'2'	=> 'notice',
		'3'	=> 'success',
	);
	
	protected $keyHeadings	= 'messenger_headings';
	protected $keyMessages	= 'messenger_messages';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment	$env			Instance of any Session Handler
	 *	@return		void
	 */
	public function __construct( Framework_Hydrogen_Environment $env  )
	{
		$this->env	= $env;
	}

	/**
	 *	Adds a Heading Text to Message Block.
	 *	@access		public
	 *	@param		string		$heading			Text of Heading
	 *	@return		void
	 */
	public function addHeading( $heading )
	{
		$headings	= $this->env->getSession()->get( $this->keyHeadings );
		if( !is_array( $headings ) )
			$headings	= array();
		$headings[]	= $heading;
		$this->env->getSession()->set( $this->keyHeadings, $headings );
	}
	
	/**
	 *	Build Headings for Message Block.
	 *	@access		public
	 *	@return		string
	 */
	public function buildHeadings()
	{
		$headings	= $this->env->getSession()->get( $this->keyHeadings );
		$heading		= implode( " / ", $headings );
		return $heading;
	}

	/**
	 *	Builds Output for each Message on the Message Stack.
	 *	@access		public
	 *	@return		string
	 */
	public function buildMessages( $timeFormat = NULL, $clear = TRUE )
	{
		$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
		$list		= '';
		if( count( $messages ) )
		{
			$list	= array();
			foreach( $messages as $message )
			{
				$class		= $this->classes[$message['type']];
				$message	= UI_HTML_Tag::create( 'span', $message['message'], array( 'class' => 'message' ) );
				if( $timeFormat && !empty( $message['timestamp'] ) )
				{
					$time		= $message['timestamp'];
					$time		= Alg_Time_Converter::convertToHuman( $time, $timeFormat );
					$time		= '['.$time.'] ';
					$time		= UI_HTML_Tag::create( 'span', $time, array( 'class' => 'time' ) );
					$message	= $time.$message;
				}
				$list[] 	= UI_HTML_Elements::ListItem( $message, 0, array( 'class' => $class ) );
			}
			$list	= UI_HTML_Elements::unorderedList( $list, 0 );
			if( $clear )
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
		$this->env->getSession()->set( $this->keyHeadings, array() );
		$this->env->getSession()->set( $this->keyMessages, array() );
	}

	/**
	 *	Saves a Error Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		$arg1				Argument to be set into Message
	 *	@param		string		$arg2				Argument to be set into Message
	 *	@return		void
	 */
	public function noteError( $message, $arg1 = NULL, $arg2 = NULL )
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
	public function noteFailure( $message, $arg1 = NULL, $arg2 = NULL )
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
	public function noteNotice( $message, $arg1 = NULL, $arg2 = NULL )
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
		$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
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
		$message	= sprintf( $message, (string) $arg1, (string) $arg2 );
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
		$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
		$messages[]	= array( "message" => $message, "type" => $type, "timestamp" => time() );
		$this->env->getSession()->set( $this->keyMessages, $messages );
	}
}
?>
