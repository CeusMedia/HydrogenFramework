<?php
/**
 *	Editor for local module XML files.
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
 *	Editor for local module XML files.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.6
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Module_Editor{

	protected $path;
	protected $nsXml	= 'http://www.w3.org/XML/1998/namespace';
	
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->path		= 'config/modules/';
		if( $env->getConfig()->get( 'path.module.config' ) )
			$this->path	= $env->getConfig()->get( 'path.module.config' );
		$this->path		= $env->path.$this->path;
	}

	/**
	 *	Adds a new configuration pair to module XML file.
	 *	@access		public
	 *	@param		string		$moduleId	Module ID
	 *	@param		string		$name		Author name
	 *	@param		string		$email		Author email address
	 *	@return		void
	 */
	public function addAuthor( $moduleId, $name, $email = NULL ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$link		= $xml->addChild( 'author', $name );
		if( strlen( trim( $email ) ) )
			$link->addAttribute( 'email', $email );
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	Adds a company to module XML file.
	 *	@access		public
	 *	@param		string		$moduleId	Module ID
	 *	@param		string		$name		Company name
	 *	@param		string		$site		Company web address
	 *	@return		void
	 */
	public function addCompany( $moduleId, $name, $site = NULL ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$link		= $xml->addChild( 'company', $name );
		if( strlen( trim( $site ) ) )
			$link->addAttribute( 'site', trim( $site ) );
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	/**
	 *	Adds a new configuration pair to module XML file.
	 *	@access		public
	 *	@param		string		$moduleId	Module ID
	 *	@param		string		$name		Pair key
	 *	@param		string		$type		Type (boolean,integer,float,string)
	 *	@param		string		$value		Pair value
	 *	@param		string		$values		List of possible values
	 *	@param		string		$mandatory	Flag: this pair needs to be set
	 *	@param		string		$protected	Flag: do not deliver this pair to frontend
	 *	@param		string		$title		Description
	 *	@return		void
	 */
	public function addConfig( $moduleId, $name, $type, $value, $values, $mandatory, $protected, $title = NULL ){
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
	 *	@param		string		$moduleId	Module ID
	 *	@param		string		$type		File resource type (class,template,locale,script,style,image)
	 *	@param		string		$data		Map object of data to set
	 *	@param		string		$source		Source, depending on type
	 *	@param		string		$load		Load mode, empty or "auto"
	 *	@return		void
	 */
	public function addFile( $moduleId, $type, $resource, $source, $load ){
		$attributes	= array( 'source', 'load' );
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
	 *	@param		string		$moduleId	Module ID
	 *	@param		string		$path		Link path
	 *	@param		string		$label		Link label
	 *	@param		string		$access		Access mode (public|inside|outside|acl)
	 *	@param		string		$language	Link language
	 *	@param		string		$rank		Link rank in navigation
	 *	@return		void
	 *	@todo		add support for attribute "link"
	 */
	public function addLink( $moduleId, $path, $label = NULL, $access = NULL, $language = NULL, $rank = NULL ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$link		= $xml->addChild( 'link', (string) $label );									//  
		if( strlen( trim( $path ) ) )																//  path attribute is given
			$link->addAttribute( 'path', trim( $path ) );											//  set path attribute
		if( strlen( trim( $access ) ) )																//  access attribute is given
			$link->addAttribute( 'access', trim( $access ) );										//  set access attribute
		if( strlen( trim( $rank ) ) )																//  rank attribute is given
			$link->addAttribute( 'rank', trim( $rank ) );											//  set rank attribute
		if( strlen( trim( $data->get( 'language' ) ) ) )											//  language attribute is given
			$link->addAttribute( 'lang', $language, 'xml', $this->nsXml );							//  set language attribute
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	public function addRelation( $moduleId, $type, $relatedModuleId ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		if( !$this->hasXmlNode( $xml, 'relations' ) )												//  relations node not yet existing
			$xml->addChild( 'relations' );															//  create relations node
		$node		= $xml->relations->addChild( $type, $relatedModuleId );							//  add new relation node
		$node->addAttribute( 'type', 'module' );													//  set type attribute on new node
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	public function addSql( $moduleId, $ddl, $event, $type ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		$ddl		= str_replace( '&', '&amp;', "\n".$ddl."\n" );
		$node		= $xml->addChildCData( 'sql', $ddl );											//  add SQL as CDATA node
		$node->addAttribute( 'on', $event );														//  set event attribute on new node
		$node->addAttribute( 'type', $type );														//  set type attribute on new node
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
		
	}

	public function editLink( $moduleId, $number, $path, $link = NULL, $label = NULL, $access = NULL, $language = NULL, $rank = NULL ){
		$xml	= $this->loadModuleXml( $moduleId );												//  load module XML
		if( !isset( $xml->link[(int) $number] ) )
			throw new OutOfRangeException( 'Invalid link number' );

		$node	= $xml->link[(int) $number];
		$node->setValue( (string) $label );

		$node->setAttribute( 'path', strlen( trim( $path ) ) );
		$node->setAttribute( 'access', strlen( trim( $access ) ) ? trim( $access ) : NULL );
		$node->setAttribute( 'link', strlen( trim( $link ) ) ? trim( $link ) : NULL );
		$node->setAttribute( 'rank', strlen( trim( $rank ) ) ? trim( $rank ) : NULL );

		$language	= strlen( trim( $language ) ) ? trim( $language ) : NULL;
		$node->setAttribute( 'lang', $language, 'xml', $this->nsXml );				//  set language attribute
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	protected function hasXmlNode( $xml, $nodeName ){
		$children	= array();
		foreach( $xml->children() as $child )														//  iterate children
			$children[]	= $child->getName();
		return in_array( $nodeName, $children );
	}

	public function removeAuthor( $moduleId, $name ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		foreach( $xml->author as $author ){
			if( $author->getValue() == $name ){
				$author->remove();
				$this->saveModuleXml( $moduleId, $xml );											//  save modified module XML
				return TRUE;
			}
		}
		return FALSE;
	}

	public function removeCompany( $moduleId, $name ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		foreach( $xml->company as $company ){
			if( $company->getValue() == $name ){
				$company->remove();
				$this->saveModuleXml( $moduleId, $xml );											//  save modified module XML
				return TRUE;
			}
		}
		return FALSE;
	}

	public function removeConfig( $moduleId, $name ){
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

	public function removeFile( $moduleId, $type, $resource ){
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
	
	public function removeLink( $moduleId, $number ){
		$xml		= $this->loadModuleXml( $moduleId );											//  load module XML
		if( !isset( $xml->link[(int) $number] ) )
			throw new OutOfRangeException( 'Invalid link number' );
		unset( $xml->link[(int) $number] );
		$this->saveModuleXml( $moduleId, $xml );													//  save modified module XML
	}

	public function removeRelation( $moduleId, $type, $relatedModuleId ){
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

	protected function loadModuleXml( $moduleId ){
		$moduleFile	= $this->path.$moduleId.'.xml';
		if( !file_exists( $moduleFile ) )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
		return XML_ElementReader::readFile( $moduleFile );
	}

	protected function saveModuleXml( $moduleId, SimpleXMLElement $xml ){
		$moduleFile	= $this->path.$moduleId.'.xml';
		if( !file_exists( $moduleFile ) )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
		$xml	= XML_DOM_Formater::format( $xml->asXML(), TRUE );
		return File_Writer::save( $moduleFile, $xml );
	}
	
}
?>