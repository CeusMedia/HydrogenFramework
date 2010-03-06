<?php
/**
 *	Definition of Input Field within Channels, Screens and Forms.
 *
 *	Copyright (c) 2007-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@uses			File_Reader
 *	@uses			File_Writer
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.05.2006
 *	@version		0.6
 */
/**
 *	Definition of Input Field within Channels, Screens and Forms.
 *	@category		cmClasses
 *	@package		framework.krypton.core
 *	@uses			File_Reader
 *	@uses			File_Writer
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.05.2006
 *	@version		0.6
 */
class Framework_Krypton_Core_FormDefinitionReader
{
	/**	@var		array		$definitions	Parsed Definitions */
	protected $definitions	= array();

	/**	@var		string		$channel		Output Channel */
	protected $channel;
	/**	@var		string		$form			Form Name */
	protected $form;

	/**	@var		string		$prefix			Prefix of Definition Files */
	protected $prefix;
	/**	@var		string		$path			Path to Definition Files */
	protected $path;
	/**	@var		string		$cachePath		Path to Cache Files */
	protected $cachePath;
	/**	@var		bool		$useCache		Flag: cache Definitions in Cache Folder */
	protected $useCache;
	/**	@var		array		$tagAttributes	List of Attributes of Definition Tags */
	protected $tagAttributes	= array(
		'syntax'	=> array(
			"class",
			"mandatory",
			"minlength",
			"maxlength"
		),
		'input'		=> array(
			"name",
			"type",
			"style",
			"validator",
			"source",
			"options",
			"submit",
			"disabled",
			"hidden",
			"tabindex",
			"colspan",
			"label"
		),
		'output'	=> array(
			"source",
			"options",
			"type",
			"format",
			"structure",
			"style",
			"label",
			"hidden",
			"colspan"
		),
		'help'		=> array(
			"type",
			"file"
		),
		'calendar'	=> array(
			"component",
			"type",
			"range",
			"direction",
			"format",
			"language"
		),
	);


	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$path			Path to XML Definition File
	 *	@param		bool		$useCache		Flag: cache XML Files party in Cache Folder
	 *	@param		string		$cachePath		Path to Cache Folder
	 *	@param		string		$prefix			Prefix of XML Definition Files
	 *	@return		void
	 */
	public function __construct( $path = "", $useCache = false, $cachePath = "cache/", $prefix = "" )
	{
		$this->path		= $path;
		if( $useCache )
		{
			$this->useCache		= $useCache;
			$this->cachePath	= $cachePath;
			$this->prefix		= $prefix;
		}
	}
	
	/**
	 *	Creates nested Folder recursive.
	 *	@access		protected
	 *	@param		string		$path		Folder to create
	 *	@return		void
	 */
	protected function createFolder( $path )
	{
		if( !file_exists( $path ) )
		{
			$parts	= explode( "/", $path );
			$folder	= array_pop( $parts );
			$path	= implode( "/", $parts );
			$this->createFolder( $path );
			mkDir( $path."/".$folder );
		}
	}

	/**
	 *	Returns full File Name of Cache File.
	 *	@access		protected
	 *	@param		string		$fileName			File Name of XML Definition File
	 *	@return		string
	 */
	protected function getCacheFilename( $fileName )
	{
		$file	= $this->cachePath.$fileName."_".$this->channel."_".$this->form.".cache";
		return $file;
	}
	
	/**
	 *	Returns complete Definition of a Field.
	 *	@access		public
	 *	@param		string		$name			Name of Field
	 *	@return		array
	 */
	public function getField( $name )
	{
		if( !isset( $this->definitions[$name] ) )
			throw new InvalidArgumentException( 'Form Field "'.$name.'" is not defined.' );
		return $this->definitions[$name];
	}

	/**
	 *	Returns semantic Definition of a Field.
	 *	@access		public
	 *	@param		string		$name			Name of Field
	 *	@return		array
	 */
	public function getFieldSemantics( $name )
	{
		if( !isset( $this->definitions[$name]['semantic'] ) )
			throw new InvalidArgumentException( 'Form Field "'.$name.'" Semantic is not defined.' );
		return $this->definitions[$name]['semantic'];
	}

	/**
	 *	Returns syntactic Definition of a Field.
	 *	@access		public
	 *	@param		string		$name			Name of Field
	 *	@return		array
	 */
	public function getFieldSyntax( $name )
	{
		if( !isset( $this->definitions[$name]['syntax'] ) )
			throw new InvalidArgumentException( 'Form Field "'.$name.'" Syntax is not defined.' );
		return $this->definitions[$name]['syntax'];
	}

	/**
	 *	Returns Input Type of a Field.
	 *	@access		public
	 *	@param		string		$name			Name of Field
	 *	@return		array
	 */
	public function getFieldInput( $name )
	{
		if( !isset( $this->definitions[$name]['input'] ) )
			throw new InvalidArgumentException( 'Form Field "'.$name.'" Input is not defined.' );
		return (array)$this->definitions[$name]['input'];
	}

	/**
	 *	Returns an Array of all Field Names in Definition.
	 *	@access		public
	 *	@return		array
	 */
	public function getFields()
	{
		return array_keys( $this->definitions );	
	}

	/**
	 *	Loads Definition from XML Definition File or Cache.
	 *	@access		public
	 *	@param		string		$fileName		File Name of XML Definition File
	 *	@param		bool		$force			Flag: force Loading of XML Defintion
	 *	@return		void
	 */
	public function loadDefinition( $fileName, $force = false )
	{
		$prefix	= $this->prefix;
		$path	= $this->path;
		$xmlFile	= $path.$prefix.$fileName.".xml";
		if( !$force && $this->useCache )
		{
			$cacheFile	= $this->getCacheFilename( $fileName );
			if( file_exists( $cacheFile ) && filemtime( $xmlFile ) <= filemtime( $cacheFile ) )
			{
				import( 'de.ceus-media.file.Reader' );
				$this->definitions	= unserialize( File_Reader::load( $cacheFile ) );
				return true;
			}
		}
		if(  file_exists( $xmlFile ) )
		{
			$this->loadDefinitionXML( $xmlFile );
			if( $this->useCache )
				$this->writeCacheFile( $fileName );
		}
		else
			throw new RuntimeException( 'Definition File "'.$xmlFile.'" is not existing.' );
	}
	
	/**
	 *	Loads Definition from XML Definition File.
	 *	@access		protected
	 *	@param		string		$fileName		File Name of XML Definition File
	 *	@return		void
	 */
	protected function loadDefinitionXML( $fileName )
	{
		$this->definitions	= array();
		$doc	= new DOMDocument();
		$doc->preserveWhiteSpace	= false;
		$doc->load( $fileName );
		$channels = $doc->firstChild->childNodes;
		foreach( $channels as $channel )
		{
			if( $channel->getAttribute( "type" ) != $this->channel )
				continue;
			$forms	= $channel->childNodes;
			foreach( $forms as $form )
			{
				if( $form->nodeType != XML_ELEMENT_NODE )
					continue;
				if( $form->getAttribute( "name" ) != $this->form )
					continue;
				$fields	= $form->childNodes;
				foreach( $fields as $field )
				{
					
					if( $field->nodeType != XML_ELEMENT_NODE )
						continue;
					$_field	= array();
					$nodes	= $field->childNodes;
					foreach( $nodes as $node )
					{
						if( $node->nodeType != XML_ELEMENT_NODE )
							continue;
						$tagName	= $node->nodeName;

						//  --  GENERAL TREATMENT OF DEFINITION TAGS  --  //							 
						if( !isset( $_field[$tagName] ) )
							$_field[$tagName]	= array();
						if( isset( $this->tagAttributes[$tagName] ) )
						{
							foreach( $this->tagAttributes[$tagName] as $attribute )
								$_field[$tagName][$attribute] = $node->getAttribute( $attribute );
						}

						//  --  SPECIAL TREATMENT OF DEFINITION TAGS  --  //							 
						switch( $tagName )
						{
							case 'syntax':
								break;
							case 'semantic':
								$semantic	= array(
									'predicate'	=> $node->getAttribute( 'predicate' ),
									'edge'		=> $node->getAttribute( 'edge' ),
								);
								$_field[$tagName][] = $semantic;
								break;
							case 'input':
								$_field[$tagName]['default']	= $node->textContent;
								break;
							case 'output':
								$_field[$tagName]['default']	= $node->textContent;
								break;
							case 'calendar':
								break;
							case 'help':
								break;
							case 'hidemode':
								$_field[$tagName]['hidemode'][]	= $node->getContent();
								break;
							case 'disablemode':
								$_field[$tagName]['hidemode'][]	= $node->getContent();
								break;
							default:
								break;
						}
					}
					$fieldName	= $field->getAttribute( "name" );
					$this->definitions[$fieldName] = $_field;
				}
				break;
			}
		}
	}

	/**
	 *	Sets Output Channel.
	 *	@access		public
	 *	@param		string		$channel		Output Channel
	 *	@return		void
	 */
	public function setChannel( $channel )
	{
		$this->channel	= $channel;
	}

	/**
	 *	Sets File Prefix.
	 *	@access		public
	 *	@param		string		$prefix			Prefix of XML Files
	 *	@return		void
	 */
	public function setPrefix( $prefix )
	{
		$this->prefix	= $prefix;
	}

	/**
	 *	Sets Form Name.
	 *	@access		public
	 *	@param		string		$form			Form Name
	 *	@return		void
	 */
	public function setForm( $form )
	{
		$this->form	= $form;
	}

	/**
	 *	Writes Cache File.
	 *	@access		protected
	 *	@param		string		$fileName		File Name of XML Definition File
	 *	@return		void
	 */
	protected function writeCacheFile( $fileName )
	{
		import( 'de.ceus-media.file.Writer' );
		$cacheFile	= $this->getCacheFilename( $fileName );
		$this->createFolder( dirname( $cacheFile ) );
		$file	= new File_Writer( $cacheFile, 0755 );
		$file->writeString( serialize( $this->definitions ) );
	}
}
?>