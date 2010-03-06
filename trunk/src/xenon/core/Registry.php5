<?php
/**
 *	Registry Singleton to store Objects
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
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.02.2007
 *	@version		0.7
 */
/**
 *	Registry Singleton to store Objects
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			20.02.2007
 *	@version		0.7
 */
class Framework_Xenon_Core_Registry
{
	/**	@var		Framework_Xenon_Core_Registry	$instance		Instance of Registry */
	protected static $instance	= null;
	/**	@var		array		$values			Array of stored Objects */
	protected $values	= array();

	/**
	 *	Constructor.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __construct() {}

	/**
	 *	Denies to clone Registry.
	 *	@access		private
	 *	@return		void
	 */
	private function __clone() {}
	
	/**
	 *	Cleares registered Object.
	 *	@access		public
	 *	@return		void
	 */
	public function clear()
	{
		foreach( $this->values as $key => $value )
			unset( $this->values[$key] );
	}

	/**
	 *	Returns Instance of Registry.
	 *	@access		public
	 *	@static
	 *	@return		Registry
	 */
	public static function getInstance()
	{
		if( self::$instance == null )
		{
			self::$instance	= new Framework_Xenon_Core_Registry();
		}
		return self::$instance;		
	}

	/**
	 *	Returns registered Object.
	 *	@access		public
	 *	@param		string		$key		Key of registered Object
	 *	@return		mixed
	 */
	public function & get( $key )
	{
		if( !isset( $this->values[$key] ) )
			throw new InvalidArgumentException( 'No Object registered with Key "'.$key.'"' );
		return $this->values[$key];
	}
	
	/**
	 *	Returns registered Object statically.
	 *	@access		public
	 *	@static
	 *	@param		string		$key		Key of registered Object
	 *	@return		mixed
	 */
	public static function & getStatic( $key )
	{
		return self::getInstance()->get( $key );
	}
	
	/**
	 *	Indicates whether a Key is registered.
	 *	@access		public
	 *	@param		string		$key		Key of registered Object to be checked
	 *	@return		bool
	 */
	public function has( $key )
	{
		return array_key_exists( $key, $this->values );
	}

	/**
	 *	Removes a registered Object from Registry.
	 *	@access		public
	 *	@param		string		$key		Key of registered Object to remove
	 *	@return		void
	 */
	public function remove( $key )
	{
		if( !isset( $this->values[$key] ) )
			throw new InvalidArgumentException( 'No Object registered with Key "'.$key.'"' );
		unset( $this->values[$key] );
	}
	
	/**
	 *	Registers Object.
	 *	@access		public
	 *	@param		string		$key		Key of registered Object
	 *	@param		mixed		$value		Object to register
	 *	@param		bool		$overwrite	Flag: overwrite already registered Objects
	 *	@return		void
	 */
	public function set( $key, &$value, $overwrite = false )
	{
		if( isset( $this->values[$key] ) && !$overwrite )
			throw new RuntimeException( 'Element "'.$key.'" is already registered.' );
		$this->values[$key]	=& $value;
	}
}  
?>