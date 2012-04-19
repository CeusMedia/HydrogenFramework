<?php
class CMF_Hydrogen_Environment_Resource_Module_Reader{
	
	static public function load( $fileName ){
		$xml	= XML_ElementReader::readFile( $fileName );
		$obj	= new stdClass();
		$obj->title					= (string) $xml->title;
		$obj->category				= (string) $xml->category;
		$obj->description			= (string) $xml->description;
		$obj->version				= (string) $xml->version;
		$obj->versionAvailable		= NULL;
		$obj->versionInstalled		= NULL;
		$obj->price					= (string) $xml->price;
		$obj->license				= (string) $xml->license;
		$obj->price					= (string) $xml->price;
		$obj->icon					= NULL;
		$obj->files					= new stdClass();
		$obj->files->classes		= array();
		$obj->files->locales		= array();
		$obj->files->templates		= array();
		$obj->files->styles			= array();
		$obj->files->scripts		= array();
		$obj->files->images			= array();
		$obj->config				= array();
		$obj->relations				= new stdClass();
		$obj->relations->needs		= array();
		$obj->relations->supports	= array();
		$obj->sql					= array();
		$obj->links					= array();
		
		$map	= array(
			'class'		=> 'classes',
			'locale'	=> 'locales',
			'template'	=> 'templates',
			'style'		=> 'styles',
			'script'	=> 'scripts',
			'image'		=> 'images',
		);
		foreach( $map as $source => $target ){
			foreach( $xml->files->$source as $file ){
				$object	= (object) array( 'file' => (string) $file );
				foreach( $file->getAttributes() as $key => $value )
					$object->$key	= $value;
				$obj->files->{$target}[]	= $object;
			}
		}
		foreach( $xml->config as $pair ){
			$key	= $pair->getAttribute( 'name' );
			$obj->config[$key]	= (object) array(
				'key'	=> $key,
				'type'	=> $pair->hasAttribute( 'type' ) ? $pair->getAttribute( 'type' ) : 'string',
				'value'	=> (string) $pair,
			);
		}
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
		foreach( $xml->link as $link )
			$obj->links[]	= (string) $link;
		return $obj;
	}
}
?>

