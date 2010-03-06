<?php
/**
 *	Generic Action Handler.
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
 *	@package		framework.neon
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
import( 'de.ceus-media.adt.Reference' );
import( 'de.ceus-media.alg.TimeConverter' );
/**
 *	Generic Action Handler.
 *	@category		cmClasses
 *	@package		framework.neon
 *	@abstract
 *	@uses			ADT_Reference
 *	@uses			Alg_TimeConverter
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.1
 */
abstract class Framework_Neon_Component
{
	/**	@var		array						$config				Configuration Array */
	protected $config;
	/**	@var		Net_HTTP_Request_Receiver	$request			Request Object */
	protected $request;
	/**	@var		Net_HTTP_PartitionSession	$session			Session Object */
	protected $session;
	/**	@var		Framework_Neon_Language		$language			Language Support Object */
	protected $language;
	/**	@var		ADT_Reference				$ref				Object Reference */
	protected $ref;
	/**	@var		Framework_Neon_Messenger	$messenger			Messenger */
	protected $messenger;
	/**	@var		Alg_TimeConverter			$actions			Array of Action events and methods */
	protected $tc								= array();
	/**	@var		array						$words				Array of all Words */
	protected $words;

	/**	@var	array		$contentPaths			Array of possible Path Keys in Config for Content Loading */
	protected $contentPaths	= array(
			'html'	=> 'html',
			'wiki'	=> 'wiki',
			'txt'	=> 'text',
			);
	/**	@var		Framework_Neon_Language		$language		Language  */
	/**	@var	UI_HTML_WikiParser			$wiki			Wiki Parser */
	var $wiki;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $useWikiParser = FALSE )
	{
		$this->ref			= new ADT_Reference();
		$this->tc			= new Alg_TimeConverter;
		$this->config	 	= $this->ref->get( 'config' );
		$this->request	 	= $this->ref->get( 'request' );
		$this->session		= $this->ref->get( 'session' );
		$this->messenger	= $this->ref->get( 'messenger' );
		$this->language		= $this->ref->get( 'language' );
		$this->words		=& $this->ref->get( 'words' );
		$this->multilingual	= isset( $this->config['languages'] );

		$this->html			= new UI_HTML_Elements;
		if( $useWikiParser )
		{
			import( 'de.ceus-media.ui.html.WikiParser' );
			$this->wiki		= new UI_HTML_WikiParser;
		}
	}

	/**
	 *	Returns a float formated as Currency.
	 *	@access		public
	 *	@static
	 *	@param		mixed		$price			Price to be formated
	 *	@param		string		$separator		Separator
	 *	@return		string
	 */
	public static function formatPrice( $price, $separator = "." )
	{
		$price	= (float) $price;
		ob_start();
		$price	= sprintf( "%01.2f", $price );
		$price	= str_replace( ".", $separator, $price );
		return $price;
	}

	/**
	 *	Loads a Language File into Language Space, needs Session.
	 *	@access		public
	 *	@param		string		$filename			File Name of Language File
	 *	@param		string		$section			Section Name in Language Space
	 *	@return		void
	 */
	public function loadLanguage( $filename, $section = false, $verbose = true )
	{
		$language	= $this->ref->get( 'language' );
		$language->loadLanguage( $filename, $section, $verbose );
	}

	/**
	 *	Loads Template File and returns Content.
	 *	@access		public
	 *	@param		string		$_template			Template Name (namespace(.class).view, i.E. example.add)
	 *	@param		array		$data				Data for Insertion in Template
	 *	@param		string		$separator_link		Separator in Language Link
	 *	@param		string		$separator_class		Separator for Language File
	 *	@return		string
	 */
	public function loadTemplate( $_template, $data = array(), $separator_link = ".", $separator_file = "/" )
	{
		$config	= $this->ref->get( "config" );
		$_file	= str_replace( $separator_link, $separator_file, $_template );

		$_template_theme	= "";		
		if( isset( $config['layout']['template_theme'] ) )
			if( $config['layout']['template_theme'] )
				$_template_theme	= $config['layout']['template_theme']."/";
		
		$_content	= "";
		$_path		= isset( $config['paths']['templates'] ) ? $config['paths']['templates'] : "templates/";
		$_filename	= $_path.$_template_theme.$_file.".phpt";
		extract( $data );
		if( file_exists( $_filename ) )
		{
			$_content = include( $_filename );
		}
		else
			$this->messenger->noteFailure( "Template '".$_filename."' for View '".$_template."' is not existing" );
		return $_content;
	}

	public function getContentUri( $file )
	{
		$parts		= explode( ".", $file );
		$ext		= array_pop( $parts );
		$file		= array_pop( $parts );
		$fileName	= $file.".".$ext;
		$filePath	= $parts ? implode( "/", $parts )."/" : "";

		if( !array_key_exists( $ext, $this->contentPaths ) )
			return !$this->messenger->noteFailure( "Content Type '".$ext."' is not registered." );

		$typePath	= $this->contentPaths[$ext];
		$basePath	= $this->config['paths'][$typePath];
		if( $this->multilingual )
			$basePath	.= $this->session->get( 'language' )."/";
		return $basePath.$filePath.$fileName;
	}

	public function hasContent( $file )
	{
		return file_exists( $this->getContentUri( $file ) );
	}

	/**
	 *	Loads Content File in HTML or DokuWiki-Format returns Content.
	 *	@access		public
	 *	@param		string		$fileKey			File Name (with Extension) of Content File (HTML|Wiki|Text), i.E. home.html leads to {CONTENT}/[{LANGUAGE}/]home.html
	 *	@param		array		$data				Data for Insertion in Template
	 *	@param		string		$separator_link		Separator in Language Link
	 *	@param		string		$separator_class	Separator for Language File
	 *	@return		string
	 */
	public function loadContent( $fileKey, $data = array() )
	{
		$fileUri	= $this->getContentUri( $fileKey );
		$parts		= explode( ".", $fileKey );
		$ext		= array_pop( $parts );

		$content	= "";
		if( $ext == "wiki" && $this->wiki )
		{
			$typePath	= $this->contentPaths['wiki'];
			$cacheFile	= $this->config['paths']['cache'].$typePath."/".$fileKey;
			if( $this->multilingual )
				$cacheFile	= $this->config['paths']['cache'].$typePath."/".$session->get( 'language' )."/".$fileKey;

			if( file_exists( $cacheFile ) && filemtime( $fileUri ) <= filemtime( $cacheFile ) )
			{
				$file		= new File_Reader( $cacheFile );
				$content	= $file->readString();
			}
			else if( file_exists( $filename ) )
			{
				$file		= new File_Reader( $fileUri );
				$cache		= new File_Writer( $cacheFile, 0755 );
				$content	= $this->wiki->parse( $file->readString() );
				$cache->writeString( $content );
				$content = "<div class='wiki'>".$content."</div>";
			}
			else
				$this->messenger->noteFailure( "Content File '".$fileUri."' is not existing." );
		}
		else if( $ext == "html" )
		{
			if( file_exists( $fileUri ) )
			{
				$file		= new File_Reader( $fileUri );
				$content	= $file->readString();
			}
			else
				$this->messenger->noteFailure( "Content File '".$fileUri."' is not existing." );
		}
		foreach( $data as $key => $value )
			$content	= str_replace( "[#".$key."#]", $value, $content );
		return $content;
	}
}
?>