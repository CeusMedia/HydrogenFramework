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

		$index	= new File_RegexFilter( $this->path, '/^[a-z]+\.xml$/i' );
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
			
		$clock	= new Alg_Time_Clock();
		$xml	= XML_ElementReader::readFile( $fileName );
		$obj	= new stdClass();
		$obj->title				= (string) $xml->title;
		$obj->description		= (string) $xml->description;
		$obj->files				= new stdClass();
		$obj->files->classes	= array();
		$obj->files->locales	= array();
		$obj->files->templates	= array();
		$obj->files->styles		= array();
		$obj->files->scripts	= array();
		$obj->files->images		= array();
		$obj->config			= array();
		$obj->version			= (string) $xml->version;
		$obj->versionAvailable	= NULL;
		$obj->versionInstalled	= NULL;
		$obj->relations			= new stdClass();
		$obj->relations->needs		= array();
		$obj->relations->supports	= array();
		$obj->sql				= array();
		foreach( $xml->files->class as $link )
			$obj->files->classes[]	= (string) $link;
		foreach( $xml->files->locale as $link )
			$obj->files->locales[]	= (string) $link;
		foreach( $xml->files->template as $link )
			$obj->files->templates[]	= (string) $link;
		foreach( $xml->files->style as $link )
			$obj->files->styles[]	= (string) $link;
		foreach( $xml->files->script as $link )
			$obj->files->scripts[]	= (string) $link;
		foreach( $xml->files->image as $link )
			$obj->files->images[]	= (string) $link;
		foreach( $xml->config as $pair )
			$obj->config[$pair->getAttribute( 'name' )]	= (string) $pair;
		if( $xml->relations ){
			foreach( $xml->relations->needs as $moduleName )
				$obj->relations->needs[]	= (string) $moduleName;
			foreach( $xml->relations->supports as $moduleName )
				$obj->relations->supports[]	= (string) $moduleName;
		}
		foreach( $xml->sql as $sql ){
			$event	= $sql->getAttribute( 'on' );
			$type	= $sql->hasAttribute( 'type' ) ? $sql->getAttribute( 'type' ) : '*';
			foreach( explode( ',', $type ) as $type ){
				$key	= $event.'@'.$type;
				$obj->sql[$key]	= (string) $sql;
			}
		}
#		remark( $fileName.': '.$clock->stop( 3, 1 ).'ms' );
		return $obj;
	}
}
?>