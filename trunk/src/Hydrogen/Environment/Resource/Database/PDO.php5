<?php
/**
 *	Database resource using PDO wrapper from cmClasses.
 *
 *	Copyright (c) 2011 Christian Würker (ceusmedia.com)
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
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Database
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.4
 *	@version		$Id$
 */
/**
 *	Database resource using PDO wrapper from cmClasses.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Database
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.4
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Database_PDO extends Database_PDO_Connection
{
	protected $env;
	
	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env	= $env;
		$this->setUp();
	}

	protected function setUp()
	{
		$config			= $this->env->getConfig();
		
		$driver			= $config->get( 'database.driver' );
		$host			= $config->get( 'database.host' );
		$port			= $config->get( 'database.port' );
		$name			= $config->get( 'database.name' );
		$username		= $config->get( 'database.username' );
		$password		= $config->get( 'database.password' );
		$prefix			= $config->get( 'database.prefix' );
		$logfile		= $config->get( 'database.log' );
#		$lazy			= $config->get( 'database.lazy' );
#		$charset		= $config->get( 'database.charset' );
		$logStatements	= $config->get( 'database.log.statements' );
		$logErrors		= $config->get( 'database.log.errors' );

		if( empty( $driver ) )
			throw new RuntimeException( 'Database driver must be set in config:database.driver' );

		$dsn		= new Database_PDO_DataSourceName( $driver, $name );
		if( $host )
			$dsn->setHost( $host );
		if( $port )
			$dsn->setPort( $port );
		if( $username )
			$dsn->setUsername( $username );
		if( $password )
			$dsn->setPassword( $password );

		$defaultOptions	= array(
			'ATTR_PERSISTENT'				=> TRUE,
			'ATTR_ERRMODE'					=> "PDO::ERRMODE_EXCEPTION",
			'ATTR_DEFAULT_FETCH_MODE'		=> "PDO::FETCH_OBJ",
			'ATTR_CASE'						=> "PDO::CASE_NATURAL",
			'MYSQL_ATTR_USE_BUFFERED_QUERY'	=> TRUE,
			'MYSQL_ATTR_INIT_COMMAND'		=> "SET NAMES 'utf8';",
		);
		$options	= $config->getAll( 'database.option.' ) + $defaultOptions;
		
		//  --  DATABASE OPTIONS  --  //
		$driverOptions	= array();																	//  @todo: to be implemented
		foreach( $options as $key => $value ){														//  iterate all database options
			if( !defined( "PDO::".$key ) )															//  no PDO constant for for option key
				throw new InvalidArgumentException( 'Unknown constant PDO::'.$key );				//  quit with exception
			if( is_string( $value ) && preg_match( "/^[A-Z][A-Z0-9_:]+$/", $value ) )				//  option value is a constant name
				$value	= constant( $value );														//  replace option value string by constant value 
			$driverOptions[constant( "PDO::".$key )]	= $value;									//  note option
		}

		parent::__construct( $dsn, $username, $password, $driverOptions );							//  connect to database

		if( $logStatements )
			$this->setStatementLogFile( $config->get( 'path.logs' ).$logStatements );
		if( $logErrors )
			$this->setErrorLogFile( $config->get( 'path.logs' ).$logErrors );
#		if( $charset && $this->driver == 'mysql' )													//  a character set is configured on a MySQL database
#			$this->exec( "SET NAMES '".$charset."';" );												//  set character set
	}

	public function tearDown(){}
}
?>
