<?php
/**
 *	Language Class of Framework Hydrogen.
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
 *	@package		framework.hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.5
 */
/**
 *	Language Class of Framework Hydrogen.
 *	@category		cmClasses
 *	@package		framework.hydrogen
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2007-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmclasses/
 *	@since			01.09.2006
 *	@version		0.5
 */
class Framework_Hydrogen_Language
{
	/**	@var		Framework_Hydrogen_Environment	$env			Application Environment Object */
	protected $env;

	/**	@var		string							$language		Set Language */
	var $language;
	/**	@var		array							$envKeys		Keys of Environment */
	var $envKeys	= array(
		'config',
		'messenger',
		);
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Framework_Hydrogen_Environment	$env			Application Environment Object
	 *	@param		string							$language		Language to select
	 *	@return		void
	 */
	public function __construct( Framework_Hydrogen_Environment $env, $language = "" )
	{
		$this->env	= $env;
		if( $language )
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
		$this->language	= $language;
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
	 *	@return		array
	 */
	public function getWords( $topic, $force = FALSE )
	{
		if( isset( $this->_data[$topic] ) )
			return $this->_data[$topic];
		else if( $force )
			$this->env->getMessenger()->noteFailure( 'Language Topic "'.$topic.'" is not defined yet' );
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
			$this->_data[$topic]	= $data;
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
		$pathname	= $this->env->getConfig()->get( 'paths.languages' );
		$filename	= $this->language."/".$topic.".ini";	
		return $pathname.$filename;
	}
}