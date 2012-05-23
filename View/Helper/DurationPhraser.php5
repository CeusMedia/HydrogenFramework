<?php
/**
 *	View helper for converting timestamps.
 *
 *	Copyright (c) 2010-2012 Christian Würker (ceus-media.de)
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
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.4
 *	@version		$Id$
 */
/**
 *	View helper for converting timestamps.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@extends		CMF_Hydrogen_View_Helper_Abstract
 *	@uses			Alg_Time_DurationPhraser
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.4
 *	@version		$Id$
 */
class CMF_Hydrogen_View_Helper_DurationPhraser extends CMF_Hydrogen_View_Helper_Abstract{

	protected $phraser		= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->setEnv( $env );
	}

	/**
	 *	Sets path to ranges by language topic and language section (in loaded topic).
	 *	@access		public
	 *	@param		string		$topic			Key of language file
	 *	@param		string		$section		Section name in loaded language file
	 *	@throws		InvalidArgumentException	if section is not available in language file
	 *	@return		void
	 */
	public function setLanguage( $topic, $section ){
		$words		= $this->env->language->getWords( $topic );
		if( !isset( $words[$section] ) )
			throw new InvalidArgumentException( 'Invalid language section "'.$section.'" in topic "'.$topic.'"' );
		$this->phraser	= new Alg_Time_DurationPhraser( $words[$section] );
	}

	public function getFromTimestamp( $timestamp ){
		if( !$this->phraser )
			throw new RuntimeException( 'No language source set' );
		return $this->phraser->getPhraseFromTimestamp( $timestamp );
	}

	public function getFromSeconds( $seconds ){
		if( !$this->phraser )
			throw new RuntimeException( 'No language source set' );
		return $this->phraser->getPhraseFromSeconds( $seconds );
	}
}
?>
