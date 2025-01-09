<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Handler for local module library.
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Library;

use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RegexFilter as FileRegexIndex;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Reader as ModuleReader;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface as LibraryInterface;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Abstraction as AbstractLibrary;
use Countable;
use ReflectionException;
use RuntimeException;
use SplFileObject;

/**
 *	Handler for local module library.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class Local extends AbstractLibrary implements Countable, LibraryInterface
{
	public static string $relativePathToInstalledModules	= 'modules/';
	public static string $relativePathOfCacheFile			= 'modules.cache.serial';


	protected string $modulePath;

	protected array $modules			= [];

	protected string $cacheFile;

	public static function readModuleFile( string $filePath, string $moduleId, string $modulesPath ): ModuleDefinition
	{
		$module		= ModuleReader::load( $filePath, $moduleId );
		$module->source				= 'local';													//  set source to local
		$module->path				= $modulesPath;												//  assume app path as module path
		$module->isInstalled		= TRUE;														//  module is installed
		$module->version->installed	= $module->version->current;								//  set installed version by found module version
		$module->isActive			= TRUE;														//  set active by fallback: not configured -> on (active)
		$configDictionary			= $module->getConfigAsDictionary();
		if( $configDictionary->has( 'active' ) )											//  module has main switch in config
			$module->isActive		= $configDictionary->get( 'active' );					//  set active by default main switch config value

/*		This snippet from source library is not working in local installation.
		$icon	= $entry->getPath().'/'.$moduleId;
		if( file_exists( $icon.'.png' ) )
			$module->icon	= 'data:image/png;base64,'.base64_encode( FileReader::load( $icon.'.png' ) );
		else if( file_exists( $icon.'.ico' ) )
			$module->icon	= 'data:image/x-icon;base64,'.base64_encode( FileReader::load( $icon.'.ico' ) );*/
		return $module;
	}

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment instance
	 *	@return		void
	 *	@throws		FileNotExistingException		if strict and file is not existing or given path is not a file
	 *	@throws		IoException						if strict and file is not readable
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$config				= $this->env->getConfig();
		$defaultPaths		= $this->env::$defaultPaths;
		$this->modulePath	= $env->uri.$defaultPaths['config'].static::$relativePathToInstalledModules;
		$this->cacheFile	= $env->uri.$defaultPaths['config'].static::$relativePathOfCacheFile;
		if( $config->get( 'path.module.config' ) )
			$this->modulePath	= $config->get( 'path.module.config' );
		$this->env->getRuntime()->reach( 'Resource_Module_Library_Local::construction' );
		$this->scan( (bool) $config->get( 'system.cache.modules' ) );
	}

	/**
	 *	Adds a module by module definition.
	 *	Disabled in productive environments.
	 *	@param		ModuleDefinition		$module
	 *	@return		static
	 *	@throws		NotSupportedException in productive environment
	 */
	public function add( ModuleDefinition $module ): static
	{
		if( $this->env->isInLiveMode() )
			throw new NotSupportedException( 'Adding modules dynamically is not allowed in productive environment' );

		$this->modules[$module->id]	= $module;
		return $this;
	}

	/**
	 *	Removes module cache file if enabled in base config.
	 *	@access		public
	 *	@return		void
	 */
	public function clearCache(): void
	{
		$useCache	= (bool) $this->env->getConfig()->get( 'system.cache.modules' );
		if( $useCache && file_exists( $this->cacheFile ) )
			@unlink( $this->cacheFile );
	}

	public function getPath(): string
	{
		return $this->modulePath;
	}

	/**
	 *	Scan modules of source.
	 *	Should return a data object containing the result source and number of found modules.
	 *	@access	public
	 *	@param		boolean		$useCache		Flag: use cache if available
	 *	@param		boolean		$forceReload	Flag: clear cache beforehand if available
	 *	@return		object		Data object containing the result source and number of found modules
	 *	@throws		FileNotExistingException	if strict and file is not existing or given path is not a file
	 *	@throws		IoException					if strict and file is not readable
	 */
	public function scan( bool $useCache = FALSE, bool $forceReload = FALSE ): object
	{
		if( $useCache ){
			if( $forceReload )
				$this->clearCache();
			if( file_exists( $this->cacheFile ) ){
				/** @var string $serial */
				$serial	= FileReader::load( $this->cacheFile );
				$this->modules	= unserialize( $serial );
				$this->env->getRuntime()->reach( 'Resource_Module_Library_Local::scan (cache)' );
				return (object) [
					'source' 	=> 'cache',
					'count'		=> count( $this->modules ),
				];
			}
		}

		if( !file_exists( $this->modulePath ) )
			return $this->scanResult = (object) [
				'source' 	=> 'none',
				'count'		=> 0,
			];
		$index	= new FileRegexIndex( $this->modulePath, '/^[a-z0-9_]+\.xml$/i' );
		/** @var SplFileObject $entry */
		foreach( $index as $entry ){
			/** @var string $moduleId */
			$moduleId		= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			$moduleFile		= $this->modulePath.$moduleId.'.xml';
			$module			= self::readModuleFile( $moduleFile, $moduleId, $this->modulePath );
			$this->modules[$moduleId]	= $module;
		}
		ksort( $this->modules );
		if( $useCache )
			FileWriter::save( $this->cacheFile, serialize( $this->modules ) );
		$this->env->getRuntime()->reach( 'Resource_Module_Library_Local::scan (files)' );
		return $this->scanResult = (object) [
			'source' 	=> 'files',
			'count'		=> count( $this->modules ),
		];
	}

	/**
	 *	Remove parts of loaded module definitions for security and memory reasons.
	 *	Changes are made directly to the list of loaded modules.
	 *	@access		public
	 *	@param		array		$features		List of module definition features to remove
	 *	@return		void
	 */
	public function stripFeatures( array $features ): void
	{
		if( count( $features ) === 0 )
			return;
		foreach( $this->modules as $moduleId => $module ){
			foreach( $features as $feature ){
				if( property_exists( $module, $feature ) ){
					$currentValue	= $module->{$feature};
					$newValue		= $currentValue;
					if( is_bool( $currentValue ) )
						$newValue	= FALSE;
					else if( is_array( $currentValue ) )
						$newValue	= [];
					else if( is_string( $currentValue ) )
						$newValue	= '';
					else if( is_numeric( $currentValue ) )
						$newValue	= 0;

					if( $newValue !== $currentValue )
						$this->modules[$moduleId]->{$feature}	= $newValue;
				}
			}
		}
	}
}
