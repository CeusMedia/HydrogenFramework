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
 *	@package		framework.helium
 *	@uses			ADT_Reference
 *	@uses			Alg_Time_Clock
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Framework_Helium_Messenger
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
import( 'de.ceus-media.adt.Reference' );
import( 'de.ceus-media.alg.time.Clock' );
import( 'de.ceus-media.net.http.request.Receiver' );
import( 'de.ceus-media.framework.helium.Messenger' );
/**
 *	Basic Framework Instance.
 *	@category		cmClasses
 *	@package		framework.helium
 *	@uses			ADT_Reference
 *	@uses			Alg_Time_Clock
 *	@uses			Net_HTTP_Request_Receiver
 *	@uses			Framework_Helium_Messenger
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
class Framework_Helium_Base
{
	/**	@var	ADT_Reference	$ref		Object Reference */
	var $ref;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->ref	= new ADT_Reference;
		$this->ref->add( "stopwatch",	new Alg_Time_Clock );		
		$this->ref->add( "messenger",	new Framework_Helium_Messenger );
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

	/**
	 *	Transforms requested Link into linked Class Names usind Separators.
	 *	@access		protected
	 *	@param		string		$link					Link to transform to Class Name File
	 *	@param		string		$separator_link		Separator in Link
	 *	@param		string		$separator_class		Separator for Classes
	 *	@return		string
	 */
	protected function transformLink( $link, $separator_folder = "__", $separator_class = "/", $separator_case = "_" )
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