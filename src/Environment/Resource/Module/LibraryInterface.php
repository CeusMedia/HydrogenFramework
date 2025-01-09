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
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Module;

use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use Countable;

/**
 *	Interface for module libraries.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
interface LibraryInterface extends Countable
{
	/**
	 *	Trigger an event hooked by modules on load.
	 *
	 *	Events are defined by a resource key (= a scope) and an event key (= a trigger key).
	 *	Example: resource "Auth" and event "onLogout" will call all hook class methods, defined in
	 *	module definitions by <code><hook resource="Auth" event"onLogout">MyModuleHook::onAuthLogout</hook></code>.
	 *
	 *	There are 2 ways of carrying data between the hooked callback method and the calling object: context and payload.
	 *
	 * 	The context can provide a prepared data object or the calling object itself to the hook callback method.
	 *	The hook can read from and write into this given context object.
	 *
	 * 	The more strict way is to use a prepared payload list reference, which is a prepared array.
	 *	The payload list is an array (map) to work on within the hook callback method.
	 *	The calling object method can interpret/use the payload changes afterward.
	 *
	 *	@access		public
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object|NULL	$context		Context object, will be available inside hook as $context
	 *	@return		bool|NULL					TRUE if hook is chain-breaking, FALSE if hook is disabled or non-chain-breaking, NULL if no modules installed or no hooks defined
	 *	@todo		remove arguments #3 and #4 after updating all calls in all modules (v1.0.1)
	 *	@todo		remove, since calling hooks is captains work, only - the library implementation should not be able to do this
	 */
	public function callHook( string $resource, string $event, ?object $context = NULL, array & $payload = [] ): ?bool;

	/**
	 *	Trigger an event hooked by modules on load.
	 *
	 *	Events are defined by a resource key (= a scope) and an event key (= a trigger key).
	 *	Example: resource "Auth" and event "onLogout" will call all hook class methods, defined in
	 *	module definitions by <code><hook resource="Auth" event"onLogout">MyModuleHook::onAuthLogout</hook></code>.
	 *
	 *	There are 2 ways of carrying data between the hooked callback method and the calling object: context and payload.
	 *
	 * 	The context can provide a prepared data object or the calling object itself to the hook callback method.
	 *	The hook can read from and write into this given context object.
	 *
	 * 	The more strict way is to use a prepared payload list reference, which is a prepared array.
	 *	The payload list is an array (map) to work on within the hook callback method.
	 *	The calling object method can interpret/use the payload changes afterward.
	 *
	 *	@access		public
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object|NULL	$context		Context object, will be available inside hook as $context
	 *	@param		array		$payload		Map of hook payload data, will be available inside hook as $payload and $data
	 *	@return		bool|NULL					TRUE if hook is chain-breaking, FALSE if hook is disabled or non-chain-breaking, NULL if no modules installed or no hooks defined
	 *	@todo		remove, since calling hooks is captains work, only - the library implementation should not be able to do this
	 */
	public function callHookWithPayload( string $resource, string $event, ?object $context = NULL, array & $payload = [] ): ?bool;

	public function count(): int;

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
	 *	Returns module providing class of given controller, if resolvable.
	 *	@access		public
	 *	@param		string			$controller			Name of controller class to get module for
	 *	@return		ModuleDefinition|NULL
	 *	@todo		this is broken by supporting uppercase parts in controller class name, provided by general dispatcher
	 *	@todo		move this method to dispatcher and adjust call in page resource
	 */
	public function getModuleFromControllerClassName( string $controller ): ?ModuleDefinition;

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
