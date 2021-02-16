<?php
/**
 *	Message Output Handler of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2021 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Message Output Handler of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@uses			Alg_Time_Converter
 *	@uses			UI_HTML_Tag
 *	@uses			UI_HTML_Elements
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Messenger
{
	/**	@var		CMF_Hydrogen_Environment	$env			Application Environment Object */
	protected $env;

	/**	@var		boolean						$enabled		Flag: store messages in session */
	protected $enabled;

	/**	@var		array						$classes		CSS Classes of Message Types */
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
	 *	@param		CMF_Hydrogen_Environment		$env			Instance of any Session Handler
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env, bool $enabled = TRUE )
	{
		$this->env		= $env;
		$this->enabled	= $enabled;
	}

	/**
	 *	Adds a Heading Text to Message Block.
	 *	@access		public
	 *	@param		string		$heading			Text of Heading
	 *	@return		self
	 */
	public function addHeading( string $heading ): self
	{
		$headings	= $this->env->getSession()->get( $this->keyHeadings );
		if( !is_array( $headings ) )
			$headings	= array();
		$headings[]	= $heading;
		$this->env->getSession()->set( $this->keyHeadings, $headings );
		return $this;
	}

	/**
	 *	Build Headings for Message Block.
	 *	@access		public
	 *	@return		string
	 */
	public function buildHeadings(): string
	{
		$headings	= $this->env->getSession()->get( $this->keyHeadings );
		$heading		= implode( " / ", $headings );
		return $heading;
	}

	/**
	 *	Builds Output for each Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$timeFormat		Date string to format message timestamp with
	 *	@param		bool		$clear			Flag: clear stack in session after rendering
	 *	@param		bool		$linkResources	Flag: try to link resources in message
	 *	@return		string
	 */
	public function buildMessages( string $timeFormat = NULL, bool $clear = TRUE, bool $linkResources = FALSE ): string
	{
		$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
		$list		= '';
		$ids		= array();
		if( count( $messages ) ){
			$list	= array();
			foreach( $messages as $message ){
				if( $linkResources )																//  @todo	kriss: check what is the point of this? where is it used? can it be removed?
					$message['message']	= preg_replace( '/(http.+)("|\'| )/U', '<a href="\\1">\\1</a>\\2', $message['message'] );

				/*  --  kriss: don't repeat yourself!  --  */
				/*  (avoid dubplicate messages which were collected during several redirects)  */
				$id	= md5( json_encode( array( $message['type'], $message['message'] ) ) );			//  calculate message ID
				if( in_array( $id, $ids ) )															//  ID has been calculated before
					continue;																		//  skip this duplicate message
				$ids[]	= $id;																		//  note calculated ID

				$class		= $this->classes[$message['type']];
				$message	= UI_HTML_Tag::create( 'span', $message['message'], array( 'class' => 'message' ) );
				if( $timeFormat && !empty( $message['timestamp'] ) ){
					$time		= $message['timestamp'];
					$time		= Alg_Time_Converter::convertToHuman( $time, $timeFormat );
					$time		= '['.$time.'] ';
					$time		= UI_HTML_Tag::create( 'span', $time, array( 'class' => 'time' ) );
					$message	= $time.$message;
				}
				if( $this->env->getModules()->has( 'UI_JS_Messenger' ) ){
					$button		= UI_HTML_Tag::create( "div", '<span></span>', array(
						'class'		=> 'button discard',
						'onclick'	=> "UI.Messenger.discardMessage($(this).parent());",
						'alt'		=> 'ausblenden',
						'title'		=> 'ausblenden',
					 ) );
					$message	= $message.$button;
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

	public function enable( bool $yesOrNo ): self
	{
		$this->enabled	= $yesOrNo;
		return $this;
	}

	public function getMessages(): array
	{
		return (array) $this->env->getSession()->get( $this->keyMessages );
	}

	/**
	 *	Indicates wheteher an Error or a Failure has been noted.
	 *	@access		public
	 *	@return		integer		Number of noted errors or failures
	 */
	public function gotError(): int
	{
		$count		= 0;
		$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
		foreach( $messages as $message )
			if( $message['type'] < 2 )
				$count++;
		return $count;
	}

	/**
	 *	Saves a Error Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		[$argX]*			Arguments to be set into Message
	 *	@return		void
	 */
	public function noteError( string $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->applyParametersToMessage( func_get_args() );
		$this->noteMessage( 1, $message);
	}

	/**
	 *	Saves a Failure Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		[$argX]*			Arguments to be set into Message
	 *	@return		void
	 */
	public function noteFailure( string $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->applyParametersToMessage( func_get_args() );
		$this->noteMessage( 0, $message);
	}

	/**
	 *	Saves a Notice Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		[$argX]*			Arguments to be set into Message
	 *	@return		void
	 */
	public function noteNotice( string $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->applyParametersToMessage( func_get_args() );
		$this->noteMessage( 2, $message);
	}

	/**
	 *	Saves a Success Message on the Message Stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		string		[$argX]*			Arguments to be set into Message
	 *	@return		void
	 */
	public function noteSuccess( string $message, $arg1 = NULL, $arg2 = NULL )
	{
		$message	= $this->applyParametersToMessage( func_get_args() );
		$this->noteMessage( 3, $message );
	}

	//  --  PROTECTED  --  //

	/**
	 *	Inserts arguments into a Message.
	 *	@access		protected
	 *	@param		array		$arguments			List with message and parameters to apply using sprintf
	 *	@return		string		Resulting message or original message if insufficient parameters
	 */
	protected function applyParametersToMessage( array $arguments ): string
	{
		if( count( $arguments ) > 1 ){
			foreach( $arguments as $nr => $argument )
				if( $nr )
					$arguments[$nr]	= htmlentities( $argument, ENT_QUOTES, 'UTF-8' );
			$function	= new ReflectionFunction( 'sprintf' );
			$message	= $function->invokeArgs( $arguments );
		}
		else
			$message	= array_shift( $arguments );
		return $message;
	}

	/**
	 *	Saves a Message on the Message Stack.
	 *	@access		protected
	 *	@param		int			$type				Message Type (0-Failure|1-Error|2-Notice|3-Success)
	 *	@param		string		$message			Message to display
	 *	@return		void
	 */
	protected function noteMessage( int $type, string $message )
	{
		if( $this->enabled ){
			if( is_array( $message ) || is_object( $message ) || is_resource( $message ) )
				throw new InvalidArgumentException( 'Message must be a string or numeric' );
			$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
			$messages[]	= array( "message" => $message, "type" => $type, "timestamp" => time() );
			$this->env->getSession()->set( $this->keyMessages, $messages );
		}
	}
}
