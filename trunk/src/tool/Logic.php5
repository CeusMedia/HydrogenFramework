<?php
/**
 *	Abstract Business Logic, Access to Database or other Resources.
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
 *	@package		framework.tool
 *	@uses			Database_PDO_Connection
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			27.05.2008
 *	@version		0.1
 */
/**
 *	Abstract Business Logic, Access to Database or other Resources.
 *	@category		cmClasses
 *	@package		framework.tool
 *	@abstract
 *	@uses			Database_PDO_Connection
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			27.05.2008
 *	@version		0.1
 */
abstract class Framework_Tool_Logic
{
	/**	@var		File_Configuration_Reader	$config			Configuration Object */
	protected $config;
	/**	@var		object						$database		Database Connection Object, is used */
	protected $database	= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		File_Configuration_Reader	$config			Configuration Object
	 *	@return		void
	 */
	public function __construct( File_Configuration_Reader $config )
	{
		$this->config	= $config;
		if( $config['database'] )
			$this->connectDatabase( $config['database'] );
	}

	/** 
	 *	Connects to Database using PDO.
	 *	@access		protected
	 *	@param		File_Configuration_Reader	$config			Configuration Object
	 *	@return		void
	 */
	protected function connectDatabase( $config )
	{
		import( 'de.ceus-media.database.pdo.Connection' );
		$dataSourceName	= "mysql:host=".$config['hostname'].";dbname=".$config['database'];
		$this->database	= new Database_PDO_Connection( $dataSourceName, $config['username'], $config['password'] );
		
		if( $this->config['errorlog'] )
			$this->database->setErrorLog( $config['errorlog'] );

		if( $this->config['querylog'] )
			$this->database->setStatementLog( $config['querylog'] );
	}
}
?>