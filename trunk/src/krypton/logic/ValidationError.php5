<?php
/**
 *	Validation Error.
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
 *	@package		framework.krypton.logic
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			22.02.2007
 *	@version		0.1
 */
/**
 *	Validation Error.
 *	@category		cmClasses
 *	@package		framework.krypton.logic
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			22.02.2007
 *	@version		0.1
 */
class Framework_Krypton_Logic_ValidationError
{
	/**	@var	string		$edge		Edge for semantic validation */
	public $edge;
	/**	@var	string		$field		Name of Field */
	public $field;
	/**	@var	string		$key		Message Key */
	public $key;
	/**	@var	string		$value		Value of Field */
	public $value;
	/**	@var	string		$prefix		Prefix of Field Name */
	public $prefix;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$type		Validation Type were Error occured (syntax|sematic)
	 *	@param		string		$field		Name of Field
	 *	@param		string		$key		Message Key
	 *	@param		string		$value		Situation to be filled in
	 *	@param		string		$edge		Edge for semantic validation
	 *	@param		string		$edge		Field Prefix
	 *	@return		void
	 */
	public function __construct( $field, $key, $value, $edge = false, $prefix = "" )
	{
		$this->key		= $key;
		$this->field	= $field;
		$this->value	= $value;
		$this->edge		= $edge;
		$this->prefix	= $prefix;
	}
}
?>