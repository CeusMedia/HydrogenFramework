<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Generic Model Class of Framework Hydrogen.
 * 	Acts as a binding to PDO table implementation in CeusMedia::Database.
 *
 *	Copyright (c) 2007-2026 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Model\Database;

use CeusMedia\Database\PDO\Table as PdoDatabaseTable;
use CeusMedia\HydrogenFramework\Environment;

use PDO;
use PDOException;
use ReflectionException;
use RuntimeException;

/**
 *	Generic Model Class of Framework Hydrogen.
 * 	Acts as a binding to PDO table implementation in CeusMedia::Database.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Table extends PdoDatabaseTable
{
	/**	@var	Environment				$env			Application Environment Object */
	protected Environment $env;

	protected ?string $className		= NULL;

	protected static array $instances	= [];

	/**
	 *	Static constructor.
	 *	Realizes singleton instance per environment URI.
	 *	@param		Environment		$env
	 *	@return		static
	 *	@throws		RuntimeException	if database connection check failed
	 *	@throws		ReflectionException	if cache setup fails to create cache backend by set cache adapter class
	 */
	public static function getInstance( Environment $env ): static
	{
		$prefix	= '';
		$dbc	= $env->getDatabase();
		if( NULL !== $dbc && method_exists( $dbc, 'getConnection' ) ){
			/** @var object $connection */
			$connection	= $dbc->getConnection();
			if( NULL !== $connection && method_exists( $connection, 'getPrefix' ) )
				$prefix	= $connection->getPrefix();
		}

		$id	= $prefix.static::class.'@'.$env->uri;
		if( ! isset( static::$instances[$id] ) ){
			$className	= static::class;
			static::$instances[$id] = new $className( $env );
		}
		return static::$instances[$id];
	}

	/**
	 *	Constructor.
	 *	Returns new instance for environment (not shared).
	 *	Use of static constructor is advised for performance.
	 *	@param		Environment		$env
	 *	@throws		RuntimeException	if database connection check failed
	 *	@throws		ReflectionException	if cache setup fails to create cache backend by set cache adapter class
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		try{
			/** @var PDO $connection */
			$connection		= $this->checkDatabaseConnection();
		}
		catch( RuntimeException $e ){
			$this->env->getLog()?->logException( $e );
			throw new RuntimeException( 'Database connection check failed: '.$e->getMessage(), 0, $e );
		}
		$this->prefix	= method_exists( $connection, 'getPrefix' ) ? $connection->getPrefix() : '';
		$this->cacheKey	= 'db.'.$this->prefix.$this->name.'.';
		parent::__construct( $connection, $this->prefix );
		if( PDO::FETCH_CLASS === $this->fetchMode )
			$this->setFetchEntityClass( $this->className );
	}

	/**
	 *	Applies several checks before creating the table object itself:
	 *	- checks for database resource in environment
	 *	- checks for connection in resource
	 *  - checks sanity of fetch mode and fetch class settings
	 *	Attention: This enforces the connection resource to be an instance of PDO.
	 *	This can change in future, but having other connections is todo.
	 *	@return		object|PDO
	 *	@throws		ReflectionException
	 *	@throws		RuntimeException	if no database resource is available
	 *	@throws		RuntimeException	if no database resource is not implementing getConnection
	 *	@throws		RuntimeException	if no database connection is not PDO
	 *	@throws		RuntimeException	if class name for fetching to class/object is not set
	 *	@todo		make other connections than PDO possible
	 */
	protected function checkDatabaseConnection(): object
	{
		$database	= $this->env->getDatabase();
		if( NULL === $database )
			throw new RuntimeException( 'Database resource needed for '.static::class );

		if( !method_exists( $database, 'getConnection' ) )
			throw new RuntimeException( 'Database resource needs to implement getConnection' );

		/** @var object $connection */
		$connection	= $database->getConnection();
		if( !$connection instanceof PDO )
			throw new RuntimeException( 'Set up database is not a fitting PDO connection' );

		if( PDO::FETCH_CLASS === $this->fetchMode && '' === ( $this->className ?? '' ) )
			throw new RuntimeException( 'Cannot set set mode FETCH_CLASS without entity class name' );

		return $connection;
	}
}

