<?php
class CMF_Hydrogen_Environment_Resource_Module_Handler{

	protected $modulesInstalled		= array();
	protected $modulesAvailable		= array();

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$config			= $this->env->getConfig();
		$this->path		= 'config/modules/';
		$this->pathlib	= 'modules/';
		if( $config->get( 'path.module.config' ) )
			$this->path	= $config->get( 'path.module.config' );
		if( !file_exists( $this->path ) )
			return;

		$index	= new File_RegexFilter( $this->path, '/^[a-z_]+\.xml$/i' );
		foreach( $index as $entry ){
			$moduleId	= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			$module		= $this->readModuleXml( $moduleId, TRUE );
			$this->modulesInstalled[$moduleId]	= $module;
		}
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
	
	public function isAvailable( $moduleId ){
		return array_key_exists( $moduleId, $this->modulesAvailable );
	}
	
	public function isInstalled( $moduleId ){
		return array_key_exists( $moduleId, $this->modulesInstalled );
	}

	protected function readModuleXml( $moduleId, $installed = FALSE ){
		if( $installed )
			$fileName	= $this->path.$moduleId.'.xml';
		else
			$fileName	= $this->pathlib.$moduleId.'/module.xml';

		return CMF_Hydrogen_Environment_Resource_Module_Reader::load( $fileName );
	}
}
?>