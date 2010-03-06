<?php
/**
 *	Factory to produce Class Names for Types.
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
 *	@since			03.03.2007
 *	@version		0.1
 */
/**
 *	Factory to produce Class Names for Types.
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			03.03.2007
 *	@version		0.1
 */
class Framework_Xenon_Core_CategoryFactory
{
	/**	@var	string		$default	Default Type Selection */
	protected $default	= "";
	/**	@var	string		$type		Selected Type */
	protected $type		= "";
	/**	@var	string		$types		Available Types */
	protected $types	= array();
	
	public $prefixClass	= "Category_";
	public $prefixFile	= "category.";
	
	/**
	 *	Returns typed Class Name.
	 *	@access		public
	 *	@param		string		$className		Class Name to type
	 *	@param		string		$prefix			Class Prefix (view,action,...)
	 *	@param		string		$category		Category to force
	 *	@return		string
	 */
	public function getClassName( $className, $prefix = "", $category = "" )
	{
		$type	= $category ? $category : $this->getType();
		$name	= $prefix." ".str_replace( "_", " ", $this->prefixClass ).$type." ".$className;
		$name	= ucWords( trim( $name ) );
		$name	= str_replace( " ", "_", $name );
		return $name;
	}

	
	/**
	 *	Returns typed Class File Name.
	 *	@access		public
	 *	@param		string		$className		Class Name to type
	 *	@param		string		$prefix			Class Prefix (view,action,...)
	 *	@param		string		$category		Category to force
	 *	@return		string
	 */
	public function getClassFileName( $className, $prefix = "", $category = "" )
	{
		$type		= $category ? $category : $this->getType();
		$className	= ucFirst( $className );
		$className	= $this->prefixFile.strtolower( $type ).".".$className;
		if( $prefix )
			$className	= strtolower( $prefix ).".".$className;
		return $className;
	}
	
	/**
	 *	Imports and returns Object.
	 *	@access		public
	 *	@param		string		$className		Class Name to type
	 *	@param		string		$prefix			Class Prefix (view,action,...)
	 *	@param		string		$category		Category to force
	 *	@return		object
	 */
	public function getObject( $className, $prefix = "", $category = "" )
	{
		$fileName	= "classes.".$this->getClassFileName( $className, $prefix, $category );
		$className	= $this->getClassName( $className, $prefix, $category );
		if( !class_exists( $className ) )
			import( $fileName );
		return new $className();
	}
	
	/**
	 *	Returns selected Typed or default Type if not Type is selected.
	 *	@access		public
	 *	@return		string
	 *	@throws		Exception
	 */
	public function getType()
	{
		if( $this->type )
			return $this->type;
		if( $this->default )
			return $this->default;
		if( $this->types )
			return $this->types[0];
		throw new Exception( 'No Types set.' );
	}


	/**
	 *	Returns List of set Types.
	 *	@access		public
	 *	@return		array
	 */
	public function getTypes()
	{
		return $this->types;
	}
	
	/**
	 *	Sets default Type.
	 *	@access		public
	 *	@param		string		$types		Default Type
	 *	@return		void
	 */
	public function setDefault( $type )
	{
		$type	= trim( $type );
		if( !in_array( $type, $this->types ) )
			throw new InvalidArgumentException( "Type '".$type."' is not available." );
		$this->default	= $type;
	}
	
	/**
	 *	Set selected Type.
	 *	@access		public
	 *	@param		string		$type		Type to select
	 *	@return		void
	 */
	public function setType( $type )
	{
		$type	= trim( $type );
		if( $type )
		{
			if( !in_array( $type, $this->types ) )
				throw new InvalidArgumentException( 'Type "'.$type.'" is not available.' );
			$this->type	= $type;
		}
	}
	
	/**
	 *	Sets available Types.
	 *	@access		public
	 *	@param		array		$types		Available Types
	 *	@return		void
	 */
	public function setTypes( $types )
	{
		$this->types	= array();
		foreach( $types as $type )
			$this->types[]	= trim( $type );
	}
}
?>