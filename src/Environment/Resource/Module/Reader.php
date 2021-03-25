<?php
/**
 *	Reader for local module XML files.
 *
 *	Copyright (c) 2012-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2012-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

use CMF_Hydrogen_Environment_Resource_Module_Component_Config as ConfigComponent;
use CMF_Hydrogen_Environment_Resource_Module_Component_File as FileComponent;

/**
 *	Reader for local module XML files.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Module_Reader
{
	/**
	 *	Load module data object from module XML file statically.
	 *	@static
	 *	@access		public
	 *	@param		string		$filePath		File path to module XML file
	 *	@param		string		$id				Module ID
	 *	@return		object						Module data object
	 */
	public static function load( string $filePath, string $id )
	{
		if( !file_exists( $filePath ) )
			throw new RuntimeException( 'Module file "'.$filePath.'" is not existing' );
		$xml	= XML_ElementReader::readFile( $filePath );
		$object	= (object) array(
			'id'				=> $id,
			'file'				=> $filePath,
			'uri'				=> realpath( $filePath ),
			'path'				=> NULL,
			'title'				=> (string) $xml->title,
			'category'			=> (string) $xml->category,
			'description'		=> (string) $xml->description,
			'version'			=> (string) $xml->version,
			'versionAvailable'	=> NULL,
			'versionInstalled'	=> NULL,
			'versionLog'		=> array(),
			'deprecation'		=> NULL,
			'isActive'			=> TRUE,
			'isInstalled'		=> FALSE,
			'companies'			=> array(),
			'authors'			=> array(),
			'licenses'			=> array(),
			'price'				=> (string) $xml->price,
			'icon'				=> NULL,
			'files'				=> (object) array(
				'classes'		=> array(),
				'locales'		=> array(),
				'templates'		=> array(),
				'styles'		=> array(),
				'scripts'		=> array(),
				'images'		=> array(),
				'files'			=> array(),
			),
			'config'			=> array(),
			'relations'			=> (object) array(
				'needs'			=> array(),
				'supports'		=> array(),
			),
			'sql'				=> array(),
			'links'				=> array(),
			'hooks'				=> array(),
			'jobs'				=> array(),
			'install'			=> (object) array(
				'type'			=> self::castNodeAttributes( $xml->version, 'install-type', 'int' ),	//  note install type
				'date'			=> self::castNodeAttributes( $xml->version, 'install-date', 'time' ),	//  note install date
				'source'		=> self::castNodeAttributes( $xml->version, 'install-source' ),			//  note install source
			),
		);
		self::decorateObjectWithLog( $object, $xml );
		self::decorateObjectWithFiles( $object, $xml );
		self::decorateObjectWithAuthors( $object, $xml );
		self::decorateObjectWithCompanies( $object, $xml );
		self::decorateObjectWithLinks( $object, $xml );
		self::decorateObjectWithHooks( $object, $xml );
		self::decorateObjectWithJobs( $object, $xml );
		self::decorateObjectWithDeprecation( $object, $xml );
		self::decorateObjectWithConfig( $object, $xml );
		self::decorateObjectWithRelations( $object, $xml );
		self::decorateObjectWithLicenses( $object, $xml );
		return $object;
	}

	/**
	*	Read module XML file and return module data object.
	 *	@access		public
	 *	@param		string		$filePath		File path to module XML file
	 *	@param		string		$id				Module ID
	 *	@return		object						Module data object
	 */
	public function read( string $filePath, string $id )
	{
		return self::load( $filePath, $id );
	}

	//  --  PROTECTED  --  //

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

	/**
	 *	Decorates module object by author information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithAuthors( $object, XML_Element $xml )
	{
		if( !$xml->author )																			//  no author nodes existing
			return FALSE;
		foreach( $xml->author as $author ){															//  iterate author nodes
			$object->authors[]	= (object) array(
				'name'	=> (string) $author,
				'email'	=> self::castNodeAttributes( $author, 'email' ),
				'site'	=> self::castNodeAttributes( $author, 'site' )
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by company information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithCompanies( $object, XML_Element $xml )
	{
		if( !$xml->company )																		//  no company nodes existing
			return FALSE;
		foreach( $xml->company as $company ){														//  iterate company nodes
			$object->companies[]	= (object) array(
				'name'		=> (string) $company,
				'email'		=> self::castNodeAttributes( $company, 'email' ),
				'site'		=> self::castNodeAttributes( $company, 'site' )
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by config information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithConfig( $object, XML_Element $xml )
	{
		if( !$xml->config )																			//  no config nodes existing
			return FALSE;
		foreach( $xml->config as $pair ){															//  iterate config nodes
			$title		= self::castNodeAttributes( $pair, 'title' );
			$type		= trim( strtolower( self::castNodeAttributes( $pair, 'type' ) ) );
			$key		= self::castNodeAttributes( $pair, 'name' );
			$info		= self::castNodeAttributes( $pair, 'info' );
			$title		= ( !$title && $info ) ? $info : $title;
			$value		= (string) $pair;
			if( in_array( $type, array( 'boolean', 'bool' ) ) )										//  value is boolean
				$value	= !in_array( strtolower( $value ), array( 'no', 'false', '0', '' ) );		//  value is not negative
/*			$object->config[$key]	= (object) array(
				'key'				=> trim( $key ),
				'type'				=> $type,
				'value'				=> $value,
				'values'			=> self::castNodeAttributes( $pair, 'values', 'array' ),
				'mandatory'			=> self::castNodeAttributes( $pair, 'mandatory', 'bool' ),
				'protected'			=> self::castNodeAttributes( $pair, 'protected' ),
				'title'				=> $title,
			);*/
			$item				= new ConfigComponent( trim( $key ), $value, $type , $title );
			$item->values		= self::castNodeAttributes( $pair, 'values', 'array' );
			$item->mandatory	= self::castNodeAttributes( $pair, 'mandatory', 'bool' );
			$item->protected	= self::castNodeAttributes( $pair, 'protected' );
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by deprecation information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithDeprecation( $object, XML_Element $xml )
	{
		if( !$xml->deprecation )																	//  deprecation node is not existing
			return FALSE;
		$object->deprecation	= (object) array(													//  note deprecation object
			'message'			=> (string) $xml->deprecation,										//  ... with message
			'url'				=> 	self::castNodeAttributes( $xml->deprecation, 'url' ),			//  ... with follow-up URL, if set
		);
		return TRUE;
	}

	/**
	 *	Decorates module object by file information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 *	@todo		rethink the defined map of paths
	 */
	protected static function decorateObjectWithFiles( $object, XML_Element $xml )
	{
		if( !$xml->files )
			return FALSE;
		$map	= array(																			//  ...
			'class'		=> 'classes',
			'locale'	=> 'locales',
			'template'	=> 'templates',
			'style'		=> 'styles',
			'script'	=> 'scripts',
			'image'		=> 'images',
			'file'		=> 'files',
		);
		foreach( $map as $source => $target ){														//  iterate files
			foreach( $xml->files->$source as $file ){
				$item	= new FileComponent( (string) $file );
//				$item	= (object) array( 'file' => (string) $file );
				foreach( $file->getAttributes() as $key => $value )
					$item->$key	= $value;
				$object->files->{$target}[]	= $item;
			}
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by hook information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithHooks( $object, XML_Element $xml )
	{
		if( !$xml->hook )																			//  hook node is not existing
			return FALSE;
		foreach( $xml->hook as $hook ){																//  iterate hook nodes
			$resource	= self::castNodeAttributes( $hook, 'resource' );
			$event		= self::castNodeAttributes( $hook, 'event' );
			$object->hooks[$resource][$event][]	= (object) array(
				'level'	=> self::castNodeAttributes( $hook, 'level', 'int', 5 ),
				'hook'	=> trim( (string) $hook, ' ' ),
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by job information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithJobs( $object, XML_Element $xml )
	{
		if( !$xml->job )																			//  hook node is not existing
			return FALSE;
		foreach( $xml->job as $job ){																//  iterate job nodes
			$callable		= explode( '::', (string) $job, 2 );
			$object->jobs[]	= (object) array(
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
		return TRUE;
	}

	/**
	 *	Decorates module object by license information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithLicenses( $object, XML_Element $xml )
	{
		if( !$xml->license )																		//  no license nodes existing
			return FALSE;
		foreach( $xml->license as $license ){														//  iterate license nodes
			$object->licenses[]	= (object) array(
				'label'			=> (string) $license,
				'source'		=> self::castNodeAttributes( $license, 'source' ),
				'title'			=> self::castNodeAttributes( $license, 'title' ),
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by link information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithLinks( $object, XML_Element $xml )
	{
		if( !$xml->link )																			//  no link nodes existing
			return FALSE;
		foreach( $xml->link as $link ){																//  iterate link nodes
			$language	= NULL;
			if( $link->hasAttribute( 'lang', 'xml' ) )
				$language	= $link->getAttribute( 'lang', 'xml' );
			$label		= (string) $link;
			$object->links[]	= (object) array(
				'parent'		=> self::castNodeAttributes( $link, 'parent' ),
				'access'		=> self::castNodeAttributes( $link, 'access' ),
				'language'		=> $language,
				'path'			=> self::castNodeAttributes( $link, 'path', 'string', $label ),
				'link'			=> self::castNodeAttributes( $link, 'link' ),
				'rank'			=> self::castNodeAttributes( $link, 'rank', 'int', 10 ),
				'label'			=> $label,
			);
			(string) $link;
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by log information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithLog( $object, XML_Element $xml )
	{
		if( !$xml->log )																			//  no log nodes existing
			return FALSE;
		foreach( $xml->log as $entry ){																//  iterate version log entries if available
			if( $entry->hasAttribute( "version" ) ){												//  only if log entry is versioned
				$object->versionLog[]	= (object) array(											//  append version log entry
					'note'		=> (string) $entry,													//  extract entry note
					'version'	=> $entry->getAttribute( 'version' ),								//  extract entry version
				);
			}
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by relation information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithRelations( $object, XML_Element $xml )
	{
		if( !$xml->relations )																		//  no relation nodes existing
			return FALSE;																			//  do nothing
		if( $xml->relations->needs )																//  if needed modules are defined
			foreach( $xml->relations->needs as $moduleName )										//  iterate list if needed modules
				$object->relations->needs[]	= (string) $moduleName;									//  note needed
		if( $xml->relations->supports )																//  if supported modules are defined
			foreach( $xml->relations->supports as $moduleName )										//  iterate list if supported modules
				$object->relations->supports[]	= (string) $moduleName;
		return TRUE;
	}

	/**
	 *	Decorates module object by SQL information, if set.
	 *	@access		protected
	 *	@param		object			$object			Data object of module
	 *	@param		XML_Element		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithSql( $object, XML_Element $xml )
	{
		if( !$xml->sql )																			//  no sql nodes existing
			return FALSE;
		foreach( $xml->sql as $sql ){																//  iterate sql nodes
			$event		= self::castNodeAttributes( $sql, 'on' );
			$to			= self::castNodeAttributes( $sql, 'version-to' );
			$version	= self::castNodeAttributes( $sql, 'version', 'string', $to );				//  @todo: remove fallback
			$type		= self::castNodeAttributes( $sql, 'type', 'string', '*' );
			if( $event === 'update' && !$version )
				throw new Exception( 'SQL type "update" needs attribute "version"' );

			foreach( explode( ',', $type ) as $type ){
				$key	= $event.'@'.$type;
				if( $event == "update" )
					$key	= $event.":".$version.'@'.$type;
				$object->sql[$key] = (object) array(
					'event'			=> $event,
					'version'		=> $version,
					'type'			=> $type,
					'sql'			=> (string) $sql
				);
			}
		}
		return TRUE;
	}
}
