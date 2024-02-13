<?php
/**
 *	Interface for module libraries.
 *
 *	Copyright (c) 2012-2024 Christian Würker (ceusmedia.de)
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
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module;

/**
 *	Interface for module libraries.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
interface LibraryInterface
{
	/**
	 *	Returns a module by its ID if found in source.
	 *	By default, only active modules are enlisted.
	 *	@access		public
	 *	@param		string		$moduleId		ID of module to check in source
	 *	@param		boolean		$activeOnly		Flag: enlist only active modules (default: yes)
	 *	@param		boolean		$strict			Flag: throw exception if not found (default: yes)
	 *	@return		object|NULL
	 */
	public function get( string $moduleId, bool $activeOnly = TRUE, bool $strict = TRUE ): ?object;

	/**
	 *	Return a list of all modules found in source.
	 *	By default, only active modules are enlisted.
	 *	@access		public
	 *	@param		boolean		$activeOnly		Flag: enlist only active modules (default: yes)
	 *	@return		array
	 */
	public function getAll( bool $activeOnly = TRUE ): array;

	/**
	 *	Indicates whether source has a module by a given module ID.
	 *	By default, only active modules are enlisted.
	 *	@access		public
	 *	@param		string		$moduleId		ID of module to check in source
	 *	@param		boolean		$activeOnly		Flag: enlist only active modules (default: yes)
	 *	@return		boolean
	 */
	public function has( string $moduleId, bool $activeOnly = TRUE ): bool;

	/**
	 *	Scan modules of source.
	 *	Should return a data object containing the result source and number of found modules.
	 *	@access	public
	 *	@param		boolean		$useCache		Flag: use cache if available
	 *	@param		boolean		$forceReload	Flag: clear cache beforehand if available
	 *	@return		object		Data object containing the result source and number of found modules
	 */
	public function scan( bool $useCache = FALSE, bool $forceReload = FALSE ): object;
}
