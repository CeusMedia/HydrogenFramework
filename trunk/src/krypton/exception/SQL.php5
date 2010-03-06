<?php
/**
 *	Exception for SQL Errors.
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
 *	@extends		RuntimeException
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.03.2007
 *	@version		0.1
 */
/**
 *	Exception for SQL Errors.
 *	@category		cmClasses
 *	@package		framework.krypton.exception
 *	@extends		RuntimeException
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.03.2007
 *	@version		0.1
 */
class Framework_Krypton_Exception_SQL extends RuntimeException
{
	/**	@var		string		$error				Error Message from SQL */
	public $sqlCode;
	/**	@var		string		$error				Error Message from SQL */
	public $sqlMessage;
	/**	@var		string		$error				Error Message from SQL */
	public $pdoCode;
	/**	@var		string		$exceptionMessage	Message of Exception with Placeholder */
	public static $exceptionMessage	= 'SQL Error';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		int			$sqlCode		SQL Error Code
	 *	@param		string		$sqlMessage		SQL Error Message
	 *	@param		int			$pdoCode		PDO Error Code
	 *	@return		void
	 */
	public function __construct( $sqlCode, $sqlMessage, $pdoCode = 0 )
	{
		$this->sqlCode		= $sqlCode;
		$this->sqlMessage	= $sqlMessage;
		$this->pdoCode		= $pdoCode;
		parent::__construct( self::$exceptionMessage );	
	}
	
	/**
	 *	Returns SQL Error Message.
	 *	@access		public
	 *	@return		string
	 */
	public function getErrorCode()
	{
		return $this->sqlCode;
	}

	/**
	 *	Returns SQL Error Message.
	 *	@access		public
	 *	@return		string
	 */
	public function getErrorMessage()
	{
		return $this->sqlMessage;
	}

	/**
	 *	Returns SQL Error Message.
	 *	@access		public
	 *	@return		string
	 */
	public function getPdoErrorCode()
	{
		return $this->pdoCode;
	}
/*	
	public function __sleep()
	{
		get_object_vars( $this );
	}
	
	public function _sleep()
	{
		get_object_vars( $this );
	}
	

	private function check( $array )
	{
		foreach( $array as $element )
		{
			if ( $element instanceof PDO )
			{
			}
			if ( $element instanceof PDOException )
			{
				unset($element);
				break;
			}
			if ( is_array( $element ) )
			{
				$this->_check( $element );
			}
		}
	}*/
}
?>
