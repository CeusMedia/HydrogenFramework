<?php
/**
 *	Reader for local module XML files.
 *
 *	Copyright (c) 2012-2020 Christian Würker (ceusmedia.de)
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
 *	@copyright		2012-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Reader for local module XML files.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Module_Reader
{
	public static function load( string $filePath, string $id )
	{
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
		$obj->jobs					= array();
		$obj->install				= new stdClass();
		$obj->install->type			= 0;
		$obj->install->date			= NULL;
		$obj->install->source		= NULL;

		/*	--  LOCALLY INSTALLED MODULE  --  */
		$obj->install->type		= self::castNodeAttributes( $xml->version, 'install-type', 'int' );	//  note install type
		$obj->install->date		= self::castNodeAttributes( $xml->version, 'install-date', 'time' );//  note install date
		$obj->install->source	= self::castNodeAttributes( $xml->version, 'install-source' );		//  note install source

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
			$obj->licenses[]	= (object) array(
				'label'		=> (string) $license,
				'source'	=> self::castNodeAttributes( $license, 'source' ),
				'title'		=> self::castNodeAttributes( $license, 'title' ),
			);
		}

		foreach( $xml->company as $company ){
			$obj->companies[]	= (object) array(
				'name'		=> (string) $company,
				'email'		=> self::castNodeAttributes( $company, 'email' ),
				'site'		=> self::castNodeAttributes( $company, 'site' )
			);
		}

		foreach( $xml->author as $author ){
			$obj->authors[]	= (object) array(
				'name'	=> (string) $author,
				'email'	=> self::castNodeAttributes( $author, 'email' ),
				'site'	=> self::castNodeAttributes( $author, 'site' )
			);
		}

		foreach( $xml->config as $pair ){
			$title		= self::castNodeAttributes( $pair, 'title' );
			$type		= trim( strtolower( self::castNodeAttributes( $pair, 'type' ) ) );
			$key		= self::castNodeAttributes( $pair, 'name' );
			$info		= self::castNodeAttributes( $pair, 'info' );
			$title		= ( !$title && $info ) ? $info : $title;
			$value		= (string) $pair;
			if( in_array( $type, array( 'boolean', 'bool' ) ) )						//  value is boolean
				$value	= !in_array( strtolower( $value ), array( 'no', 'false', '0', '' ) );		//  value is not negative
			$obj->config[$key]	= (object) array(
				'key'		=> trim( $key ),
				'type'		=> $type,
				'value'		=> $value,
				'values'	=> self::castNodeAttributes( $pair, 'values', 'array' ),
				'mandatory'	=> self::castNodeAttributes( $pair, 'mandatory', 'bool' ),
				'protected'	=> self::castNodeAttributes( $pair, 'protected', 'bool' ),
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
			$event		= self::castNodeAttributes( $sql, 'on' );
			$to			= self::castNodeAttributes( $sql, 'version-to' );
			$version	= self::castNodeAttributes( $sql, 'version', 'string', $to );				//  @todo: remove fallback
			$type		= self::castNodeAttributes( $sql, 'type', 'string', '*' );
			if( $event === 'update' )
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
			$language	= $link->hasAttribute( 'lang', 'xml' ) ? $link->getAttribute( 'lang', 'xml' ) : NULL;
			$label		= (string) $link;
			$obj->links[]	= (object) array(
				'parent'	=> self::castNodeAttributes( $link, 'parent' ),
				'access'	=> self::castNodeAttributes( $link, 'access' ),
				'language'	=> $language,
				'path'		=> self::castNodeAttributes( $link, 'path', 'string', $label ),
				'link'		=> self::castNodeAttributes( $link, 'link' ),
				'rank'		=> self::castNodeAttributes( $link, 'rank', 'int', 10 ),
				'label'		=> $label,
			);
			(string) $link;
		}

		foreach( $xml->hook as $hook ){
			$resource	= self::castNodeAttributes( $hook, 'resource' );
			$event		= self::castNodeAttributes( $hook, 'event' );
			$obj->hooks[$resource][$event][]	= (object) array(
				'level'	=> self::castNodeAttributes( $hook, 'level', 'int', 5 ),
				'hook'	=> trim( (string) $hook, ' ' ),
			);
		}

		foreach( $xml->job as $job ){
			$callable		= explode( '::', (string) $job, 2 );
			$obj->jobs[]	= (object) array(
				'id'			=> self::castNodeAttributes( $job, 'id' ),
				'class'			=> $callable[0],
				'method'		=> $callable[1],
				'commands'		=> self::castNodeAttributes( $job, 'commands' ),
				'arguments'		=> self::castNodeAttributes( $job, 'arguments' ),
				'mode'			=> self::castNodeAttributes( $job, 'mode', 'array', array() ),
				'interval'		=> self::castNodeAttributes( $job, 'interval' ),
				'multiple'		=> self::castNodeAttributes( $job, 'multiple', 'bool' ),
				'deprecated'	=> self::castNodeAttributes( $job, 'deprecated' ),
				'disabled'		=> self::castNodeAttributes( $job, 'disabled' ),
			);
		}
		return $obj;
	}

	protected static function castNodeAttributes( $node, string $attribute, string $type = 'string', $default = NULL )
	{
		if( !$node->hasAttribute( $attribute ) ){
			switch( $type ){
				case 'array':
					return !is_null( $default ) ? $default : array();
				case 'string':
					return !is_null( $default ) ? $default : '';
				case 'bool':
				case 'boolean':
					return !is_null( $default ) ? $default : FALSE;
				case 'int':
				case 'integer':
					return !is_null( $default ) ? $default : 0;
				default:
					return !is_null( $default ) ? $default : NULL;
			}
		}
		$value	= $node->getAttribute( $attribute );
		switch( $type ){
			case 'array':
				return preg_split( '/\s*,\s*/', trim( $value ) );
			case 'string':
				return trim( (string) $value );
			case 'time':
				return strtotime( (string) $value );
			case 'bool':
			case 'boolean':
				return in_array( strtolower( $value ), array( 'true', 'yes', '1', TRUE ) );
			case 'int':
			case 'integer':
				return (int) $value;
			default:
				return $value;
		}
	}
}
