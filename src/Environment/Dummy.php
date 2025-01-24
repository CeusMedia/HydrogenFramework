<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Empty environment for remote dummy use.
 *
 *	Copyright (c) 2012-2025 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Environment;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\HydrogenFramework\Environment\Resource\Log as LogResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;

/**
 *	Empty environment for remote dummy use.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			extend from (namespaced) Environment after all modules are migrated to 0.9
 */
class Dummy extends Environment
{
	public bool $hasDatabase		= FALSE;

	public function __construct( array $options = [] )
	{
		$this->options		= $options;
		$this->path			= $options['pathApp'] ?? getCwd().'/';
		$this->uri			= $options['pathApp'] ?? getCwd().'/';

		$this->initRuntime();																		//  setup runtime clock
		$this->config		= new Environment\Resource\Configuration( $this );						//  create empty configuration object
		$this->initModules();																		//  setup empty module handler
		$this->initLog();																			//  setup memory logger
		$this->initPhp();																			//  setup PHP environment
		$this->initCaptain();																		//  setup inactive captain (on empty module dictionary)
		$this->initLogic();																			//  setup logic pool
		$this->hasDatabase	= FALSE;

		$this->__onInit();																			//  default callback for construction end
		$this->runtime->reach( 'Environment (Web): construction end' );						//  log time of construction
	}

	/**
	 *	Log in memory, only.
	 *	@return		static
	 */
	protected function initLog(): static
	{
		$this->log	= new LogResource( $this );
		$this->log->setStrategies( [LogResource::STRATEGY_MEMORY] );
		return $this;
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
