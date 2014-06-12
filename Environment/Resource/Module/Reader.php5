<?php
/**
 *	Reader for local module XML files.
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
 *	@package		Hydrogen.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
/**
 *	Reader for local module XML files.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Module_Reader{

	static public function load( $fileName, $id ){
		$xml	= XML_ElementReader::readFile( $fileName );
		$obj	= new stdClass();
		$obj->id					= $id;
		$obj->title					= (string) $xml->title;
		$obj->category				= (string) $xml->category;
		$obj->description			= (string) $xml->description;
		$obj->version				= (string) $xml->version;
		$obj->versionAvailable		= NULL;
		$obj->versionInstalled		= NULL;
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
		$obj->files->files			= array();
		$obj->config				= array();
		$obj->relations				= new stdClass();
		$obj->relations->needs		= array();
		$obj->relations->supports	= array();
		$obj->sql					= array();
		$obj->links					= array();
		$obj->hooks					= array();
		$obj->installType			= 0;
		$obj->installDate			= NULL;
		$obj->installSource			= NULL;

		if( $xml->version->hasAttribute( 'install-type' ) )
			$obj->installType	= (int) $xml->version->getAttribute( 'install-type' );
		if( $xml->version->hasAttribute( 'install-date' ) )
			$obj->installDate	= strtotime( $xml->version->getAttribute( 'install-date' ) );
		if( $xml->version->hasAttribute( 'install-source' ) )
			$obj->installSource	= $xml->version->getAttribute( 'install-source' );

		if( $xml->files ){
			$map	= array(
				'class'		=> 'classes',
				'locale'	=> 'locales',
				'template'	=> 'templates',
				'style'		=> 'styles',
				'script'	=> 'scripts',
				'image'		=> 'images',
				'file'		=> 'files',
			);
			foreach( $map as $source => $target ){
				foreach( $xml->files->$source as $file ){
					$object	= (object) array( 'file' => (string) $file );
					foreach( $file->getAttributes() as $key => $value )
						$object->$key	= $value;
					$obj->files->{$target}[]	= $object;
				}
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
				'name'		=> (string) $company,
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
			$key		= $pair->getAttribute( 'name' );
			$type		= $pair->hasAttribute( 'type' ) ? $pair->getAttribute( 'type' ) : 'string';
			$values		= $pair->hasAttribute( 'values' ) ? explode( ',', $pair->getAttribute( 'values' ) ) : array();
			$mandatory	= $pair->hasAttribute( 'mandatory' ) ? $pair->getAttribute( 'mandatory' ) : FALSE;
			$protected	= $pair->hasAttribute( 'protected' ) ? $pair->getAttribute( 'protected' ) : FALSE;
			$title		= $pair->hasAttribute( 'title' ) ? $pair->getAttribute( 'title' ) : NULL;
			if( !$title && $pair->hasAttribute( 'info' ) )
				$title	= $pair->getAttribute( 'info' );
			$value		= trim( (string) $pair );
			if( in_array( strtolower( $type ), array( 'boolean', 'bool' ) ) )						//  value is boolean
				$value	= !in_array( strtolower( $value ), array( 'no', 'false', '0', '' ) );		//  value is not negative
			$obj->config[$key]	= (object) array(
				'key'		=> trim( $key ),
				'type'		=> trim( strtolower( $type ) ),
				'value'		=> $value,
				'values'	=> $values,
				'mandatory'	=> $mandatory,
				'protected'	=> $protected,
				'title'		=> $title,
			);
		}
		if( $xml->relations ){
			foreach( $xml->relations->needs as $moduleName )
				$obj->relations->needs[]	= (string) $moduleName;
			foreach( $xml->relations->supports as $moduleName )
				$obj->relations->supports[]	= (string) $moduleName;
		}
		foreach( $xml->sql as $sql ){
			$event		= $sql->getAttribute( 'on' );
			$to			= $sql->hasAttribute( 'version-to' ) ? $sql->getAttribute( 'version-to' ) : NULL;
			$version	= $sql->hasAttribute( 'version' ) ? $sql->getAttribute( 'version' ) : $to; 		//NULL;			//  @todo: remove fallback
			$type		= $sql->hasAttribute( 'type' ) ? $sql->getAttribute( 'type' ) : '*';

			if( $event == "update" )
				if( !$version )
					throw new Exception( 'SQL type "update" needs attribute "version"' );

			foreach( explode( ',', $type ) as $type ){
				$key	= $event.'@'.$type;
				if( $event == "update" )
					$key	= $event.":".$version.'@'.$type;
				$obj->sql[$key] = (object) array(
					'event'		=> $event,
					'version'	=> $version,
					'type'		=> $type,
					'sql'		=> (string) $sql
				);
			}
		}

		foreach( $xml->link as $link ){
			$access		= $link->hasAttribute( 'access' ) ? $link->getAttribute( 'access' ) : NULL;
			$language	= $link->hasAttribute( 'lang', 'xml' ) ? $link->getAttribute( 'lang', 'xml' ) : NULL;
			$label		= (string) $link;
			$path		= $link->hasAttribute( 'path' ) ? $link->getAttribute( 'path' ) : $label;
			$rank		= $link->hasAttribute( 'rank' ) ? (int) $link->getAttribute( 'rank' ) : 10;
			$parent		= $link->hasAttribute( 'parent' ) ? $link->getAttribute( 'parent' ) : NULL;
			$link		= $link->hasAttribute( 'link' ) ? $link->getAttribute( 'link' ) : '';
			$obj->links[]	= (object) array(
				'parent'	=> $parent,
				'access'	=> $access,
				'language'	=> $language,
				'path'		=> $path,
				'link'		=> $link,
				'rank'		=> $rank,
				'label'		=> $label,
			);
			(string) $link;
		}

		foreach( $xml->hook as $hook ){
			$resource	= $hook->getAttribute( 'resource' );
			$event		= $hook->getAttribute( 'event' );
			$obj->hooks[$resource][$event]	= (string) $hook;
		}
		return $obj;
	}
}
?>
