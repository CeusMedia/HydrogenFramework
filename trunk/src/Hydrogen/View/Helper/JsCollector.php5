<?php
/**
 *	Helper to collect JavaScript blocks.
 *
 *	Copyright (c) 2010 Christian Würker (ceusmedia.com)
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
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Helper to collect JavaScript blocks.
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 *	@todo			kriss: check if deprecated
 */
class CMF_Hydrogen_View_Helper_JsCollector {

	/**	@var		ADT_Singleton	$instance		Instance of Singleton */
	protected static $instance;
	/**	@var		array			$scripts		List of JavaScript blocks */
	protected $scripts	= array();

	/**
	 *	Constructor is disabled from public context.
	 *	Use static call 'getInstance()' instead of 'new'.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __construct(){}

	/**
	 *	Cloning this object is not allowed.
	 *	@access		private
	 *	@return		void
	 */
	private function __clone(){}

	/**
	 *	Collect a JavaScript block.
	 *	@access		public
	 *	@param		string		$script		JavaScript block
	 *	@param		bool		$onTop		Put this block on top of all others
	 *	@return		void
	 */
	public function addScript( $script, $onTop = FALSE ){
		$onTop ? array_unshift( $this->scripts, $script ) : array_push( $this->scripts, $script );
	}

	/**
	 *	Returns a single instance of this Singleton class.
	 *	This method is abtract and must be defined in inheriting clases.
	 *	@abstract
	 *	@static
	 *	@access		public
	 *	@return		ADT_Singleton	Single instance of this Singleton class
	 */
	public static function getInstance(){
		if( !self::$instance )
			self::$instance	= new CMF_Hydrogen_View_Helper_JsCollector();
		return self::$instance;
	}

	/**
	 *	Renders an HTML scrtipt tag with all collected JavaScript blocks.
	 *	@access		public
	 *	@param		bool		$indentEndTag	Flag: indent end tag by 2 tabs
	 *	@return		string
	 */
	public function getScriptTag( $indentEndTag = FALSE ){
		array_unshift( $this->scripts, '' );
		array_push( $this->scripts, $indentEndTag ? "\t\t" : '' );
		$content	= implode( "\n", $this->scripts );
		$attributes	= array(
			'type'		=> 'text/javascript',
//			'language'	=> 'JavaScript',
		);
		return UI_HTML_Tag::create( 'script', $content, $attributes );
	}
}
?>