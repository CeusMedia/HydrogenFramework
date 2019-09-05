<?php
/**
 *	Handler for local modules.
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2019 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Handler for local modules.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2019 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@deprecated		since CMF_Hydrogen_Environment_Resource_Module_Library_Local is used within app instances
 *	@todo			to be removed in 0.8.7
 */
class CMF_Hydrogen_Environment_Resource_Module_Handler{

	protected $modulesInstalled		= array();
	protected $modulesAvailable		= array();
	protected $sources				= array();

	public function __construct(CMF_Hydrogen_Environment $env ){
		CMF_Hydrogen_Deprecation::getInstance()
			->setErrorVersion( '0.8.6.5' )
			->setExceptionVersion( '0.8.7' )
			->message( 'Please use CMF_Hydrogen_Environment_Resource_Module_Library_Local instead' );

		$this->env		= $env;
		$config			= $this->env->getConfig();

#		if( class_exists( 'Model_Source' ) ){
#			$model	= new Model_Source( $env );
#			foreach( $model->getAll() as $source )
#				$this->sources[$source->id]	= $source;
#		}
#
		$this->path		= 'config/modules/';
		if( $config->get( 'path.module.config' ) )
			$this->path	= $config->get( 'path.module.config' );
		if( !file_exists( $this->path ) )
			return;

		$this->modulesInstalled	= new CMF_Hydrogen_Environment_Resource_Module_LibraryLocal( $env );
	}

	public function clearCache(){
		$this->modulesInstalled->clearCache();
	}

	public function get( $moduleId, $installed = FALSE ){
		if( $installed ){
			if( $this->isInstalled( $moduleId ) )
				return $this->modulesInstalled[$moduleId];
			throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
		}
		if( $this->isAvailable( $moduleId ) )
			return $this->modulesAvailable[$moduleId];
		throw new RuntimeException( 'Module "'.$moduleId.'" is not available' );
	}

	public function getInstalled(){
		return $this->modulesInstalled;
	}
	public function has( $moduleId, $installed = FALSE ){
		$source	= $installed ? $this->modulesInstalled : $this->modulesAvailable;
		return array_key_exists( $moduleId, $source );
	}

	public function isActive( $moduleId ){
		return array_key_exists( $moduleId, $this->modulesAvailable );
	}

	public function isAvailable( $moduleId ){
		return array_key_exists( $moduleId, $this->modulesAvailable );
	}

	public function isInstalled( $moduleId ){
		return array_key_exists( $moduleId, $this->modulesInstalled );
	}

	protected function readModuleXml( $moduleId, $sourceId = NULL ){
		$fileName	= $this->path.$moduleId.'.xml';
		if( $sourceId ){
			if( empty( $this->sources[$sourceId] ) )
				throw new RuntimeException( 'Module source "'.$sourceId.'" is not existing' );
			$fileName	= $this->sources[$sourceId]->path.$moduleId.'.xml';
		}
		if( !file_exists( $fileName ) )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not available in source "'.$sourceId.'"' );
		return CMF_Hydrogen_Environment_Resource_Module_Reader::load( $fileName, $moduleId );
	}
}
?>
