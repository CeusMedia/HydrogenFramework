<?php
/**
 *	Handler for local module library.
 *
 *	Copyright (c) 2012-2019 Christian Würker (ceusmedia.de)
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
 *	@copyright		2012-2019 Christian Würker
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
 *	@copyright		2012-2019 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class CMF_Hydrogen_Environment_Resource_Module_Library_Local extends CMF_Hydrogen_Environment_Resource_Module_Library_Abstract implements CMF_Hydrogen_Environment_Resource_Module_Library_Interface{

	protected $env;
	protected $modules		= array();

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment		$env			Environment instance
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
		$config			= $this->env->getConfig();
		$envClass		= get_class( $this->env );
		$this->path		= $envClass::$configPath.'modules/';
		if( $config->get( 'path.module.config' ) )
			$this->path	= $config->get( 'path.module.config' );
		$this->path		= $env->uri.$this->path;
		$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Local::' );
		$this->scan( $config->get( 'system.cache.modules' ) );
	}

	public function callHook( $resource, $event, $context, $arguments = array() ){
		$captain	= $this->env->getCaptain();
		$countHooks	= $captain->callHook( $resource, $event, $context, $arguments );
//		remark( 'Library_Local@'.$event.': '.$countHooks );
		return $countHooks;
	}

	public function clearCache(){
		$useCache	= $this->env->getConfig()->get( 'system.cache.modules' );
		$cacheFile	= $this->path.'../modules.cache.serial';
		if( $useCache && file_exists( $cacheFile ) )
			@unlink( $cacheFile );
	}

	/**
	 *	Returns module providing class of given controller, if resolvable.
	 *	@access		public
	 *	@param		string			$controller			Name of controller class to get module for
	 *	@return		object|NULL
	 */
	public function getModuleFromControllerClassName( $controller ){
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

	public function scan( $useCache = FALSE, $forceReload = FALSE ){
		if( !file_exists( $this->path ) )
			return;

		$cacheFile	= $this->path.'../modules.cache.serial';
		if( $forceReload )
			$this->clearCache();
		if( $useCache && file_exists( $cacheFile ) ){
			$this->modules	= unserialize( FS_File_Reader::load( $cacheFile ) );
			$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Local::scan (cache)' );
			return;
		}

		$index	= new FS_File_RegexFilter( $this->path, '/^[a-z0-9_]+\.xml$/i' );
		foreach( $index as $entry ){
			$moduleId		= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			$moduleFile		= $this->path.$moduleId.'.xml';
			$module			= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $moduleFile, $moduleId );
			$module->source				= 'local';													//  set source to local
			$module->path				= $this->path;												//  assume app path as module path
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
			FS_File_Writer::save( $cacheFile, serialize( $this->modules ) );
		$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Local::scan (files)' );
	}
}
?>
