<?php
/**
 *	Abstract module libraries.
 *
 *	Copyright (c) 2018-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2018-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Library;

use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface;

use DomainException;
use RangeException;
use ReflectionException;
use RuntimeException;

/**
 *	Abstract module libraries.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Libary
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2018-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class Abstraction implements LibraryInterface
{
	protected Environment $env;

	protected array $modules		= [];

	protected ?object $scanResult	= NULL;

	/**
	 *	Trigger an event hooked by modules on load.
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
	 *	@see		\CeusMedia\HydrogenFramework\Environment\Resource\Captain#callHook() Call hook in captain resource
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object|NULL	$context		Context object, will be available inside hook as $context
	 *	@param		array|NULL	$payload		Map of hook payload data, will be available inside hook as $payload
	 *	@return		bool|NULL					TRUE if hook is chain-breaking, FALSE if hook is disabled or non-chain-breaking, NULL if no modules installed or no hooks defined
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			if method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 *	@todo		remove, since calling hooks is captains work, only - the library implementation should not be able to do this
	 */
	public function callHook( string $resource, string $event, ?object $context = NULL, array & $payload = NULL ): ?bool
	{
		$payload	??= [];
		return $this->env->getCaptain()->callHook( $resource, $event, $context ?? $this, $payload );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object|NULL	$context		Context object, will be available inside hook as $context
	 *	@param		array		$payload		Map of hook payload data, will be available inside hook as $payload and $data
	 *	@return		bool|NULL					Number of called hooks for event
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			if method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 *	@throws		ReflectionException
	 *	@todo		remove, since calling hooks is captains work, only - the library implementation should not be able to do this
	 */
	public function callHookWithPayload( string $resource, string $event, ?object $context = NULL, array & $payload = [] ): ?bool
	{
		return $this->env->getCaptain()->callHook( $resource, $event, $context ?? $this, $payload );
	}

	/**
	 *	Returns number of found (and active) modules.
	 *	@æccess		public
	 *	@return		int			Number of found (and active) modules
	 */
	public function count(): int
	{
		return count( $this->getAll() );
	}

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
	 *	Returns module providing class of given controller, if resolvable.
	 *	@access		public
	 *	@param		string			$controller			Name of controller class to get module for
	 *	@return		ModuleDefinition|NULL
	 *	@todo		this is broken by supporting uppercase parts in controller class name, provided by general dispatcher
	 *	@todo		move this method to dispatcher and adjust call in page resource
	 */
	public function getModuleFromControllerClassName( string $controller ): ?ModuleDefinition
	{
		$controllerPathName	= "Controller/".str_replace( "_", "/", $controller );
		/** @var ModuleDefinition $module */
		foreach( $this->env->getModules()->getAll() as $module ){
			foreach( $module->files->classes as $file ){
				$path	= pathinfo( $file->file, PATHINFO_DIRNAME ).'/';
				$base	= pathinfo( $file->file, PATHINFO_FILENAME );
				if( $path.$base === $controllerPathName )
					return $module;
			}
		}
		return NULL;
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
