<?php
/**
 *	Logic Base Class with Validation
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
 *	@uses			Framework_Xenon_Core_Registry
 *	@uses			Framework_Xenon_Core_DefinitionValidator
 *	@uses			Alg_Validation_Predicates
 *	@uses			Framework_Xenon_Exception_Validation
 *	@uses			Framework_Xenon_Exception_IO
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			21.02.2007
 *	@version		0.6
 */
/**
 *	Logic Base Class with Validation
 *	@category		cmClasses
 *	@package		framework.xenon.core
 *	@uses			Framework_Xenon_Core_Registry
 *	@uses			Framework_Xenon_Core_DefinitionValidator
 *	@uses			Alg_Validation_Predicates
 *	@uses			Framework_Xenon_Exception_Validation
 *	@uses			Framework_Xenon_Exception_IO
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			21.02.2007
 *	@version		0.6
 */
class Framework_Xenon_Core_Logic
{
	/**	@var		Registry	$registry		Registry for Objects */
	protected $registry;
	
	public static $pathLogic		= "classes.logic.category.";
	public static $pathCollection	= "classes.collection.category.";
	public static $classLogic		= "Logic_Category_";
	public static $classCollection	= "Collection_Category_";
	
	protected static $module	= NULL;

	/**
	 *	Constructor, loads Definition Validator and Field Definition.
	 *	@access		public
	 *	@param		string		$predicateClass		Class holding Validation Predicates
	 *	@return		void
	 */
	public function __construct()
	{
		$this->registry			= Framework_Xenon_Core_Registry::getInstance();
	}

	/**
	 *	Logic Factory for Categories.
	 *	@access		public
	 *	@static
	 *	@param		string			$category			Category to get Logic for
	 *	@return		object
	 */
	public static function getCategoryLogic( $category )
	{
		$category	= ucFirst( $category );
		$fileName	= self::$pathLogic.$category;
		$className	= self::$classLogic.$category;
		if( !class_exists( $className, TRUE ) )
			throw new Exception_IO( 'Category Logic Class File "'.$fileName.'" is not existing.' );
		return new $className();
	}

	/**
	 *	Collection Factory for Categories.
	 *	@access		public
	 *	@static
	 *	@param		string						$category		Category to get Logic for
	 *	@param		Database_StatementBuilder	$builder		Statement Builder
	 *	@return		object
	 */
	public static function getCategoryCollection( $category, $builder )
	{
		$category	= ucFirst( $category );
		$fileName	= self::$pathCollection.$category;
		$className	= self::$classCollection.$category;
		if( !class_exists( $className, TRUE ) )	
			throw new Exception_IO( 'Category Collection Class File "'.$fileName.'" is not existing.' );
		return new $className( $builder );
	}

	/**
	 *	Returns Table Fields of Model
	 *	@access		public
	 *	@static
	 *	@param		string		$modelName		Class Name of Model
	 *	@throws		Exception_IO
	 *	@return		array
	 */
	public static function getFieldsFromModel( $modelName )
	{
		if( class_exists( $modelName, TRUE ) )
		{
			$model	= new $modelName;
			return $model->getColumns();
		}
		else
		{
			$list		= array();
			$parts		= explode( "_", $modelName );
			$className	= array_pop( $parts );
			foreach( $parts as $part )
				$list[]	= strtolower( $part );
			$classPath	= implode( "/", $list );
			$fileName	= "classes/".$classPath."/".$className.".php5";
			if( file_exists( $fileName ) )
			{
				require_once( $fileName );
				$model	= new $modelName;
				return $model->getColumns();
			}
		}
		throw new Exception_IO( 'Class "'.$modelName.'" is not existing.' );
	}

	/**
	 *	Loads Field Definitions.
	 *	@access		private
	 *	@static
	 *	@param		string		$fileKey		File Key of XML Definition File (e.g. #FOLDER.FILE#.xml)
	 *	@return		void
	 */
	private static function loadDefinition( $module, $fileKey, $form )
	{
		$registry		= Framework_Xenon_Core_Registry::getInstance();
		if( $registry->has( 'definition' ) )
			$definition	= $registry->get( 'definition' );
		
		$definition->setForm( $form );
		$definition->loadModuleDefinition( $module, $fileKey );
		return $definition;
	}

	/**
	 *	Removes Prefix from Field Name.
	 *	@access		public
	 *	@static
	 *	@param		string		$name		Field Name
	 *	@param		string		$prefix		Prefix to be removed
	 *	@return		string
	 */
	public static function removePrefixFromFieldName( $name, $prefix )
	{
		if( $prefix )
			if( preg_match( "@^".$prefix."@", $name ) )
				$name	= preg_replace( "@^".$prefix."@", "", $name );
		return $name;
	}

	/**
	 *	Removes Prefix from Fields within an associative Array.
	 *	@access		public
	 *	@static
	 *	@param		string		$array		Associative Array of Fields and Values
	 *	@param		string		$prefix		Prefix to be removed
	 *	@return		array
	 */
	public static function removePrefixFromFields( $data, $prefix, $clean = true )
	{
		if( !$prefix )
			return $data;
		$list	= array();
		foreach( $data as $key => $value )
		{
			$newkey	= self::removePrefixFromFieldName( $key, $prefix );
			if( $newkey != $key )
				$list[$newkey]	= $value;
			else if( !$clean )
				$list[$key] = $value;
		}
		return $list;
	}

	/**
	 *	Runs Validation of Field Definitions against Input, creates Error Objects and returns Success.
	 *	@access		public
	 *	@static
	 *	@param		string		$file			Name of XML Definition File (e.g. %PREFIX%#FILE#.xml)
	 *	@param		string		$form			Name of Form within XML Definition File (e.g. 'addExample' )
	 *	@param		array		$data			Array of Input Data
	 *	@param		string		$prefix			Prefix used within Fields of Input Data
	 *	@param		string		$predicateClass	Class holding Validation Predicates
	 *	@throws		Framework_Xenon_Exception_Validation
	 *	@return		bool
	 */
	public static function validateForm( $module, $file, $form, &$data, $prefix = "", $predicateClass = "Alg_Validation_Predicates" )
	{
		$validator	= new Framework_Xenon_Core_DefinitionValidator( $predicateClass );
		$errors		= array();

		$definition	= self::loadDefinition( $module, $file, $form );
		$fields		= $definition->getFields();
		foreach( $fields as $field )
		{
			$def	= $definition->getField( $field );
			$key	= self::removePrefixFromFieldName( $def['input']['name'], $prefix );
			$value	= isset( $data[$key] ) ? $data[$key] : NULL;

			//  --  SET NEGATIVE CHECKBOXES  --  //
			if( preg_match( "@check@", $def['input']['type'] ) && $value === NULL )
			{
				$isInt	= (int) $def['input']['default'] == $def['input']['default'];
				$data[$field]	= $isInt ? (int) $def['input']['default'] : (string) $def['input']['default'];
			}
			if( is_array( $value ) )
				foreach( $value as $entry )
					$errors	= array_merge( $errors, $validator->validate( $field, $def, $entry, $prefix ) );
			else
				$errors	= array_merge( $errors, $validator->validate( $field, $def, $value, $prefix ) );
		}
		if( $errors )
		{
			throw new Exception_Validation( "error_not_valid", $errors, $form );
		}
		return true;
	}
}
?>