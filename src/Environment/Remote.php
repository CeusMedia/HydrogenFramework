<?php
/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *
 *	Copyright (c) 2012-2022 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Remote\Messenger;

/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			is a web environment needed instead? try to avoid this - maybe a console messenger needs to be implemented therefore
 *	@todo			finish path resolution (path is set twice at the moment)
 *	@todo			extend from (namespaced) Environment after all modules are migrated to 0.9
 */
class Remote extends Environment
{
	/**	@var	boolean		$hasDatabase		Flag: indicates availability of a database connection */
	public bool $hasDatabase		= FALSE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		array		$options		Map of environment options
	 *	@return		void
	 */
	public function __construct( array $options = [] )
	{
//		self::$defaultPaths	= Environment::$defaultPaths;
		$this->options	= $options;
		$this->path		= $options['pathApp'] ?? getCwd() . '/';
		$this->uri		= $options['pathApp'] ?? getCwd() . '/';											//

		Loader::registerNew( 'php5', NULL, $this->path.'classes/' );								//  enable autoloader for remote app classes

		self::$configFile	= $this->path."/config/config.ini";

		$this->initRuntime();																		//  setup clock
		$this->initMessenger();																		//  setup user interface messenger
		$this->initConfiguration();																	//  setup configuration
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		$this->initLanguage();
		$this->initLogic();

		$this->hasDatabase	= (bool) $this->database;													//  note if database is available
		$this->__onInit();
	}

	/**
	 *	Close remote environment and keep calling client application alive.
	 *	@access		public
	 *	@param		array		$additionalResources	Not used in remote environment
	 *	@param		boolean		$keepAppAlive			Not used in remote environment
	 *	@return		void
	 */
	public function close( array $additionalResources = [], bool $keepAppAlive = FALSE )
	{
		parent::close( [], FALSE );															//  unbind bound resources but keep application alive
	}

	public function getMessenger(): Messenger
	{
		return $this->messenger;
	}

	public function initMessenger()
	{
		$this->messenger	= new Messenger( $this );
	}
}
