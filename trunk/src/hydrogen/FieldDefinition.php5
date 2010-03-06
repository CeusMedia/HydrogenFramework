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
 *	@package		framework.hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.05.2006
 *	@version		0.1
 */
import( 'de.ceus-media.adt.OptionObject' );
import( 'de.ceus-media.file.Reader' );
import( 'de.ceus-media.file.Writer' );
/**
 *	Definition of Input Field within Channels, Screens and Forms.
 *	@package		framework.hydrogen
 *	@extends		ADT_OptionObject
 *	@uses			File_Reader
 *	@uses			File_Writer
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.05.2006
 *	@version		0.1
 */
class Framework_Hydrogen_FieldDefinition extends ADT_OptionObject
{
	private $definition	= array();
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		path			Path to XML Definition File
	 *	@param		bool			use_cache	Flag: cache XML Files party in Cache Folder
	 *	@param		string		cache_path	Path to Cache Folder
	 *	@param		string		prefix		Prefix of XML Definition Files
	 *	@return		void
	 */
	public function __construct( $path = "", $use_cache = false, $cache_path = "cache/", $prefix = "" )
	{
		$this->setOption( 'path', $path );
		if( $use_cache )
		{
			$this->setOption( 'use_cache', $use_cache );
			$this->setOption( 'cache_path', $cache_path );
			$this->setOption( 'prefix', $prefix );
		}
	}

	/**
	 *	Returns full File Name of Cache File.
	 *	@access		private
	 *	@param		string		filename		File Name of XML Definition File
	 *	@return		string
	 */
	private function getCacheFilename( $filename )
	{
		$file	= $this->getOption( 'cache_path' ).$filename."_".$this->getOption( 'channel' )."_".$this->getOption( 'screen', FALSE )."_".$this->getOption( 'form' ).".cache";
		return $file;
	}
	
	/**
	 *	Returns complete Definition of a Field.
	 *	@access		public
	 *	@param		string		name		Name of Field
	 *	@return		array
	 */
	public function getField( $name )
	{
		if( isset( $this->definition[$name] ) )
			return $this->definition[$name];
		return array();
	}

	/**
	 *	Returns syntactic Definition of a Field.
	 *	@access		public
	 *	@param		string		name		Name of Field
	 *	@return		array
	 */
	public function getFieldSyntax( $name )
	{
		return $this->definition[$name]['syntax'];
	}

	/**
	 *	Returns semantic Definition of a Field.
	 *	@access		public
	 *	@param		string		name		Name of Field
	 *	@return		array
	 */
	public function getFieldSemantics( $name )
	{
		if( isset( $this->definition[$name]['semantic'] ) )
			return $this->definition[$name]['semantic'];
		return array();
	}

	/**
	 *	Returns Input Type of a Field.
	 *	@access		public
	 *	@param		string		name		Name of Field
	 *	@return		array
	 */
	public function getFieldInput( $name )
	{
		return (array)$this->definition[$name]['input'];
	}

	/**
	 *	Returns an Array of all Field Names in Definition.
	 *	@access		public
	 *	@return		array
	 */
	public function getFields()
	{
		return array_keys( $this->definition );	
	}

	/**
	 *	Loads Definition from XML Definition File or Cache.
	 *	@access		public
	 *	@param		string		filename		File Name of XML Definition File
	 *	@param		bool			force		Flag: force Loading of XML Defintion
	 *	@return		void
	 */
	public function loadDefinition( $filename, $force = false )
	{
		$prefix	= $this->getOption( 'prefix' );
		$path	= $this->getOption( 'path' );
		$xml_file	= $path.$prefix.$filename.".xml";
		if( !$force && $this->getOption( 'use_cache' ) )
		{
			$cache_file	= $this->getCacheFilename( $filename );
			if( file_exists( $cache_file ) && filemtime( $xml_file ) <= filemtime( $cache_file ) )
			{
				$file	= new File_Reader( $cache_file );
				$this->definition	= unserialize( $file->readString() );
				return true;
			}
		}
		if(  file_exists( $xml_file ) )
		{
			$this->loadDefinitionXML( $xml_file );
			if( $this->getOption( 'use_cache' ) )
				$this->writeCacheFile( $filename );
		}
		else
			trigger_error( "Definition File '".$xml_file."' is not existing", E_USER_ERROR );
	}

	/**
	 *	Sets Output Channel.
	 *	@access		public
	 *	@param		string		channel		Output Channel
	 *	@return		void
	 */
	public function setChannel( $channel )
	{
		$this->setOption( 'channel', $channel );
	}

	/**
	 *	Sets Channel Screen.
	 *	@access		public
	 *	@param		string		screen		Channel Screen
	 *	@return		void
	 */
	public function setScreen( $screen )
	{
		$this->setOption( 'screen', $screen );
	}

	/**
	 *	Sets Screen Form
	 *	@access		public
	 *	@param		string		form			Screen Form
	 *	@return		void
	 */
	public function setForm( $form )
	{
		$this->setOption( 'form', $form );
	}
	
	//  --  PRIVATE METHODS  --  //
	/**
	 *	Loads Definition from XML Definition File.
	 *	@access		private
	 *	@param		string		filename		File Name of XML Definition File
	 *	@return		void
	 */
	private function loadDefinitionXML( $filename )
	{
		$this->definition	= array();

		$doc	= new DOMDocument();
		$doc->preserveWhiteSpace	= false;
		$doc->load( $filename );
		$channels = $doc->firstChild->childNodes;
		foreach( $channels as $channel )
		{
			if( $channel->getAttribute( "type" ) == $this->getOption( 'channel' ) )
			{
				$screens	= $channel->childNodes;
				foreach( $screens as $screen )
				{
					if( !$this->getOption( 'screen', FALSE ) || $screen->getAttribute( "id" ) == $this->getOption( 'screen' ) )
					{
						$forms	= $screen->childNodes;
						foreach( $forms as $form )
						{
							if( $form->nodeType == XML_ELEMENT_NODE )
							{
								if( $form->getAttribute( "name" ) == $this->getOption( 'form' ) )
								{
									$fields	= $form->childNodes;
									foreach( $fields as $field )
									{
										if( $field->nodeType == XML_ELEMENT_NODE )
										{
											$_field	= array();
											$nodes	= $field->childNodes;
											foreach( $nodes as $node )
											{
												$name	= $node->nodeName;
												if( $name	 == "syntax" )
												{
													$keys	= array( "class", "mandatory", "minlength", "maxlength", "preg" );
													foreach( $keys as $key )
														$_field[$name][$key] = $node->getAttribute( $key );
												}
												else if( $name	 == "semantic" )
												{
													$semantic	= array(
														'predicate'	=> $node->getAttribute( 'predicate' ),
														'edge'		=> $node->getAttribute( 'edge' ),
														);
													$_field[$name][] = $semantic;
												}
												if( $name	 == "input" )
												{
													$keys	= array( "name", "type", "style", "validator", "source", "submit", "disabled", "hidden", "tabindex", "colspan" );
													foreach( $keys as $key )
														$_field[$name][$key]	= $node->getAttribute( $key );
													$_field[$name]['default']		= $node->textContent;
												}
												else if( $name	 == "calendar" )
												{
													$keys	= array( "component", "type", "range", "direction", "format_js", "format_php", "language" );
													foreach( $keys as $key )
														$_field[$name][$key]	= $node->getAttribute( $key );
												}
												else if( $name	 == "help" )
												{
													$keys	= array( "type", "file" );
													foreach( $keys as $key )
														$_field[$name][$key]	= $node->getAttribute( $key );
												}
												else if( $name	 == "hidemode" )
												{
													$_field[$name]['hidemode'][]	= $node->getContent();
												}
												else if( $name	 == "disablemode" )
												{
													$_field[$name]['hidemode'][]	= $node->getContent();
												}
											}
											$name	= $field->getAttribute( "name" );
											$this->definition[$name] = $_field;
										}
									}
									break;
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 *	Writes Cache File.
	 *	@access		private
	 *	@param		string		filename		File Name of XML Definition File
	 *	@return		void
	 */
	private function writeCacheFile( $filename )
	{
		$cache_file	= $this->getCacheFilename( $filename );
		$file	= new File_Writer( $cache_file, 0755 );
		$file->writeString( serialize( $this->definition ) );
	}
}
?>