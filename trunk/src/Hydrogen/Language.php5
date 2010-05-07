<?php
/**
 *	Language Class of Framework Hydrogen.
 *
 *	Copyright (c) 2007-2010 Christian Würker (ceus-media.de)
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
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
/**
 *	Language Class of Framework Hydrogen.
 *	@category		cmFrameworks
 *	@package		Hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class Framework_Hydrogen_Language
{
	/**	@var		array							$data			Array of loaded Language File Definitions */
	protected $data;
	/**	@var		Framework_Hydrogen_Environment	$env			Application Environment Object */
	protected $env;
	/**	@var		string							$filePath		Path to Language Files */
	protected $filePath;
	/**	@var		string							$language		Set Language */
	protected $language;
	/**	@var		array							$languages		List of allowed Languages */
	protected $languages;
	/**	@var		array							$envKeys		Keys of Environment */
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment	$env			Application Environment Object
	 *	@param		string							$language		Language to select
	 *	@return		void
	 */
	public function __construct( Framework_Hydrogen_Environment $env )
	{
		$this->env			= $env;
		$this->filePath		= $this->env->getConfig()->get( 'path.locales' );
		$languages			= $this->env->getConfig()->get( 'locale.allowed' );
		$language			= $this->env->getConfig()->get( 'locale.default' );
		$this->languages	= explode( ',', $languages );
		if( $this->env->getSession()->get( 'language' ) )
			$language		= $this->env->getSession()->get( 'language' );
		$this->setLanguage( $language );
	}
	
	/**
	 *	Sets a Language.
	 *	@access		public
	 *	@param		string		$language		Language to select
	 *	@return		void
	 */
	public function setLanguage( $language )
	{
		$language	= strtolower( $language );
		if( !in_array( $language, $this->languages ) )
			return FALSE;
		$this->data		= array();
		$this->language	= $language;
		$this->load( 'main' );
		return TRUE;
	}
	
	/**
	 *	Returns selected Language.
	 *	@access		public
	 *	@return		string
	 */
	public function getLanguage()
	{
		return $this->language;
	}
	
	/**
	 *	Returns Array of Word Pairs of Language Topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		bool		$force			Flag: note Failure if not loaded
	 *	@return		array
	 */
	public function getWords( $topic, $force = FALSE )
	{
		if( isset( $this->data[$topic] ) )
			return $this->data[$topic];
		else if( $force )
			$this->env->getMessenger()->noteFailure( 'Language Topic "'.$topic.'" is not defined yet' );
		return array();
	}
	
	/**
	 *	Loads Language File by Topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@return		void
	 */
	public function load( $topic, $force = FALSE )
	{
		$filename	= $this->getFilenameOfLanguage( $topic );
		if( file_exists( $filename ) )
		{
			$data	= parse_ini_file( $filename, TRUE );
			$this->data[$topic]	= $data;
		}
		else if( $force )
			$this->env->getMessenger()->noteFailure( 'Language File "'.$topic.'" is not defined yet' );
	}

	/**
	 *	Returns File Name of Language Topic.
	 *	@access		protected
	 *	@param		string		$topic			Topic of Language
	 *	@return		void
	 */
	protected function getFilenameOfLanguage( $topic )
	{
		return $this->filePath.$this->language."/".$topic.".ini";	
	}
}