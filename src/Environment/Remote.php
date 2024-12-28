<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Setup for Resource Environment for Hydrogen Applications.
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
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as BaseMessenger;
use CeusMedia\HydrogenFramework\Environment\Resource\Remote\Messenger;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentExceptionAlias;
use ReflectionException;

/**
 *	Setup for Resource Environment for Hydrogen Applications.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			is a web environment needed instead? try to avoid this - maybe a console messenger needs to be implemented therefore
 *	@todo			finish path resolution (path is set twice at the moment)
 *	@todo			extend from (namespaced) Environment after all modules are migrated to 0.9
 */
class Remote extends Environment
{
	/**	@var	boolean		$hasDatabase		Flag: indicates availability of a database connection */
	public bool $hasDatabase		= FALSE;

	/**	@var	Dictionary					$request		Request Object */
	private Dictionary $request;

	/**	@var	Dictionary					$session		Session Object */
	private Dictionary $session;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		array		$options		Map of environment options
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentExceptionAlias
	 */
	public function __construct( array $options = [] )
	{
		parent::__construct( $options, FALSE );
//		self::$defaultPaths	= Environment::$defaultPaths;
		$this->options	= $options;
		$this->path		= $options['pathApp'] ?? getCwd() . '/';
		$this->uri		= $options['pathApp'] ?? getCwd() . '/';											//

		Loader::create( 'php', $this->path.'classes/' )->register();					//  enable autoloader for remote app classes

		self::$configFile	= $this->path."/config/config.ini";

		$this->initRequest();
		$this->initSession();
		$this->initMessenger();																		//  setup user interface messenger
		$this->initLanguage();

		$this->hasDatabase	= (bool) $this->database;													//  note if database is available
		$this->__onInit();
	}

	/**
	 *	Close remote environment and keep calling client application alive.
	 *	@access		public
	 *	@param		array		$additionalResources	List of resource member names to be unbound, too
	 *	@param		boolean		$keepAppAlive			Flag: do not end execution right now if turned on
	 *	@return		void
	 */
	public function close( array $additionalResources = [], bool $keepAppAlive = FALSE ): void
	{
		parent::close( array_merge( [																//  delegate closing with these resources, too
			'request',																				//  remote request handler
			'session',																				//  remote session handler
			'messenger',																			//  application message handler
			'language',																				//  language handler
		], array_values( $additionalResources ) ), $keepAppAlive );									//  add additional resources and carry exit flag
	}

	public function getMessenger(): ?BaseMessenger
	{
		return $this->messenger;
	}

	/**
	 *	Returns Request Object.
	 *	@access		public
	 *	@return		Dictionary
	 */
	public function getRequest(): Dictionary
	{
		return $this->request ?? new Dictionary();
	}

	/**
	 *	Returns Session Object.
	 *	@access		public
	 *	@return		Dictionary
	 */
	public function getSession(): Dictionary
	{
		return $this->session;
	}

	/**
	 * @return static
	 */
	protected function initMessenger(): static
	{
		$this->messenger	= new Messenger( $this );
		return $this;
	}

	/**
	 * @return static
	 */
	protected function initRequest(): static
	{
		$this->request	= new Dictionary();
		return $this;
	}

	/**
	 * @return static
	 */
	protected function initSession(): static
	{
		$this->session	= new Dictionary();
		return $this;
	}
}
