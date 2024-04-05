<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Reader for local module XML files.
 *
 *	Copyright (c) 2012-2024 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Module;

use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\Common\XML\ElementReader as XmlReader;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Author as AuthorDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Company as CompanyDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Config as ConfigDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Deprecation as DeprecationDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\File as FileDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Hook as HookDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Job as JobDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\License as LicenseDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Relation as RelationDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\SQL as SqlDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Version as VersionDefinition;

use Exception;
use RuntimeException;

/**
 *	Reader for local module XML files.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Reader
{
	/**
	 *	Load module data object from module XML file statically.
	 *	@static
	 *	@access		public
	 *	@param		string		$filePath		File path to module XML file
	 *	@param		string		$id				Module ID
	 *	@return		Definition					Module data object
	 *	@throws		Exception	if XML file could not been loaded and parsed
	 */
	public static function load( string $filePath, string $id ): Definition
	{
		if( !file_exists( $filePath ) )
			throw new RuntimeException( 'Module file "'.$filePath.'" is not existing' );
		$xml	= XmlReader::readFile( $filePath );
		$object	= new Definition( $id, (string) $xml->version, $filePath );
		self::decorateObjectWithBasics( $object, $xml );
		self::decorateObjectWithFrameworks( $object, $xml );
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
//		self::decorateObjectWithSql( $object, $xml );
		return $object;
	}

	/**
	*	Read module XML file and return module data object.
	 *	@access		public
	 *	@param		string		$filePath		File path to module XML file
	 *	@param		string		$id				Module ID
	 *	@return		object						Module data object
	 *	@throws		Exception
	 */
	public function read( string $filePath, string $id ): object
	{
		return self::load( $filePath, $id );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		XmlElement		$node
	 *	@param		string			$attribute
	 *	@param		string			$type
	 *	@param		mixed			$default
	 *	@return		array|string|bool|int|NULL
	 */
	protected static function castNodeAttributes( XmlElement $node, string $attribute, string $type = 'string', mixed $default = NULL ): array|string|bool|int|NULL
	{
		if( !$node->hasAttribute( $attribute ) ){
			return match( $type ){
				'array'				=> $default ?? [],
				'string'			=> $default ?? '',
				'bool', 'boolean'	=> $default ?? FALSE,
				'int', 'integer'	=> $default ?? 0,
				default				=> $default ?? NULL,
			};
		}
		$value	= $node->getAttribute( $attribute );
		return match( $type ){
			'array'				=> preg_split( '/\s*,\s*/', trim( $value ) ),
			'string'			=> trim( $value ),
			'time'				=> strtotime( $value ),
			'bool', 'boolean'	=> in_array( strtolower( $value ), ['true', 'yes', '1', TRUE] ),
			'int', 'integer'	=> (int) $value,
			default				=> $value,
		};
	}

	/**
	 *	Decorates module object by author information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithAuthors( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->author )																			//  no author nodes existing
			return FALSE;
		foreach( $xml->author as $author ){															//  iterate author nodes
			$object->authors[]	= new AuthorDefinition(
				(string) $author,
				self::castNodeAttributes( $author, 'email' ),
				self::castNodeAttributes( $author, 'site' )
			);
		}
		return TRUE;
	}

	protected static function decorateObjectWithBasics( Definition $object, XmlElement $xml ): void
	{
		$object->version			= new VersionDefinition( (string) $xml->version );
		$object->title				= (string) $xml->title;
		$object->category			= (string) $xml->category;
		$object->description		= (string) $xml->description;
		$object->price				= (string) $xml->price;
		if( NULL !== $object->install && isset( $xml->version ) ){
			$object->install->type		= (string) self::castNodeAttributes( $xml->version, 'install-type' );
			$object->install->date		= (string) self::castNodeAttributes( $xml->version, 'install-date' );
			$object->install->source	= (string) self::castNodeAttributes( $xml->version, 'install-source' );
		}
	}

	/**
	 *	Decorates module object by company information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithCompanies( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->company )																		//  no company nodes existing
			return FALSE;
		foreach( $xml->company as $company ){														//  iterate company nodes
			$object->companies[]	= new CompanyDefinition(
				(string) $company,
				self::castNodeAttributes( $company, 'email' ),
				self::castNodeAttributes( $company, 'site' )
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by config information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithConfig( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->config )																			//  no config nodes existing
			return FALSE;
		foreach( $xml->config as $pair ){															//  iterate config nodes
			/** @var string $title */
			$title		= self::castNodeAttributes( $pair, 'title' );
			/** @var string $type */
			$type		= self::castNodeAttributes( $pair, 'type' );
			$type		= trim( strtolower( $type ) );
			/** @var string $key */
			$key		= self::castNodeAttributes( $pair, 'name' );
			/** @var string $info */
			$info		= self::castNodeAttributes( $pair, 'info' );
			$title		= ( !$title && $info ) ? $info : $title;
			$value		= (string) $pair;
			if( in_array( $type, array( 'boolean', 'bool' ) ) )										//  value is boolean
				$value	= !in_array( strtolower( $value ), array( 'no', 'false', '0', '' ) );		//  value is not negative

			$item				= new ConfigDefinition( trim( $key ), $value, $type , $title );		//  container for config entry

			$item->values		= (array) self::castNodeAttributes( $pair, 'values', 'array' );
			$item->mandatory	= (bool) self::castNodeAttributes( $pair, 'mandatory', 'bool' );
			/** @var bool|string $protected */
			$protected			= self::castNodeAttributes( $pair, 'protected' );
			$item->protected	= $protected;
			$object->config[$key]	= $item;
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by deprecation information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithDeprecation( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->deprecation )																	//  deprecation node is not existing
			return FALSE;
		$object->deprecation	= new DeprecationDefinition(										//  note deprecation object
			(string) $xml->deprecation,																//  ... with message
			(string) self::castNodeAttributes( $xml->deprecation, 'url' )					//  ... with follow-up URL, if set
		);
		return TRUE;
	}

	/**
	 *	Decorates module object by file information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 *	@todo		rethink the defined map of paths
	 */
	protected static function decorateObjectWithFiles( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->files )
			return FALSE;
		$map	= [
			'class'		=> 'classes',
			'locale'	=> 'locales',
			'template'	=> 'templates',
			'style'		=> 'styles',
			'script'	=> 'scripts',
			'image'		=> 'images',
			'file'		=> 'files',
		];
		foreach( $map as $source => $target ){														//  iterate files
			foreach( $xml->files->$source as $file ){
				$item	= new FileDefinition( (string) $file );
//				$item	= (object) array( 'file' => (string) $file );
				foreach( $file->getAttributes() as $key => $value )
					$item->$key	= $value;
				$object->files->{$target}[]	= $item;
			}
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by framework support information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithFrameworks( Definition $object, XmlElement $xml ): bool
	{
		/** @var string $frameworks */
		$frameworks	= self::castNodeAttributes( $xml, 'frameworks', 'string', 'Hydrogen:<0.9' );
		if( !strlen( trim( $frameworks ) ) )
			return FALSE;
		/** @var array $list */
		$list		= preg_split( '/\s*(,|\|)\s*/', $frameworks );
		foreach( $list as $listItem ){
			/** @var array $parts */
			$parts	= preg_split( '/\s*(:|@)\s*/', $listItem );
			if( count( $parts ) < 2 )
				$parts[1]	= '*';
			$object->frameworks[(string) $parts[0]]	= (string) $parts[1];
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by hook information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithHooks( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->hook )																			//  hook node is not existing
			return FALSE;
		foreach( $xml->hook as $hook ){																//  iterate hook nodes
			/** @var string $resource */
			$resource	= self::castNodeAttributes( $hook, 'resource' );
			/** @var string $event */
			$event		= self::castNodeAttributes( $hook, 'event' );
			$object->hooks[$resource][$event][]	= new HookDefinition(
				trim( (string) $hook, ' ' ),
				$resource,
				$event,
				self::castNodeAttributes( $hook, 'level', 'int', 5 )
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by job information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithJobs( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->job )																			//  hook node is not existing
			return FALSE;
		foreach( $xml->job as $job ){																//  iterate job nodes
			$callable		= explode( '::', (string) $job, 2 );
			$object->jobs[]	= new JobDefinition(
				self::castNodeAttributes( $job, 'id' ),
				$callable[0],
				$callable[1],
				self::castNodeAttributes( $job, 'commands' ),
				self::castNodeAttributes( $job, 'arguments' ),
				self::castNodeAttributes( $job, 'mode', 'array', [] ),
				self::castNodeAttributes( $job, 'interval' ),
				self::castNodeAttributes( $job, 'multiple', 'bool' ),
				self::castNodeAttributes( $job, 'deprecated' ),
				self::castNodeAttributes( $job, 'disabled' )
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by license information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithLicenses( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->license )																		//  no license nodes existing
			return FALSE;
		foreach( $xml->license as $license ){														//  iterate license nodes
			$object->licenses[]	= new LicenseDefinition(
				(string) $license,
				self::castNodeAttributes( $license, 'title' ),
				self::castNodeAttributes( $license, 'source' )
			);
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by link information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithLinks( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->link )																			//  no link nodes existing
			return FALSE;
		foreach( $xml->link as $link ){																//  iterate link nodes
			$language	= NULL;
			if( $link->hasAttribute( 'lang', 'xml' ) )
				$language	= $link->getAttribute( 'lang', 'xml' );
			$label		= (string) $link;
			$object->links[]	= (object) [
				'parent'		=> self::castNodeAttributes( $link, 'parent' ),
				'access'		=> self::castNodeAttributes( $link, 'access' ),
				'language'		=> $language,
				'path'			=> self::castNodeAttributes( $link, 'path', 'string', $label ),
				'link'			=> self::castNodeAttributes( $link, 'link' ),
				'rank'			=> self::castNodeAttributes( $link, 'rank', 'int', 10 ),
				'label'			=> $label,
				'icon'			=> self::castNodeAttributes( $link, 'icon' ),
			];
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by log information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithLog( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->log )																			//  no log nodes existing
			return FALSE;
		foreach( $xml->log as $entry ){																//  iterate version log entries if available
			if( $entry->hasAttribute( "version" ) ){												//  only if log entry is versioned
				$object->version->log[]	= (object) [												//  append version log entry
					'note'		=> (string) $entry,													//  extract entry note
					'version'	=> $entry->getAttribute( 'version' ),								//  extract entry version
				];
			}
		}
		return TRUE;
	}

	/**
	 *	Decorates module object by relation information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 */
	protected static function decorateObjectWithRelations( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->relations )																		//  no relation nodes existing
			return FALSE;																			//  do nothing
		if( $xml->relations->needs )																//  if needed modules are defined
			foreach( $xml->relations->needs as $moduleName )										//  iterate list if needed modules
				$object->relations->needs[(string) $moduleName]		= new RelationDefinition(		//  note relation
					(string) $moduleName,															//  ... with module ID
					self::castNodeAttributes( $moduleName, 'type' ),						//  ... with relation type
					self::castNodeAttributes( $moduleName, 'source' ),						//  ... with module source, if set
					self::castNodeAttributes( $moduleName, 'version' ),						//  ... with version, if set
					'needs'																	//  ... as needed
				);
		if( $xml->relations->supports )																//  if supported modules are defined
			foreach( $xml->relations->supports as $moduleName )										//  iterate list if supported modules
				$object->relations->supports[(string) $moduleName]	= new RelationDefinition(		//  note relation
					(string) $moduleName,															//  ... with module ID
					self::castNodeAttributes( $moduleName, 'type' ),						//  ... with relation type
					self::castNodeAttributes( $moduleName, 'source' ),						//  ... with module source, if set
					self::castNodeAttributes( $moduleName, 'version' ),						//  ... with version, if set
					'supports'																//  ... as supported
				);
		return TRUE;
	}

	/**
	 *	Decorates module object by SQL information, if set.
	 *	@access		protected
	 *	@param		Definition		$object			Data object of module
	 *	@param		XmlElement		$xml			XML tree object of module created by ::load
	 *	@return		boolean							TRUE if data object of module has been decorated
	 *	@throws		Exception
	 */
	protected static function decorateObjectWithSql( Definition $object, XmlElement $xml ): bool
	{
		if( !$xml->sql )																			//  no sql nodes existing
			return FALSE;
		foreach( $xml->sql as $sql ){																//  iterate sql nodes
			/** @var string $event */
			$event		= self::castNodeAttributes( $sql, 'on' );
			/** @var string $to */
			$to			= self::castNodeAttributes( $sql, 'version-to' );
			/** @var string $version */
			$version	= self::castNodeAttributes( $sql, 'version', 'string', $to );	//  @todo: remove fallback
			/** @var string $type */
			$type		= self::castNodeAttributes( $sql, 'type', 'string', '*' );
			if( $event === 'update' && !$version )
				throw new Exception( 'SQL type "update" needs attribute "version"' );

			foreach( explode( ',', $type ) as $type ){
				$key	= $event.'@'.$type;
				if( $event == "update" )
					$key	= $event.":".$version.'@'.$type;
				$object->sql[$key] = new SqlDefinition(
					$event,
					$version,
					$type,
					(string) $sql
				);
			}
		}
		return TRUE;
	}
}
