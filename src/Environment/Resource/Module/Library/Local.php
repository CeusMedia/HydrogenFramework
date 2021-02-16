<?php
/**
 *	Handler for local module library.
 *
 *	Copyright (c) 2012-2021 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Handler for local module library.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@extends		CMF_Hydrogen_Environment_Resource_Module_Library_Abstract
 *	@implements		CMF_Hydrogen_Environment_Resource_Module_Library_Interface
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class CMF_Hydrogen_Environment_Resource_Module_Library_Local extends CMF_Hydrogen_Environment_Resource_Module_Library_Abstract implements CMF_Hydrogen_Environment_Resource_Module_Library_Interface
{
	protected $env;
	protected $modulePath;
	protected $modules		= array();
	protected $cacheFile;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment		$env			Environment instance
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env			= $env;
		$config				= $this->env->getConfig();
		$envClass			= get_class( $this->env );
		$this->modulePath	= $env->path.$envClass::$configPath.'modules/';
		$this->cacheFile	= $this->modulePath.'../modules.cache.serial';
		if( $config->get( 'path.module.config' ) )
			$this->modulePath	= $config->get( 'path.module.config' );
		$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Local::' );
		$this->scan( (bool) $config->get( 'system.cache.modules' ) );
	}

	/**
	 *	@todo		check if this is needed anymore and remove otherwise
	 */
	public function callHook( string $resource, string $event, $context, $arguments = array() )
	{
		$captain	= $this->env->getCaptain();
		$countHooks	= $captain->callHook( $resource, $event, $context, $arguments );
//		remark( 'Library_Local@'.$event.': '.$countHooks );
		return $countHooks;
	}

	/**
	 *	Removes module cache file if enabled in base config.
	 *	@access		public
	 */
	public function clearCache()
	{
		$useCache	= $this->env->getConfig()->get( 'system.cache.modules' );
		if( $useCache && file_exists( $this->cacheFile ) )
			@unlink( $this->cacheFile );
	}

	/**
	 *	Returns module providing class of given controller, if resolvable.
	 *	@access		public
	 *	@param		string			$controller			Name of controller class to get module for
	 *	@return		object|NULL
	 */
	public function getModuleFromControllerClassName( string $controller )
	{
		$controllerPathName	= "Controller/".str_replace( "_", "/", $controller );
		foreach( $this->env->getModules()->getAll() as $module ){
			foreach( $module->files->classes as $file ){
				$path	= pathinfo( $file->file, PATHINFO_DIRNAME ).'/';
				$base	= pathinfo( $file->file, PATHINFO_FILENAME );
				if( $path.$base === $controllerPathName )
					return $module;
			}
		}
	}

	/**
	 *	Scan modules of source.
	 *	Should return a data object containing the result source and number of found modules.
	 *	@access	public
	 *	@param		boolean		$useCache		Flag: use cache if available
	 *	@param		boolean		$forceReload	Flag: clear cache beforehand if available
	 *	@return		object		Data object containing the result source and number of found modules
	 */
	public function scan( bool $useCache = FALSE, bool $forceReload = FALSE )
	{
		if( $useCache ){
			if( $forceReload )
				$this->clearCache();
			if( file_exists( $this->cacheFile ) ){
				$this->modules	= unserialize( FS_File_Reader::load( $this->cacheFile ) );
				$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Local::scan (cache)' );
				return (object) array(
					'source' 	=> 'cache',
					'count'		=> count( $this->modules ),
				);
			}
		}

		if( !file_exists( $this->modulePath ) )
			return $this->scanResult = (object) array(
				'source' 	=> 'none',
				'count'		=> 0,
			);
		$index	= new FS_File_RegexFilter( $this->modulePath, '/^[a-z0-9_]+\.xml$/i' );
		foreach( $index as $entry ){
			$moduleId		= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			$moduleFile		= $this->modulePath.$moduleId.'.xml';
			$module			= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $moduleFile, $moduleId );
			$module->source				= 'local';													//  set source to local
			$module->path				= $this->modulePath;												//  assume app path as module path
			$module->isInstalled		= TRUE;														//  module is installed
			$module->versionInstalled	= $module->version;											//  set installed version by found module version
			if( isset( $module->config['active'] ) )												//  module has main switch in config
				$module->isActive		= $module->config['active']->value;							//  set active by default main switch config value

/*			This snippet from source library is not working in local installation.
			$icon	= $entry->getPath().'/'.$moduleId;
			if( file_exists( $icon.'.png' ) )
				$module->icon	= 'data:image/png;base64,'.base64_encode( FS_File_Reader::load( $icon.'.png' ) );
			else if( file_exists( $icon.'.ico' ) )
				$module->icon	= 'data:image/x-icon;base64,'.base64_encode( FS_File_Reader::load( $icon.'.ico' ) );*/

			$this->modules[$moduleId]	= $module;
		}
		ksort( $this->modules );
		if( $useCache )
			FS_File_Writer::save( $this->cacheFile, serialize( $this->modules ) );
		$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Local::scan (files)' );
		return $this->scanResult = (object) array(
			'source' 	=> 'files',
			'count'		=> count( $this->modules ),
		);
	}

	/**
	 *	Remove parts of loaded module definitions for security and memory reasons.
	 *	Changes are made directly to the list of loaded modules.
	 *	@access		public
	 *	@param		array		$features		List of module definition features to remove
	 */
	public function stripFeatures( array $features )
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
						$newValue	= array();
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
