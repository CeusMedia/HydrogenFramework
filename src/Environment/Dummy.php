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

use CeusMedia\HydrogenFramework\Environment;

use CeusMedia\HydrogenFramework\Environment\Features\ConfigByDummy as ConfigByDummyFeature;
use CeusMedia\HydrogenFramework\Environment\Features\ModulesByFake as ModulesByFakeFeature;
//use CeusMedia\HydrogenFramework\Environment\Features\RequestByFake as RequestByFakeFeature;
//use CeusMedia\HydrogenFramework\Environment\Features\SessionByDictionary as SessionByDictionaryFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Log as LogResource;

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
	use ConfigByDummyFeature;
	use ModulesByFakeFeature;
//	use RequestByFakeFeature;
//	use SessionByDictionaryFeature;

	public bool $hasDatabase		= FALSE;

	/**
	 * @param array $options
	 * @todo is session feature needed? ATM it is not called
	 */
	public function __construct( array $options = [], bool $isFinale = FALSE )
	{
		static::$defaultLoggingStrategies	= [LogResource::STRATEGY_MEMORY];

		$this->options		= $options;
		$this->path			= $options['pathApp'] ?? getCwd().'/';
		$this->uri			= $options['pathApp'] ?? getCwd().'/';

		$this->initRuntime();																		//  setup runtime clock
		$this->initConfig();																		//  setup runtime clock
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
	 *	@param		array		$options 			@todo: doc
	 *	@param		boolean		$isFinal			Flag: there is no extending environment class, default: TRUE
	 *	@return		void
	 */
	protected function runBaseEnvConstruction( array $options = [], bool $isFinal = TRUE ): void
	{
		//	Right now, there is no need to call the base environment construction
		//  @see this method in all other environment implementations
		//
	}
}
