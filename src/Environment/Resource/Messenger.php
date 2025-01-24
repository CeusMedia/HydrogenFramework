<?php
/**
 *	Message Output Handler of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2025 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\Common\Alg\Time\Converter as TimeConverter;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment as WebEnvironment;

use ReflectionFunction;

/**
 *	Message Output Handler of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Messenger
{
	/**	@var		WebEnvironment	$env			Application Environment Object */
	protected WebEnvironment $env;

	/**	@var		boolean			$enabled		Flag: store messages in session */
	protected bool $enabled;

	/**	@var		array			$classes		CSS classes of message types */
	protected array $classes	= [
		'0'	=> 'failure',
		'1'	=> 'error',
		'2'	=> 'notice',
		'3'	=> 'success',
	];

	protected string $keyHeadings	= 'messenger_headings';

	protected string $keyMessages	= 'messenger_messages';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment	$env			Instance of any session handler
	 *	@return		void
	 */
	public function __construct( WebEnvironment $env, bool $enabled = TRUE )
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
			$headings	= [];
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
		return implode( " / ", $headings );
	}

	/**
	 *	Builds output for each message on the message stack.
	 *	@access		public
	 *	@param		string|NULL		$timeFormat		Date string to format message timestamp with
	 *	@param		bool			$clear			Flag: clear stack in session after rendering
	 *	@param		bool			$linkResources	Flag: try to link resources in message
	 *	@return		string
	 *	@noinspection PhpDocMissingThrowsInspection
	 */
	public function buildMessages( string $timeFormat = NULL, bool $clear = TRUE, bool $linkResources = FALSE ): string
	{
		$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
		$list		= '';
		$ids		= [];
		if( count( $messages ) ){
			$items	= [];
			foreach( $messages as $message ){
				if( $linkResources )																//  realize URLs as links
					$message['message']	= preg_replace( '/(http.+)("|\'| )/U', '<a href="\\1">\\1</a>\\2', $message['message'] );

				/*  (avoid duplicate messages which were collected during several redirects)  */
				/** @noinspection PhpUnhandledExceptionInspection */
				$id	= md5( json_encode( [$message['type'], $message['message']], JSON_THROW_ON_ERROR ) );			//  calculate message ID
				if( in_array( $id, $ids ) )															//  ID has been calculated before
					continue;																		//  skip this duplicate message
				$ids[]	= $id;																		//  note calculated ID

				$class		= $this->classes[$message['type']];
				$span		= HtmlTag::create( 'span', $message['message'], array( 'class' => 'message' ) );
				if( $timeFormat && !empty( $message['timestamp'] ) ){
					$time	= $message['timestamp'];
					$time	= TimeConverter::convertToHuman( $time, $timeFormat );
					$time	= '['.$time.'] ';
					$time	= HtmlTag::create( 'span', $time, array( 'class' => 'time' ) );
					$span	= $time.$span;
				}
				// @todo use hook to apply module UI_JS_Messenger
				if( $this->env->getModules()->has( 'UI_JS_Messenger' ) ){
					$button		= HtmlTag::create( "div", '<span></span>', array(
						'class'		=> 'button discard',
						'onclick'	=> "UI.Messenger.discardMessage($(this).parent());",
						'alt'		=> 'ausblenden',
						'title'		=> 'ausblenden',
					 ) );
					$span	= $span.$button;
				}
				$items[] 	= HtmlElements::ListItem( $span, 0, array( 'class' => $class ) );
			}
			$list	= HtmlElements::unorderedList( $items );
			if( $clear )
				$this->clear();
		}
		return $list;
	}

	/**
	 *	Clears stack of messages.
	 *	@access		public
	 *	@return		void
	 */
	public function clear()
	{
		$this->env->getSession()->set( $this->keyHeadings, [] );
		$this->env->getSession()->set( $this->keyMessages, [] );
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
	 *	Indicates whether an Error or a Failure has been noted.
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
	 *	Saves an error message on the message stack.
	 *	@access		public
	 *	@param		string		$message			Message to display
	 *	@param		mixed|NULL	$arg1				Arguments to be set into message
	 *	@param		mixed|NULL	$arg2				Arguments to be set into message
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
	 *	@param		mixed|NULL	$arg1				Arguments to be set into Message
	 *	@param		mixed|NULL	$arg2				Arguments to be set into Message
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
	 *	@param		mixed|NULL	$arg1				Arguments to be set into Message
	 *	@param		mixed|NULL	$arg2				Arguments to be set into Message
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
	 *	@param		mixed|NULL	$arg1				Arguments to be set into Message
	 *	@param		mixed|NULL	$arg2				Arguments to be set into Message
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
			$messages	= (array) $this->env->getSession()->get( $this->keyMessages );
			$messages[]	= array( "message" => $message, "type" => $type, "timestamp" => time() );
			$this->env->getSession()->set( $this->keyMessages, $messages );
		}
	}
}
