<?php
/**
 *	Cache to store any Value by its Key statically accessible in all other Scopes.
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
 *	@since			10.11.2008
 *	@version		0.2
 */
import( 'de.ceus-media.file.StaticCache' );
/**
 *	Cache to store any Value by its Key statically accessible in all other Scopes.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@extends		File_StaticCache
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			10.11.2008
 *	@version		0.2
 */
class Framework_Krypton_Core_Cache extends File_StaticCache
{
	public static $enabled;
	public static $accessLog	= "logs/cache.access.log";

	/**
	 *	Returns a cached Value from its Key.
	 *	@access		public
	 *	@static
	 *	@param		string		$key		Key in Cache
	 *	@return		mixed
	 */
	public static function get( $key )
	{
		if( !self::$enabled )
			return NULL;
		error_log( "get:".$key."\n", 3, self::$accessLog );
		return parent::get( $key );
	}

	/**
	 *	Indicates whether Returns a cached Value from its Key.
	 *	@access		public
	 *	@static
	 *	@param		string		$key		Key in Cache
	 *	@return		mixed
	 */
	public static function has( $key )
	{
		if( !self::$enabled )
			return NULL;
		error_log( "has:".$key."\n", 3, self::$accessLog );
		return parent::has( $key );
	}

	/**
	 *	Removes a cached Value.
	 *	@access		public
	 *	@static
	 *	@param		string		$key		Key in Cache
	 *	@return		bool
	 */
	public static function remove( $key )
	{
		if( !self::$enabled )
			return NULL;
		error_log( "remove:".$key."\n", 3, self::$accessLog );
		return parent::remove( $key );
	}
	
	public static function init()
	{
		parent::init( "contents/cache/data/" );
	}

	/**
	 *	Stores a Value by its Key.
	 *	@access		public
	 *	@static
	 *	@param		string		$key		Key in Cache
	 *	@param		mixed		$value		Value to store
	 *	@return		bool
	 */
	public static function set( $key, $value )
	{
		if( !self::$enabled )
			return NULL;
		error_log( "set:".$key."\n", 3, self::$accessLog );
		return parent::set( $key, $value );
	}
}

?>