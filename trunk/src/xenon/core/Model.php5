<?php
/**
 *	Generic Model for Database Structures.
 *	Is related to open Database Connection and Configuration in Registry.
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
 *	@extends		Database_PDO_TableWriter
 *	@uses			Core_Registry
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			19.02.2007
 *	@version		0.6
 */
import( 'de.ceus-media.database.pdo.TableWriter' );
import( 'de.ceus-media.framework.xenon.core.Registry' );
/**
 *	Generic Model for Database Structures.
 *	Is related to open Database Connection and Configuration in Registry.
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@extends		Database_PDO_TableWriter
 *	@uses			Core_Registry
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			19.02.2007
 *	@version		0.6
 */
class Framework_Xenon_Core_Model extends Database_PDO_TableWriter
{
	public static $tablePrefixKey	= 'database.access.prefix';
	public static $tablePrefixKeyBelow_v0_6_5	= 'config.table_prefix';

	/**	@var	string			$prefix			Prefix of Table  */
	private $prefix;
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$table			Name of Table
	 *	@param		array		$columns		Columnss of Table
	 *	@param		string		$primaryKey		Primary Key of Table
	 *	@param		int			$focus			Current focussed primary Key
	 *	@return		void
	 */
	public function __construct( $table, $columns, $primaryKey, $focus = FALSE )
	{
		$dbc			= Framework_Xenon_Core_Registry::getStatic( 'dbc' );
		$config			= Framework_Xenon_Core_Registry::getStatic( 'config' );
		$this->prefix	= $config[self::$tablePrefixKey];
		
		//  downwards compatibility with applications build below cmClasses v0.6.5
		if( !isset( $config[self::$tablePrefixKey] )&& isset( $config[self::$tablePrefixKeyBelow_v0_6_5] ) )
			$this->prefix	= $config[self::$tablePrefixKeyBelow_v0_6_5];

		parent::__construct( $dbc, $table, $columns, $primaryKey, $focus );
	}
	
	/**
	 *	Returns Prefix of Table.
	 *	@access		public
	 *	@return		string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	public function getError()
	{
		$dbc	= Framework_Xenon_Core_Registry::getStatic( 'dbc' );
		return $dbc->errorInfo();
	}
	
	/**
	 *	Returns (prefixed) Name of Table.
	 *	@access		public
	 *	@param		bool		$prefixed		Flag: use also Prefix of Table
	 *	@return		string
	 */
	public function getTableName( $prefixed = TRUE )
	{
		if( $prefixed )
			return $this->getPrefix().parent::getTableName();
		return parent::getTableName();
	}
		
	/**
	 *	Indicates whether an Entry exists.
	 *	@access		public
	 *	@param		int			$id				Primary Key
	 *	@return		bool
	 */
	public function exists( $id = NULL )
	{
		if( $id === NULL )
			return (bool) count( $this->get( TRUE ) );
		if( (int) $id > 0 )
		{
			$clone	= clone( $this );
			$clone->defocus();
			$clone->focusPrimary( (int) $id );
			return (bool) count( $clone->get( TRUE ) );
		}
		throw new InvalidArgumentException( 'ID invalid' );
	}
}
?>
