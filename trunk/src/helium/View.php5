<?php
/**
 *	Generic View with Language Support.
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
 *	@package		framework.helium
 *	@uses			ADT_Reference
 *	@uses			UI_HTML_Elements
 *	@uses			UI_HTML_Paging
 *	@uses			Alg_TimeConverter
 *	@uses			File_INI_Reader
 *	@uses			WikiParser
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.3
 */
import( 'de.ceus-media.adt.Reference' );
import( 'de.ceus-media.ui.html.Elements' );
import( 'de.ceus-media.ui.html.Paging' );
import( 'de.ceus-media.alg.TimeConverter' );
import( 'de.ceus-media.file.ini.Reader' );
import( 'de.ceus-media.ui.html.WikiParser' );
/**
 *	Generic View with Language Support.
 *	@category		cmClasses
 *	@package		framework.helium
 *	@uses			ADT_Reference
 *	@uses			UI_HTML_Elements
 *	@uses			UI_HTML_Paging
 *	@uses			Alg_TimeConverter
 *	@uses			File_INI_Reader
 *	@uses			WikiParser
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.12.2005
 *	@version		0.3
 */
class Framework_Helium_View
{
	/**	@var	ADT_Reference	$ref		Object Reference */
	var $ref;

	/**	@var	array			$_paths		Array of possible Path Keys in Config for Content Loading */
	var $_paths	= array(
			'html'	=> 'html',
			'wiki'	=> 'wiki',
			'txt'	=> 'text',
			);

	/**
	 *	Constructor, references Output Objects.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->ref			= new ADT_Reference();
		$this->tc			= new Alg_TimeConverter;
		$this->html			= new UI_HTML_Elements;
		$this->wiki			= new WikiParser;
		$this->lan			= $this->ref->get( 'language' );
		$this->messenger	= $this->ref->get( 'messenger' );
	}

	/**
	 *	Builds HTML for Paging of Lists.
	 *	@access		public
	 *	@param		int		$count_all		Total mount of total entries
	 *	@param		int		$limit			Maximal amount of displayed entries
	 *	@param		int		$offset			Currently offset entries
	 *	@param		array	$options		Array of Options to set
	 *	@return		string
	 */
	public function buildPaging( $count_all, $limit, $offset, $options = array())
	{
		$request	= $this->ref->get( "request" );
		$link		= $request->get( 'link');

		$p	= new UI_HTML_Paging;
		$p->setOption( 'uri',	"index.php" );
		$p->setOption( 'param',	array( 'link'	=> $link ) );
		$p->setOption( 'indent',	"" );

		foreach( $options as $key => $value )
			$p->setOption( $key, $value );
		
		$p->setOption( 'text_first',	$this->lan['paging']['first'] );
		$p->setOption( 'text_previous',	$this->lan['paging']['previous'] );
		$p->setOption( 'text_next',		$this->lan['paging']['next'] );
		$p->setOption( 'text_last',		$this->lan['paging']['last'] );
		$p->setOption( 'text_more',		$this->lan['paging']['more'] );
		
		$pages	= $p->build( $count_all, $limit, $offset );
		return $pages;
	}

	/**
	 *	Highlights a String within a String.
	 *	@access		public
	 *	@param		string		$text			String to highlight within
	 *	@param		string		$searches		Array of String to highlight
	 *	@return 	string
	 */
	public function hilight( $text, $searches )
	{
		if( is_array( $searches ) && count( $searches ) )
		{
			$list	= array();
			foreach( $searches as $search )
			{
				$length	= strlen( $search );
				if( !isset( $list[$length] ) )
					$list[$length]	= array();
				$list[$length][]	= $search;
			}
			krsort( $list );
			$searches	= array();
			$i=0;
			foreach( $list as $length )
				foreach( $length as $search )
				{
					$matches = array();
					preg_match_all( "/".$search."/si", $text, $matches );
					foreach( $matches[0] as $match)
					{
						$text	= preg_replace( "/".$match."/si", "[#".$i."#]", $text, 1 );
						$searches[$i++] = $match;
					}
				}
			foreach( $searches as $key => $search )
				$text	= preg_replace( "/\[#".$key."#\]/", "<span class='highlight'>".$search."</span>", $text, 1 );
		}
		return $text;
	}

	/**
	 *	Shortens a string by a maximum length with a mask.
	 *	@access		public
	 *	@param		string		$string		String to be shortened
	 *	@param		int			$length		Maximum length to cut at
	 *	@param		string		$mask		Mask to append to shortened string
	 *	@return		string
	 */
	public function str_shorten( $string, $length = 20, $mask = "..." )
	{
		if( $length )
		{
			$inner_length	= $length - strlen( $mask );
			$sting_length	= strlen( $string );
			if( $sting_length > $inner_length )
				$string	= substr( $string, 0, $inner_length ).$mask;
		}
		return $string;
	}

	/**
	 *	Transforms a formated String to HTML.
	 *	@access		public
	 *	@param		string		$string		String to be transformed
	 *	@return		string
	 */
	public function transform( $string )
	{
		$string	= htmlspecialchars( $string );
		$pattern	= "@(\[d\])(.*)(\[/d\])@si";
		$string	= preg_replace_callback( $pattern, array( $this, "transform_callback" ), $string );
		$pattern	= "@(\[k\])(.*)(\[/k\])@si";
		$string	= preg_replace_callback( $pattern, array( $this, "transform_callback" ), $string );
		$pattern	= "@(\[g\])(.*)(\[/g\])@si";
		$string	= preg_replace_callback( $pattern, array( $this, "transform_callback" ), $string );
		$string	= nl2br( $string );
		return $string;
	}
	
	/**
	 *	Callback for String Transformation.
	 *	@access		public
	 *	@param		string		$string		String to be transformed
	 *	@return		string
	 */
	public function transform_callback( $matches )
	{
		if( $matches[0] )
		{
			if( substr( $matches[1], 1, 1 ) == "d" )
				$string	= "<b>".$matches[2]."</b>";
			else if( substr( $matches[1], 1, 1 ) == "k" )
				$string	= "<em>".$matches[2]."</em>";
			else if( substr( $matches[1], 1, 1 ) == "g" )
				$string	= "<font size='+1'>".$matches[2]."</font>";
		}
		return $string;
	}
	
	/**
	 *	Returns a float formated as Currency.
	 *	@access		public
	 *	@param		mixed		$price			Price to be formated
	 *	@param		string		$separator		Separator
	 *	@return		string
	 */
	public function formatPrice( $price, $separator = "." )
	{
		$price	= (float)$price;
		ob_start();
		printf( "%01".$separator."2f", $price );
		return ob_get_clean();
	}

	/**
	 *	Loads Content File in HTML or DokuWiki-Format returns Content.
	 *	@access		public
	 *	@param		string		$_file				File Name (with Extension) of Content File (HTML|Wiki|Text), i.E. home.html leads to {CONTENT}/{LANGUAGE}/home.html
	 *	@param		array		$data				Data for Insertion in Template
	 *	@return		string
	 */
	public function loadContent( $_file, $data = array() )
	{
		$config		= $this->ref->get( "config" );
		$session	= $this->ref->get( "session" );

		$parts		= explode( ".", $_file );
		$ext			= array_pop( $parts );
		$file			=array_pop( $parts );
		$basename	= $file.".".$ext;
		
		$path		= $this->_paths[$ext];
		$uri			= $config['paths'][$path].$session->get( 'language' )."/".implode( "/", $parts )."/";
//		$theme		= $config['layout']['template_theme'] ? $config['layout']['template_theme']."/" : "";
		$theme		= "";
		$filename		= $uri.$theme.$basename;
		
		if( file_exists( $filename ) )
		{
			$file	= new File_Reader( $filename );
			$content	= $file->readString();
			foreach( $data as $key => $value )
				$content	= str_replace( "[#".$key."#]", $value, $content );
			if( $ext == "wiki" )
			{
				$content = "<div class='wiki'>".$this->wiki->parse( $content )."</div>";
			}
			else if( $ext == "html" )
			{
			}
			else
				$this->messenger->noteFailure( "Content Type for File '".$filename."' is not implemented." );
		}
		else
			$this->messenger->noteFailure( "Content File '".$filename."' is not existing." );
		return $content;
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
			
		extract( $data );
		$_content	= "";
		$_filename	= "templates/".$_template_theme.$_file.".phpt";
		if( file_exists( $_filename ) )
			$_content = include( $_filename );
		else
			$this->messenger->noteFailure( "Template '".$_filename."' for View '".$_template."' is not existing" );
		return $_content;
	}

	/**
	 *	Loads a Language File into Language Space, needs Session.
	 *	@access		public
	 *	@param		string		$section		Section Name in Language Space
	 *	@param		tring		$filename		File Name of Language File
	 *	@return		void
	 */
	public function loadLanguage( $section, $filename = false, $verbose = true )
	{
		$session	= $this->ref->get( 'session' );
		if( !$filename )
			$filename	= $section;
		$uri	= "languages/".$session->get( 'language' )."/".$filename.".lan";
		if( file_exists( $uri ) )
		{
			$ir	= new File_INI_Reader( $uri, true );
			$this->lan[$section]	= $ir->toArray( true );
			return true;
		}
		else if( $verbose )
			$this->messenger->noteFailure( "Language File '".$filename."' is not existing in '".$uri."'" );
		return false;
	}

	public  function loadCache( $filename )
	{
		$config	= $this->ref->get( 'config' );
		$url	= $config['paths']['cache'].$filename;
		$file	= new File_Reader( $url );
		return $file->readString();
	//	!( file_exists( $uri ) && filemtime( $uri ) + 3600 > time() )
		return implode( "", file( $url ) );
	}
	
	public function saveCache( $filename, $content )
	{
		$config	= $this->ref->get( 'config' );
		$url	= $config['paths']['cache'].$filename;
		$file	= new File_Writer( $url, 0750 );
		$file->writeString( $content );
	}
}
?>