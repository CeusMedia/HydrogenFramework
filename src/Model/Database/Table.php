<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Generic Model Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2024 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Model\Database;

use CeusMedia\Database\PDO\Connection as PdoConnection;
use CeusMedia\Database\PDO\Table as PdoDatabaseTable;
use CeusMedia\HydrogenFramework\Environment;

use PDO;
use RuntimeException;

/**
 *	Generic Model Class of Framework Hydrogen.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Table extends PdoDatabaseTable
{
	/**	@var	Environment				$env			Application Environment Object */
	protected Environment $env;

	protected ?string $className		= NULL;

	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
		$connection	= $this->env->getDatabase();

		if( NULL !== $connection && method_exists( $connection, 'getConnection' ) )
			$connection	= $connection->getConnection();

		if( !$connection instanceof PdoConnection )
			throw new RuntimeException( 'Set up database is not a fitting PDO connection' );

		parent::__construct( $connection );
		if( PDO::FETCH_CLASS === $this->fetchMode ){
			if( '' === ( $this->className ?? '' ) )
				throw new RuntimeException( 'Cannot set set mode FETCH_CLASS without entity class name' );
			$this->setFetchEntityClass( $this->className );
		}
		$this->cacheKey	= 'db.'.$this->prefix.$this->name.'.';
	}

	//  --  PROTECTED  --  //

	/**
	 *	Sets Environment of Controller by copying Framework Member Variables.
	 *	@access		protected
	 *	@param		Environment			$env			Application Environment Object
	 *	@return		self
	 *	@throws		RuntimeException	if no database resource is available in given environment
	 */
	protected function setEnv( Environment $env ): self
	{
		$this->env		= $env;
		$database		= $env->getDatabase();
		if( NULL === $database )
			throw new RuntimeException( 'Database resource needed for '.static::class );
		if( method_exists( $database, 'getPrefix' ) )
			$this->prefix	= $database->getPrefix();

		return $this;
	}
}

