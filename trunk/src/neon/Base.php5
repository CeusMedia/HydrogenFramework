<?php
/**
 *	Basic Framework Instance.
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
 *	@package		framework.neon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
import( 'de.ceus-media.adt.Reference' );
import( 'de.ceus-media.StopWatch' );
import( 'de.ceus-media.net.http.request.Receiver' );
import( 'de.ceus-media.net.http.request.Response' );
import( 'de.ceus-media.framework.neon.Messenger' );
/**
 *	Basic Framework Instance.
 *	@category		cmClasses
 *	@package		framework.neon
 *	@uses			ADT_Reference
 *	@uses			StopWatch
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Net_HTTP_Request_Response
 *	@uses			Framework_Neon_Messenger
 *	@uses			InterfaceViews
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
class Framework_Neon_Base
{
	/**	@var	ADT_Reference	$ref			Object Reference */
	protected $ref;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->ref	= new ADT_Reference;
		$this->ref->add( "stopwatch",	new StopWatch );		
		$this->ref->add( "messenger",	new Framework_Neon_Messenger );
		$this->ref->add( "request",		new Net_HTTP_Request_Receiver );
		$this->init();
	}
	
	/**
	 *	Creates references Objects and loads Configuration, to be overwritten.
	 *	@access		protected
	 *	@return		void
	 */
	protected function init()
	{
	}

	/**
	 *	Runs called Actions, to be overwritten.
	 *	@access		public
	 *	@return		void
	 */
	public function runActions()
	{
	}
	
	/**
	 *	Creates Views by called Link and Rights of current User and returns HTML, to be overwritten.
	 *	@access		public
	 *	@return		string
	 */
	public function buildViews()
	{
	}

	protected function transformLink( $link, $cutSeparator = "__", $joinSeparator = "_" )
	{
		$parts	= explode( $cutSeparator, $link );
		$class	= ucFirst( array_pop( $parts ) );
		$path	= implode( $joinSeparator, $parts );
		$path	= $path ? $path.$joinSeparator : "";
		$parts	= explode( "_", $class );
		foreach( $parts as $part )
			$path	.= ucFirst( $part );
		return $path;
	}
	
	protected function getClassNameFromLink( $link )
	{
		return $this->transformLink( $link, "__", "_" );
	}
	
	protected function getClassFileFromLink( $link )
	{
		return $this->transformLink( $link, "__", "/" );
	}
	
	protected function loadActionClassFromLink( $link )
	{
		return $this->loadClassFromLink( $link, "actions/", "Actions" );
	}
	
	protected function loadClassFromLink( $link, $pathSuffix, $classSuffix )
	{
		$className	= $this->getClassNameFromLink( $link ).$classSuffix;
		$classFile	= "classes/".$pathSuffix.$this->getClassFileFromLink( $link ).$classSuffix.".php5";
		if( !file_exists( $classFile ) )
		{
			$path		= ucFirst( str_replace( "/", "_", $pathSuffix ) );
			$className2	= "Framework_Neon_".$path.$className;
			$classFile	= preg_replace( "@^classes/@", CM_CLASS_PATH."de/ceus-media/framework/neon/", $classFile );
			if( !file_exists( $classFile ) )
				throw new RuntimeException( 'Neither Project Class "'.$className.'" nor Framework Class "'.$className2.'" is existing.' );
			$className	= $className2;
		}
		require_once( $classFile );
		$object	= new $className();
		return $object;
	}
	
	protected function loadViewClassFromLink( $link )
	{
		return $this->loadClassFromLink( $link, "views/", "Views" );
	}
	
	/**
	 *	Transforms requested Link into linked Class Names usind Separators.
	 *	@access		protected
	 *	@param		string		$link				Link to transform to Class Name File
	 *	@param		string		$separator_link		Separator in Link
	 *	@param		string		$separator_class	Separator for Classes
	 *	@return		string
	 */
	protected function _transformLink( $link, $separator_folder = "__", $separator_class = "/", $separator_case = "_" )
	{
		$words	= explode( $separator_folder, $link );
		$count	= count( $words );
		for( $i=0; $i<$count; $i++ )
		{
			if( $separator_class && $i == ( $count - 1 ) )
				$class	= ucfirst( strtolower( $words[$i] ) );
			else
				$class	= ucfirst( strtolower( $words[$i] ) );
			$words[$i] = $class;
		}
		$link		= implode( $separator_class, $words );

		$words	= explode( $separator_case, $link );
		$count	= count( $words );
		for( $i=0; $i<$count; $i++ )
		{
			$class	= ucfirst( ucfirst( $words[$i] ) );
			$words[$i] = $class;
		}
		$link		= implode( "", $words );

		return $link;
	}
}
?>