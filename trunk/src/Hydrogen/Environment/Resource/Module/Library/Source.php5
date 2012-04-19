<?php
class CMF_Hydrogen_Environment_Resource_Module_Library_Source implements CMF_Hydrogen_Environment_Resource_Module_Library{

	protected $env;
	protected $modules		= array();
	protected $source;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env, $source ){
		$this->env		= $env;
		$this->source	= $source;
		$this->scan();
	}

	public function get( $moduleId ){
		if( $this->isInstalled( $moduleId ) )
			return $this->modulesInstalled[$moduleId];
		throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
	}

	public function getAll(){
		return $this->modules;
	}

	public function has( $moduleId ){
		return array_key_exists( $moduleId, $this->modules );
	}

	public function scan(){
		if( !file_exists( $this->source->path ) )
			throw new RuntimeException( 'Source path "'.$this->source->path.'" is not existing' );
		
		$index	= new File_RecursiveNameFilter( $this->source->path, 'module.xml' );
		foreach( $index as $entry ){
			$id		= preg_replace( '@^'.$this->source->path.'@', '', $entry->getPath() );
			$id		= str_replace( '/', '_', $id );
			$icon	= $entry->getPath().'/icon';
			try{
				$obj	= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $entry->getPathname() );
				$obj->path		= $entry->getPath();
				$obj->file		= $entry->getPathname();
				$obj->source	= $this->source->id;
				$obj->id		= $id;
				$obj->versionAvailable	= $obj->version;
				$obj->icon	= NULL;
				if( file_exists( $icon.'.png' ) )
					$obj->icon	= 'data:image/png;base64,'.base64_encode( File_Reader::load( $icon.'.png' ) );
				else if( file_exists( $icon.'.ico' ) )
					$obj->icon	= 'data:image/ico;base64,'.base64_encode( File_Reader::load( $icon.'.ico' ) );
				$list[$id]	= $obj;
			}
			catch( Exception $e ){
				$this->env->messenger->noteFailure( 'XML of available Module "'.$id.'" is broken ('.$e->getMessage().').' );
			}
		}
		ksort( $list );
		$this->modules	= $list;
	}
}
?>
