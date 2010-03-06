<?php
/**
 *	Validator for defined Fields.
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
 *	@extends		Alg_Validation_DefinitionValidator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			28.08.2006
 *	@version		0.6
 */
import( 'de.ceus-media.alg.validation.DefinitionValidator' );
import( 'de.ceus-media.framework.krypton.logic.ValidationError' );
import( 'de.ceus-media.exception.Validation' );
/**
 *	Validator for defined Fields.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@extends		Alg_Validation_DefinitionValidator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			28.08.2006
 *	@version		0.6
 */
class Framework_Krypton_Core_DefinitionValidator extends Alg_Validation_DefinitionValidator
{
	/**
	 *	Generated Message for Validation Error.
	 *	@access		protected
	 *	@param		string		$field		Field
	 *	@param		string		$key		Validator Key
	 *	@param		string		$value		Value to validate
	 *	@param		string		$edge		At least accepted Value
	 *	@param		string		$prefix		Prefix of Input Field
	 *	@return		string
	 */
	protected function handleError( $field, $key, $value, $edge = false, $prefix )
	{
		import( 'de.ceus-media.framework.krypton.logic.ValidationError' );
		return new Framework_Krypton_Logic_ValidationError( $field, $key, $value, $edge, $prefix );
	}

	/**
	 *	Validates Syntax against Field Definition and generates Messages, special Treatment for Select Options with Key '0'.
	 *	@access		public
	 *	@param		string		$field		Field
	 *	@param		string		$data		Field Definition
	 *	@param		string		$value		Value to validate
	 *	@param		string		$prefix	 	Prefix of Input Field
	 *	@return		array
	 */
	public function validate( $field, $definition, $value, $prefix = "" )
	{
		$errors	= parent::validate( $field, $definition, $value, $prefix );
		foreach( $errors as $error )
			if( $error->key == "isMandatory" )
				return $errors;

		if( isset( $definition['syntax']['mandatory'] ) && $definition['syntax']['mandatory'] )
			if( $definition['input']['type'] == "select" )
				if( !$this->validator->validate( $value, 'isNotZero' ) )
					$errors[]	= $this->handleError( $field, 'isMandatory', $value, NULL, $prefix );
		return $errors;
	}
}
?>