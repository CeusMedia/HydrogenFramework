<?php
/**
 *	Database resource using PDO extension of CeusMedia:Common.
 *
 *	Copyright (c) 2011-2025 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Database
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Database;

use CeusMedia\Database\PDO\Connection;
use CeusMedia\Database\PDO\DataSourceName;
use CeusMedia\HydrogenFramework\Environment;
use InvalidArgumentException;
use RuntimeException;

/**
 *	Database resource using PDO extension of CeusMedia:Common.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Database
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class PDO extends Connection
{
	protected Environment $env;

	/**
	 *	@param			Environment		$env
	 *	@noinspection	PhpMissingParentConstructorInspection
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->setUp();
	}

	public function __destruct()
	{
		$this->tearDown();
	}

	/**
	 *	Returns table prefix from configuration.
	 *	@access		public
	 *	@return		string
	 */
	public function getPrefix(): string
	{
		if( $this->env->getModules()->has( 'Resource_Database' ) )									//  module for database support is installed
			return $this->env->getConfig()->get( 'module.resource_database.access.prefix' );		//  extract prefix from module configuration
		return $this->env->getConfig()->get( 'database.prefix' );									//  extract prefix from main configuration
	}

	/**
	 *	Sets up connection to database, if configured with database module or main config (deprecated).
	 *
	 *	Attention: If using MySQL and UTF-8 the charset must bet set after connection established.
	 *	Therefore, the option MYSQL_ATTR_INIT_COMMAND is set by default, which hinders lazy connection mode (which is not implemented yet).
	 *	In the future, having lazy mode, the config pair "charset" will be realized by implementing a statement queue, which is run before a lazy connection is used the first time.
	 *
	 *	Attention: Using statement log means that EVERY statement send to database will be logged.
	 *	Applications with heavy database use will slow down and create large log files.
	 *	Be sure to rotate the logs or remove them frequently to avoid low hard disk space.
	 *	Disable this feature after development/debugging!
	 *
	 *	@todo		implement lazy mode
	 *	@todo		0.7: clean deprecated code
	 *	@return		void
	 */
	protected function setUp()
	{
		$config			= $this->env->getConfig();
		if( $this->env->getModules()->has( 'Resource_Database' ) ){									//  module for database support is installed
			$dba			= $config->getAll( 'module.resource_database.access.' );				//  get connection access configuration
			$logStatements	= $config->get( 'module.resource_database.log.statements' );			//
			$logErrors		= $config->get( 'module.resource_database.log.errors' );				//
			$options		= $config->getAll( 'module.resource_database.option.' );				//  get connection options
		}
		else{																						//  @deprecated	use database module instead
			$dba		= [];
			$dbaKeys	= array( 'driver', 'host', 'port', 'name', 'username', 'password', 'prefix' );	//  list of access configuration pair keys
			foreach( $dbaKeys as $key )																	//  iterate keys
				$dba[$key]	= $config->get( 'database.'.$key );											//  realize access configuration setting
	#		$logfile		= $config->get( 'database.log' );										//  @deprecated
	#		$lazy			= $config->get( 'database.lazy' );										//  @todo		implement
	#		$charset		= $config->get( 'database.charset' );									//  @todo		implement, for lazy mode too
			$logStatements	= $config->get( 'database.log.statements' );
			$logErrors		= $config->get( 'database.log.errors' );
			$options		= $config->getAll( 'database.option.' );
		}

		if( !( isset( $dba['driver'] ) && strlen( $dba['driver'] ) > 0 ) )
			throw new RuntimeException( 'Database driver must be set in config:database.driver' );

		$dsn		= new DataSourceName( $dba['driver'], $dba['name'] );
		if( isset( $dba['host'] ) && strlen( $dba['host'] ) > 0 )
			$dsn->setHost( $dba['host'] );
		if( isset( $dba['port'] ) && strlen( $dba['port'] ) > 0 )
			$dsn->setPort( $dba['port'] );
		if( isset( $dba['username'] ) && strlen( $dba['username'] ) > 0 )
			$dsn->setUsername( $dba['username'] );
		if( isset( $dba['password'] ) && strlen( $dba['password'] ) > 0 )
			$dsn->setPassword( $dba['password'] );

		$defaultOptions	= [
			'ATTR_PERSISTENT'				=> TRUE,
			'ATTR_ERRMODE'					=> "PDO::ERRMODE_EXCEPTION",
			'ATTR_DEFAULT_FETCH_MODE'		=> "PDO::FETCH_OBJ",
			'ATTR_CASE'						=> "PDO::CASE_NATURAL",
			'MYSQL_ATTR_USE_BUFFERED_QUERY'	=> TRUE,
			'MYSQL_ATTR_INIT_COMMAND'		=> "SET NAMES 'utf8';",
		];
		$options	+= $defaultOptions;

		//  --  DATABASE OPTIONS  --  //
		$driverOptions	= [];																	//  @todo: to be implemented
		foreach( $options as $key => $value ){														//  iterate all database options
			if( !defined( "PDO::".$key ) )															//  no PDO constant for option key
				throw new InvalidArgumentException( 'Unknown constant PDO::'.$key );				//  quit with exception
			if( is_string( $value ) && preg_match( "/^[A-Z][A-Z0-9_:]+$/", $value ) )				//  option value is a constant name
				$value	= constant( $value );														//  replace option value string by constant value
			$driverOptions[constant( "PDO::".$key )]	= $value;									//  note option
		}

		parent::__construct( $dsn, $dba['username'], $dba['password'], $driverOptions );							//  connect to database

		if( $logStatements )
			$this->setStatementLogFile( $config->get( 'path.logs' ).$logStatements );
		if( $logErrors )
			$this->setErrorLogFile( $config->get( 'path.logs' ).$logErrors );
#		if( $charset && $this->driver == 'mysql' )													//  a character set is configured on a MySQL database
#			$this->exec( "SET NAMES '".$charset."';" );												//  set character set
	}

	/**
	 *	@return		void
	 */
	protected function tearDown(): void
	{
	}
}
