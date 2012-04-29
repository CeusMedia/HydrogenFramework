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
		$obj->companies				= array();
		$obj->authors				= array();
		$obj->licenses				= array();
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

		foreach( $xml->license as $license ){
			$source	= $license->hasAttribute( 'source' ) ? $license->getAttribute( 'source' ) : '';
			$obj->licenses[]	= (object) array(
				'label'		=> (string) $license,
				'source'	=> $source
			);
		}

		foreach( $xml->company as $company ){
			$site	= $company->hasAttribute( 'site' ) ? $company->getAttribute( 'site' ) : '';
			$obj->companies[]	= (object) array(
				'label'		=> (string) $company,
				'site'		=> $site
			);
		}

		foreach( $xml->author as $author ){
			$email	= $author->hasAttribute( 'email' ) ? $author->getAttribute( 'email' ) : '';
			$site	= $author->hasAttribute( 'site' ) ? $author->getAttribute( 'site' ) : '';
			$obj->authors[]	= (object) array(
				'name'	=> (string) $author,
				'email'	=> $email,
				'site'	=> $site
			);
		}

		foreach( $xml->config as $pair ){
			$key	= $pair->getAttribute( 'name' );
			$type	= $pair->hasAttribute( 'type' ) ? $pair->getAttribute( 'type' ) : 'string';
			$value	= trim( (string) $pair );
			if( in_array( strtolower( $type ), array( 'boolean', 'bool' ) ) )						//  value is boolean
				$value	= !in_array( strtolower( $value ), array( 'no', 'false', '0', '' ) );		//  value is not negative
			$obj->config[$key]	= (object) array(
				'key'	=> trim( $key ),
				'type'	=> trim( strtolower( $type ) ),
				'value'	=> $value,
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

		foreach( $xml->link as $link ){
			$access		= $link->hasAttribute( 'access' ) ? $link->getAttribute( 'access' ) : 'public';
			$language	= $link->hasAttribute( 'lang', 'xml' ) ? $link->getAttribute( 'lang', 'xml' ) : 'en';
			$label		= (string) $link;
			$path		= $link->hasAttribute( 'path' ) ? $link->getAttribute( 'path' ) : $label;
			$rank		= $link->hasAttribute( 'rank' ) ? (int) $link->getAttribute( 'rank' ) : 10;
			$link		= $link->hasAttribute( 'link' ) ? $link->getAttribute( 'link' ) : $path;
			$obj->links[]	= (object) array(
				'access'	=> $access,
				'language'	=> $language,
				'path'		=> $path,
				'link'		=> $link,
				'rank'		=> $rank,
				'label'		=> $label,
			);
			(string) $link;
		}
		return $obj;
	}
}
?>