<?php
/**
 *	Abstract module libraries.
 *
 *	Copyright (c) 2018-2024 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Library;

use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface;

use RangeException;
use RuntimeException;

/**
 *	Abstract module libraries.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class Abstraction implements LibraryInterface
{
	protected array $modules		= [];

	protected ?object $scanResult;

	/**
	 *	Return module definition by module ID.
	 *	Returns NULL if module is not installed and strict mode is off.
	 *	Returns NULL if module is not active and strict mode is off and activeOnly is on.
	 *	@access		public
	 *	@param		string		$moduleId		ID of module to get definition for
	 *	@param		boolean		$activeOnly		Flag: exclude deactivated modules (default: yes)
	 *	@param		boolean		$strict			Flag: throw exception if not installed (default: yes)
	 *	@return		ModuleDefinition|NULL
	 *	@throws		RangeException				if module is not installed (using strict mode)
	 *	@throws		RuntimeException			if module is not active (using strict mode and activeOnly)
	 */
	public function get( string $moduleId, bool $activeOnly = TRUE, bool $strict = TRUE ): ?ModuleDefinition
	{
		$moduleId	= $this->sanitizeId( $moduleId );
		if( !array_key_exists( $moduleId, $this->modules ) ){										//  module is not installed
			if( $strict )
				throw new RangeException( 'Module "'.$moduleId.'" is not installed' );
			return NULL;
		}
		if( $activeOnly && !$this->modules[$moduleId]->isActive ){
			if( $strict )
				throw new RuntimeException( 'Module "'.$moduleId.'" is not active' );
			return NULL;
		}
		return $this->modules[$moduleId];
	}

	/**
	 *	Return list of all installed (and active) modules.
	 *	@access		public
	 *	@param		boolean		$activeOnly		Flag: exclude deactivated modules (default: yes)
	 *	@return		array
	 */
	public function getAll( bool $activeOnly = TRUE ): array
	{
		if( !$activeOnly )
			return $this->modules;
		$modules	= [];
		foreach( $this->modules as $module )
			if( $module->isActive )
				$modules[$module->id]	= $module;
		return $modules;
	}

	/**
	 *	@access		public
	 *	@return		object
	 *	@todo		remove after interface changed not auto-scanning on construction
	 */
	public function getScanResults(): object
	{
		if( NULL === $this->scanResult )
			$this->scanResult = (object) [
				'source'	=> 'unscanned',
				'count'		=> 0,
			];
		return $this->scanResult;
	}

	/**
	 *	Indicate whether module is installed and active by module ID.
	 *	Returns false if module is not installed.
	 *	Returns false if module is installed and main switch "active" is existing and disabled.
	 *	Returns true otherwise.
	 *	@access		public
	 *	@param		string		$moduleId		ID of module to check for existence (and activity)
	 *	@param		boolean		$activeOnly		Flag: exclude deactivated modules (default: yes)
	 *	@return		boolean
	 */
	public function has( string $moduleId, bool $activeOnly = TRUE ): bool
	{
		$moduleId	= $this->sanitizeId( $moduleId );
		if( !array_key_exists( $moduleId, $this->modules ) )										//  module is not installed
			return FALSE;																			//
		if( $activeOnly )																			//  activity check is needed
			return $this->modules[$moduleId]->isActive;												//  return activity
		return TRUE;																				//  otherwise
	}

	//  --  PROTECTED  --  //

	protected function sanitizeId( string $moduleId ): string
	{
		return str_replace( ':', '_', $moduleId );
	}
}
