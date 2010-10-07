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
 *	@package		Hydrogen.Environment.Resource
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
 *	@package		Hydrogen.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2010 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.1
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Language
{
	/**	@var		array								$data			Array of loaded Language File Definitions */
	protected $data;
	/**	@var		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object */
	protected $env;
	/**	@var		string								$filePath		Path to Language Files */
	protected $filePath;
	/**	@var		string								$language		Set Language */
	protected $language;
	/**	@var		array								$languages		List of allowed Languages */
	protected $languages;
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env			Application Environment Object
	 *	@param		string								$language		Language to select
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env			= $env;
		$config				= $env->getConfig();

		$this->filePath		= (string) $config->get( 'path.locales' );
		$languages			= $config->has( 'locale.allowed' ) ? $config['locale.allowed'] : 'en';
		$language			= $config->has( 'locale.default' ) ? $config['locale.default'] : 'en';
		$this->languages	= explode( ',', $languages );

		if( $this->env->getSession() && $this->env->getSession()->get( 'language' ) )
			$language		= $this->env->getSession()->get( 'language' );
		$this->setLanguage( $language );
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
	 *	@param		bool		$strict			Flag: throw exception if language topic is not loaded
	 *	@param		bool		$force			Flag: note Failure if not loaded
	 *	@return		array
	 *	@throws		RuntimeException if language topic is not loaded and strict is on
	 */
	public function getWords( $topic, $strict = TRUE, $force = TRUE )
	{
		if( isset( $this->data[$topic] ) )
			return $this->data[$topic];
		$message	= 'Invalid language topic "'.$topic.'"';
		if( $strict )
			throw new RuntimeException( $message, 221 );
		if( $force )
			$this->env->getMessenger()->noteFailure( $message );
		return array();
	}

	public function hasWords( $topic )
	{
		return isset( $this->data[$topic] );
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

	/**
	 *	Loads Language File by Topic.
	 *	@access		public
	 *	@param		string		$topic			Topic of Language
	 *	@param		bool		$strict			Flag: throw Exception if language file is not existing
	 *	@return		void
	 *	@throws		RuntimeException if language file is not existing (and strict is on)
	 */
	public function load( $topic, $strict = FALSE, $force = FALSE )
	{
		$fileName	= $this->getFilenameOfLanguage( $topic );
		if( file_exists( $fileName ) )
		{
			$data	= parse_ini_file( $fileName, TRUE );
			$this->data[$topic]	= $data;
		}
		else
		{
			$message	= 'Invalid language file "'.$topic.' ('.$fileName.')"';
			if( $strict )
				throw new RuntimeException( $message, 221 );
			if( $force )
				$this->env->getMessenger()->noteFailure( $message );
		}
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
}
?>