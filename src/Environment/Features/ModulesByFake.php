<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface;

trait ModulesByFake
{
	/**	@var	LibraryInterface			$modules		Handler for installed modules */
	protected LibraryInterface $modules;

	/**
	 *	Returns initially set up handler for (usually local) module library.
	 *	@access		public
	 *	@return		LibraryInterface
	 */
	public function getModules(): LibraryInterface
	{
		return $this->modules;
	}

	/**
	 *	@param		string		$moduleId
	 *	@return		bool
	 */
	public function hasModule( string $moduleId ): bool
	{
		return FALSE;
	}

	/**
	 *	@return		bool
	 */
	public function hasModules(): bool
	{
		return FALSE;
	}

	/**
	 *	Sets up a dysfunctional handler for local installed modules.
	 *	@access		protected
	 *	@return		static
	 */
	protected function initModules(): static
	{
		LocalModuleLibraryResource::$relativePathToInstalledModules	= 'NotExistingPath/'.ID::uuid().'/';
		$this->modules	= new LocalModuleLibraryResource( $this );
		return $this;
	}
}
