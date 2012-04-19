<?php
interface CMF_Hydrogen_Environment_Resource_Module_Library{
	
}
class CMF_Hydrogen_Environment_Resource_Module_Library_Local implements CMF_Hydrogen_Environment_Resource_Module_Library{

	protected $modules		= array();

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$config			= $this->env->getConfig();
		$this->path		= 'config/modules/';
		if( $config->get( 'path.module.config' ) )
			$this->path	= $config->get( 'path.module.config' );
		$this->path		= $env->path.$this->path;
		if( !file_exists( $this->path ) )
			return;
		$index	= new File_RegexFilter( $this->path, '/^[a-z_]+\.xml$/i' );
		foreach( $index as $entry ){
			$moduleId		= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			$moduleFile		= $this->path.$moduleId.'.xml';
			$module			= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $moduleFile );
			$module->source	= 'local';
			$module->id		= $moduleId;
			$module->versionInstalled	= $module->version;
			$this->modules[$moduleId]	= $module;
		}
	}

	public function get( $moduleId ){
		if( $this->has( $moduleId ) )
			return $this->modules[$moduleId];
		throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
	}

	public function getAll(){
		return $this->modules;
	}

	public function has( $moduleId ){
		return array_key_exists( $moduleId, $this->modules );
	}
}
?>
