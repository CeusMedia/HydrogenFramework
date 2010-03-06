<?php
/**
 *	Logic Error.
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
 *	@package		framework.krypton.exception
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			22.02.2007
 *	@version		0.1
 */
/**
 *	Logic Error.
 *	@category		cmClasses
 *	@package		framework.krypton.exception
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			22.02.2007
 *	@version		0.1
 */
class Framework_Krypton_Exception_Logic extends Exception
{
	/**	@var	string		$key		Message Key */
	public $key;
	/**	@var	string		$subject	Subject to be filled in */
	public $subject;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$key			Message Key
	 *	@param		string		$subject		Subject to be filled in
	 *	@param		string		$situation		Situation to be filled in
	 *	@return		void
	 */
	public function __construct( $key, $subject = "" )
	{
		parent::__construct( $key );
		$this->key			= $key;
		$this->subject		= $subject;
	}
}
?>