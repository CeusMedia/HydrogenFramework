<?php
/**
 *	Reader for local module XML files.
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
 *	Reader for local module XML files.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2019 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Module_Reader{

	static public function load( $filePath, $id ){
		if( !file_exists( $filePath ) )
			throw new RuntimeException( 'Module file "'.$filePath.'" is not existing' );
		$xml						= XML_ElementReader::readFile( $filePath );
		$obj						= new stdClass();
		$obj->id					= $id;
		$obj->file					= $filePath;
		$obj->uri					= realpath( $filePath );
		$obj->path					= NULL;
		$obj->title					= (string) $xml->title;
		$obj->category				= (string) $xml->category;
		$obj->description			= (string) $xml->description;
		$obj->version				= (string) $xml->version;
		$obj->versionAvailable		= NULL;
		$obj->versionInstalled		= NULL;
		$obj->versionLog			= array();
		$obj->isActive				= TRUE;
		$obj->isInstalled			= FALSE;
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
		$obj->install				= new stdClass();
		$obj->install->type			= 0;
		$obj->install->date			= NULL;
		$obj->install->source		= NULL;

		/*	--  LOCALLY INSTALLED MODULE  --  */
		if( $xml->version->hasAttribute( 'install-type' ) )											//  install type is set
			$obj->install->type	= (int) $xml->version->getAttribute( 'install-type' );				//  note install type
		if( $xml->version->hasAttribute( 'install-date' ) )											//  install date is set
			$obj->install->date	= strtotime( $xml->version->getAttribute( 'install-date' ) );		//  note install date
		if( $xml->version->hasAttribute( 'install-source' ) )										//  install source is set
			$obj->install->source	= $xml->version->getAttribute( 'install-source' );				//  note install source

		foreach( $xml->log as $entry ){																//  iterate version log entries if available
			if( $entry->hasAttribute( "version" ) ){												//  only if log entry is versioned
				$obj->versionLog[]	= (object) array(												//  append version log entry
					'note'		=> (string) $entry,													//  extract entry note
					'version'	=> $entry->getAttribute( 'version' ),								//  extract entry version
				);
			}
		}

		if( $xml->files ){																			//  iterate files
			$map	= array(																		//  ...
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
			$title	= $license->hasAttribute( 'title' ) ? $license->getAttribute( 'title' ) : '';
			$obj->licenses[]	= (object) array(
				'label'		=> (string) $license,
				'source'	=> $source,
				'title'		=> $title,
			);
		}

		foreach( $xml->company as $company ){
			$email	= $company->hasAttribute( 'email' ) ? $company->getAttribute( 'email' ) : '';
			$site	= $company->hasAttribute( 'site' ) ? $company->getAttribute( 'site' ) : '';
			$obj->companies[]	= (object) array(
				'name'		=> (string) $company,
				'email'		=> $email,
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
//			$value		= trim( (string) $pair );
			$value		= (string) $pair;
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
			$link		= $link->hasAttribute( 'link' ) ? $link->getAttribute( 'link' ) : NULL;
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
			$level		= 5;
			if( $hook->hasAttribute( 'level' ) )
				$level	= (int) $hook->getAttribute( 'level' );
			$obj->hooks[$resource][$event][]	= (object) array(
				'level'	=> $level,
				'hook'	=> trim( (string) $hook, ' ' ),
			);
		}
		return $obj;
	}
}
?>
