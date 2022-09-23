<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 *	Editor for local module XML files.
 *
 *	Copyright (c) 2012-2022 Christian Würker (ceusmedia.de)
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
 *	@copyright		2012-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource\Module;

use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\XML\DOM\Formater as XmlFormatter;
use CeusMedia\Common\XML\Element;
use CeusMedia\Common\XML\ElementReader as XmlReader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Deprecation;

use Exception;
use InvalidArgumentException;
use OutOfRangeException;
use RuntimeException;
use SimpleXMLElement;

/**
 *	Editor for local module XML files.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			add support for hooks and jobs
 */
class Editor
{
	protected $path;
	protected $nsXml	= 'http://www.w3.org/XML/1998/namespace';

	/**
	 *	@param		Environment		$env
	 *	@throws		Exception
	 */
	public function __construct( Environment $env )
	{
		$this->path		= $env->getConfig()->get( 'path.config' ).'/modules/';
		if( $env->getConfig()->get( 'path.module.config' ) ){
			Deprecation::getInstance()
				->setErrorVersion( '0.8.6.6' )
				->setExceptionVersion( '0.8.9' )
				->message( 'Using config path "module.config" is deprecated. Please remove this config pair!' );
			$this->path	= $env->getConfig()->get( 'path.module.config' );
		}
		$this->path		= $env->path.$this->path;
	}

	/**
	 *	Adds a new configuration pair to module XML file.
	 *	@access		public
	 *	@param		string			$moduleId	Module ID
	 *	@param		string			$name		Author name
	 *	@param		string|NULL		$email		Author email address
	 *	@return		void
	 */
	public function addAuthor( string $moduleId, string $name, ?string $email = NULL )
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$link		= $xml->addChild( 'author', $name );
		if( strlen( trim( $email ) ) )
			$link->addAttribute( 'email', $email );
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	Adds a company to module XML file.
	 *	@access		public
	 *	@param		string			$moduleId	Module ID
	 *	@param		string			$name		Company name
	 *	@param		string|NULL		$site		Company web address
	 *	@return		void
	 */
	public function addCompany( string $moduleId, string $name, ?string $site = NULL )
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$link		= $xml->addChild( 'company', $name );
		if( strlen( trim( $site ) ) )
			$link->addAttribute( 'site', trim( $site ) );
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	Adds a new configuration pair to module XML file.
	 *	@access		public
	 *	@param		string			$moduleId	Module ID
	 *	@param		string			$name		Pair key
	 *	@param		string			$type		Type (boolean,integer,float,string)
	 *	@param		string			$value		Pair value
	 *	@param		string			$values		List of possible values
	 *	@param		string			$mandatory	Flag: this pair needs to be set
	 *	@param		string			$protected	Flag: do not deliver this pair to frontend
	 *	@param		string|NULL		$title		Description
	 *	@return		void
	 */
	public function addConfig( string $moduleId, string $name, string $type, string $value, string $values, string $mandatory, string $protected, ?string $title = NULL )
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$link		= $xml->addChild( 'config', $value );											//  add pair node
		$link->addAttribute( 'name', $name );														//  set name attribute
		if( strlen( trim( $type ) ) )																//  type attribute is given
			$link->addAttribute( 'type', trim( $type ) );											//  set type attribute
		if( strlen( trim( $values ) ) )																//  values attribute is given
			$link->addAttribute( 'values', trim( $values ) );										//  set values attribute
		if( strlen( trim( $mandatory ) ) )															//  mandatory attribute is given
			$link->addAttribute( 'mandatory', trim( $mandatory ) );									//  set mandatory attribute
		if( strlen( trim( $protected ) ) )															//  protected attribute is given
			$link->addAttribute( 'protected', trim( $protected ) );									//  set protected attribute
		if( strlen( trim( $title ) ) )																//  title attribute is given
			$link->addAttribute( 'title', trim( addslashes( $title ) ) );							//  set title attribute
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	Adds a new configuration pair to module XML file.
	 *	@access		public
	 *	@param		string			$moduleId	Module ID
	 *	@param		string			$type		File resource type (class,template,locale,script,style,image)
	 *	@param		string			$resource	Typed file resource
	 *	@param		string			$source		Source, depending on type
	 *	@param		string|NULL		$load		Load mode, empty or "auto"
	 *	@return		void
	 *	@throws		Exception
	 */
	public function addFile( string $moduleId, string $type, string $resource, string $source, string $load = NULL )
	{
		$attributes	= ['source', 'load'];
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		if( !strlen( trim( $type ) ) )
			throw new InvalidArgumentException( 'No type given' );
		if( !strlen( trim( $resource ) ) )
			throw new InvalidArgumentException( 'No resource given' );
		$link		= $xml->files->addChild( $type, $resource );									//  add typed resource
		if( strlen( trim( $source ) ) )																//  source attribute is given
			$link->addAttribute( 'source', $source );												//  set source attribute
		if( strlen( trim( $load ) ) )																//  load attribute is given
			$link->addAttribute( 'load', $load );													//  set load attribute
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	Adds a new configuration pair to module XML file.
	 *	@access		public
	 *	@param		string			$moduleId	Module ID
	 *	@param		string			$path		Link path
	 *	@param		string|NULL		$link		?
	 *	@param		string|NULL		$label		Link label
	 *	@param		string|NULL		$access		Access mode (public|inside|outside|acl)
	 *	@param		string|NULL		$language	Link language
	 *	@param		string|NULL		$rank		Link rank in navigation
	 *	@return		void
	 *	@throws		Exception
	 */
	public function addLink( string $moduleId, string $path, ?string $link = NULL, ?string $label = NULL, ?string $access = NULL, ?string $language = NULL, ?string $rank = NULL )
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$link		= $xml->addChild( 'link', (string) $label );									//
		if( strlen( trim( $path ) ) )																//  path attribute is given
			$link->addAttribute( 'path', trim( $path ) );											//  set path attribute
		if( strlen( trim( $link ) ) )																//  link attribute is given
			$link->addAttribute( 'link', trim( $path ) );											//  set link attribute
		if( strlen( trim( $access ) ) )																//  access attribute is given
			$link->addAttribute( 'access', trim( $access ) );										//  set access attribute
		if( strlen( trim( $rank ) ) )																//  rank attribute is given
			$link->addAttribute( 'rank', trim( $rank ) );											//  set rank attribute
		if( strlen( trim( $language ) ) )															//  language attribute is given
			$link->addAttribute( 'lang', $language, 'xml', $this->nsXml );							//  set language attribute
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$type
	 *	@param		string		$relatedModuleId
	 *	@return		void
	 *	@throws		Exception
	 */
	public function addRelation( string $moduleId, string $type, string $relatedModuleId )
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		if( !$this->hasXmlNode( $xml, 'relations' ) )												//  relations node not yet existing
			$xml->addChild( 'relations' );															//  create relations node
		$node		= $xml->relations->addChild( $type, $relatedModuleId );							//  add new relation node
		$node->addAttribute( 'type', $type );														//  set type attribute on new node
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$ddl
	 *	@param		string		$event
	 *	@param		string		$type
	 *	@param		string		$version
	 *	@return		void
	 *	@throws		Exception
	 */
	public function addSql( string $moduleId, string $ddl, string $event, string $type, string $version )
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$ddl		= str_replace( '&', '&amp;', "\n".$ddl."\n" );
		$node		= $xml->addChildCData( 'sql', $ddl );											//  add SQL as CDATA node
		$node->addAttribute( 'on', $event );														//  set event attribute on new node
		$node->addAttribute( 'type', $type );														//  set type attribute on new node
		if( $event === "update" ){																	//  only for update set versions
			$node->addAttribute( 'version', $version );												//  set update source version
		}
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	@param		string			$moduleId
	 *	@param		int				$number
	 *	@param		string			$path
	 *	@param		string|NULL		$link
	 *	@param		string|NULL		$label
	 *	@param		string|NULL		$access
	 *	@param		string|NULL		$language
	 *	@param		$rank
	 *	@return		void
	 *	@throws		Exception
	 */
	public function editLink( string $moduleId, int $number, string $path, ?string $link = NULL, ?string $label = NULL, ?string $access = NULL, ?string $language = NULL, $rank = NULL )
	{
		$xml	= $this->loadModuleXml( $moduleId );												//  load module XML
		if( !isset( $xml->link[$number] ) )
			throw new OutOfRangeException( 'Invalid link number' );

		$node	= $xml->link[$number];
		$node->setValue( (string) $label );

		$node->setAttribute( 'path', strlen( trim( $path ) ) );
		$node->setAttribute( 'access', strlen( trim( $access ) ) ? trim( $access ) : NULL );
		$node->setAttribute( 'link', strlen( trim( $link ) ) ? trim( $link ) : NULL );
		$node->setAttribute( 'rank', strlen( trim( $rank ) ) ? trim( $rank ) : NULL );

		$language	= strlen( trim( $language ) ) ? trim( $language ) : NULL;
		$node->setAttribute( 'lang', $language, 'xml', $this->nsXml );								//  set language attribute
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$name
	 *	@return		bool
	 *	@throws		Exception
	 */
	public function removeAuthor( string $moduleId, string $name ): bool
	{
		$xml	= $this->loadModuleXml( $moduleId );												//  load module XML
		foreach( $xml->author as $author ){
			if( $author->getValue() == $name ){
				$author->remove();
				$this->saveModuleXml( $moduleId, $xml );											//  save modified module XML
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$name
	 *	@return		bool
	 *	@throws		Exception
	 */
	public function removeCompany( string $moduleId, string $name ): bool
	{
		$xml	= $this->loadModuleXml( $moduleId );												//  load module XML
		foreach( $xml->company as $company ){
			if( $company->getValue() == $name ){
				$company->remove();
				$this->saveModuleXml( $moduleId, $xml );											//  save modified module XML
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$name
	 *	@return		bool
	 *	@throws		Exception
	 */
	public function removeConfig( string $moduleId, string $name ): bool
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		foreach( $xml->config as $config ){
			if( $config->getAttribute( 'name' ) == $name ){
				$config->remove();
				$this->saveModuleXml( $moduleId, $xml );											//  save modified module XML
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$type
	 *	@param		string		$resource
	 *	@return		bool
	 *	@throws		Exception
	 */
	public function removeFile( string $moduleId, string $type, string $resource ): bool
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		if( !isset( $xml->files->$type ) )
			throw new InvalidArgumentException( 'Invalid type: '.$type );
		foreach( $xml->files->$type as $file ){
			if( $file->getValue() == $resource ){
				$file->remove();
				$this->saveModuleXml( $moduleId, $xml );											//  save modified module XML
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		int			$number
	 *	@return		bool
	 *	@throws		Exception
	 */
	public function removeLink( string $moduleId, int $number ): bool
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		if( !isset( $xml->link[$number] ) )
			throw new OutOfRangeException( 'Invalid link number' );
		unset( $xml->link[$number] );
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
		return TRUE;
	}

	public function removeRelation( string $moduleId, string $type, string $relatedModuleId ): bool
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		foreach( $xml->relations->$type as $relation ){
			if( $relation->getValue() == $relatedModuleId ){
				$relation->remove();
				$this->saveModuleXml( $moduleId, $xml );											//  save modified module XML
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *	@param		string			$moduleId
	 *	@param		string			$event
	 *	@param		string			$type
	 *	@param		string|NULL		$versionFrom
	 *	@param		string|NULL		$versionTo
	 *	@return		bool
	 *	@throws		Exception
	 */
	public function removeSql( string $moduleId, string $event, string $type, ?string $versionFrom = NULL, ?string $versionTo = NULL ): bool
	{
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		foreach( $xml->sql as $sql ){																//  iterate SQL entries
			if( $sql->event === $event && $sql->type === $type ){									//  event and type are matching
				$matchingVersions	= $sql->from === $versionFrom && $sql->from === $versionFrom;	//  compare versions
				if( $event !== "update" || ( $event === "update" && $matchingVersions ) ){			//  check versions on update
					$sql->remove();																	//  remove XML node
					$this->saveModuleXml( $moduleId, $xml );										//  save modified module XML
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	//  --  PROTECTED  --  //

	protected function hasXmlNode( $xml, string $nodeName ): bool
	{
		$children	= [];
		foreach( $xml->children() as $child )														//  iterate children
			$children[]	= $child->getName();
		return in_array( $nodeName, $children );
	}

	/**
	 *	@param		string		$moduleId
	 *	@return		Element
	 *	@throws		Exception
	 */
	protected function loadModuleXml( string $moduleId ): Element
	{
		$moduleFile	= $this->path.$moduleId.'.xml';
		if( !file_exists( $moduleFile ) )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
		return XmlReader::readFile( $moduleFile );
	}

	/**
	 *	@param		string				$moduleId
	 *	@param		SimpleXMLElement	$xml
	 *	@return		int
	 */
	protected function saveModuleXml( string $moduleId, SimpleXMLElement $xml ): int
	{
		$moduleFile	= $this->path.$moduleId.'.xml';
		if( !file_exists( $moduleFile ) )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
		$xml	= XmlFormatter::format( $xml->asXML(), TRUE );
		return FileWriter::save( $moduleFile, $xml );
	}
}
