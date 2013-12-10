<?php
/**
 *	Handler for local module library.
 *
 *	Copyright (c) 2012 Christian Würker (ceusmedia.com)
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
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Module.Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
/**
 *	Handler for local module library.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Module.Library
 *	@implements		CMF_Hydrogen_Environment_Resource_Module_Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 *	@todo			Code Documentation
 */
class CMF_Hydrogen_Environment_Resource_Module_Library_Local implements CMF_Hydrogen_Environment_Resource_Module_Library{

	protected $env;
	protected $modules		= array();

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$config			= $this->env->getConfig();
		$this->path		= 'config/modules/';
		if( $config->get( 'path.module.config' ) )
			$this->path	= $config->get( 'path.module.config' );
		$this->path		= $env->path.$this->path;
		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::init' );
		$this->scan( $config->get( 'system.cache.modules' ) );
		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::scan' );
	}

	public function callHook( $resource, $event, $context, $arguments = array() ){
		$count	= 0;
		foreach( $this->modules as $module ){
			if( empty( $module->hooks[$resource][$event] ) )
				continue;
			$function	= $module->hooks[$resource][$event];
			$pattern	= "/^([a-z0-9_]+)::([a-z0-9_]+)$/i";
			if( preg_match( $pattern, $function ) ){
				$className	= preg_replace( $pattern, "\\1", $function );
				$methodName	= preg_replace( $pattern, "\\2", $function );
				$function	= array( $className, $methodName );
			}
			else
				$function	= create_function( '$env, $context, $module, $arguments = array()', $function );
			try{
				$count++;
				ob_start();
				$args	= array( $this->env, &$context, $module, $arguments );
				call_user_func_array( $function, $args );
				$stdout	= ob_get_clean();
				if( strlen( $stdout ) )
					if( $this->env->has( 'messenger' ) )
						$this->env->getMessenger()->noteNotice( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' reported: '.$stdout );
					else
						throw new RuntimeException( $stdout );
			}
			catch( Exception $e ){
				if( $this->env->has( 'messenger' ) )
					$this->env->getMessenger()->noteFailure( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' failed: '.$e->getMessage() );
				else
					throw new RuntimeException( 'Hook '.$module->id.'::'.$resource.'@'.$event.' failed', 0, $e );
			}
		}
		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::callHook' );
		return $count;
	}

	public function clearCache(){
		$useCache	= $this->env->getConfig()->get( 'system.cache.modules' );
		$cacheFile	= $this->path.'../modules.cache.serial';
		if( $useCache && file_exists( $cacheFile ) )
			@unlink( $cacheFile );
	}

	public function get( $moduleId ){
		if( !$this->has( $moduleId ) )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
		return $this->modules[$moduleId];
	}

	public function getAll(){
		return $this->modules;
	}

	public function has( $moduleId ){
		return array_key_exists( $moduleId, $this->modules );
	}
	
	public function scan( $useCache = FALSE, $forceReload = FALSE ){
		if( !file_exists( $this->path ) )
			return;

		$cacheFile	= $this->path.'../modules.cache.serial';
		if( $forceReload )
			$this->clearCache();
		if( $useCache && file_exists( $cacheFile ) ){
			$this->modules	= unserialize( File_Reader::load( $cacheFile ) );
			return;
		}

		$index	= new File_RegexFilter( $this->path, '/^[a-z0-9_]+\.xml$/i' );
		foreach( $index as $entry ){
			$moduleId		= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			$moduleFile		= $this->path.$moduleId.'.xml';
			$module			= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $moduleFile, $moduleId );
			$module->uri	= $moduleFile;
			$module->source	= 'local';
			$module->id		= $moduleId;
			$module->versionInstalled	= $module->version;

			$icon	= $entry->getPath().'/'.$moduleId;
			if( file_exists( $icon.'.png' ) )
				$module->icon	= 'data:image/png;base64,'.base64_encode( File_Reader::load( $icon.'.png' ) );
			else if( file_exists( $icon.'.ico' ) )
				$module->icon	= 'data:image/x-icon;base64,'.base64_encode( File_Reader::load( $icon.'.ico' ) );
			
			$this->modules[$moduleId]	= $module;
		}
		ksort( $this->modules );
		if( $useCache )
			File_Writer::save( $cacheFile, serialize( $this->modules ) );
	}
}
?>
