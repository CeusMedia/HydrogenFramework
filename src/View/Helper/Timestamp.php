<?php
/**
 *	View helper for converting and displaying timestamps.
 *
 *	Copyright (c) 2010-2016 Christian Würker (ceusmedia.de)
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
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.5
 *	@version		$Id$
 */
/**
 *	View helper for converting and displaying timestamps.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@extends		CMF_Hydrogen_View_Helper_Abstract
 *	@uses			UI_HTML_Tag
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			http://code.google.com/p/cmframeworks/
 *	@since			0.5
 *	@version		$Id$
 */
class CMF_Hydrogen_View_Helper_Timestamp extends CMF_Hydrogen_View_Helper_Abstract{

	protected $timestamp			= NULL;
	protected $stringEmpty			= "";
	public static $formatDatetime	= 'Y-m-d H:i:s';
	public static $formatDate		= 'Y-m-d';
	public static $formatTime		= 'H:i:s';

	public function __construct( $timestamp, $stringEmpty = "---" ){
		$this->timestamp	= $timestamp;
		$this->stringEmpty	= $stringEmpty;
	}

	public function toDatetime( $format = NULL, $html = FALSE ){
		if( !$this->timestamp )
			return $this->stringEmpty;
		$format	= $format ? $format : self::$formatDatetime;
		$date	= date( $format, $this->timestamp );
		if( $html ){
			$attr	= array( 'class' => 'datetime' );
			$date	= UI_HTML_Tag::create( 'span', $date, $attr );
		}
		return $date;
	}

	public function toDate( $format = NULL ){
		if( !$this->timestamp )
			return '-';
		$format	= $format ? $format : self::$formatDate;
		$date	= date( $format, $this->timestamp );
		if( $html ){
			$attr	= array( 'class' => 'date' );
			$date	= UI_HTML_Tag::create( 'span', $date, $attr );
		}
		return $date;
	}

	public function toTime( $format = NULL ){
		if( !$this->timestamp )
			return '-';
		$format	= $format ? $format : self::$formatTime;
		$time	= date( $format, $this->timestamp );
		if( $html ){
			$attr	= array( 'class' => 'time' );
			$time	= UI_HTML_Tag::create( 'span', $time, $attr );
		}
		return $time;
	}

	public function toPhrase( $env, $html = FALSE, $languageTopic = 'main', $languageSection = 'phrases-time' ){
		if( !$this->timestamp )
			return '-';
		$phraser	= new CMF_Hydrogen_View_Helper_DurationPhraser( $env );
		$phraser->setLanguage( $languageTopic, $languageSection );
		$phrase		= $phraser->getFromTimestamp( $this->timestamp );
		if( $html ){
			$attr		= array( 'class' => 'phrase' );
			$datetime	= $this->toDatetime();
			$acronym	= UI_HTML_Tag::create( 'abbr', $phrase, array( 'title' => $datetime ) );
			$phrase		= UI_HTML_Tag::create( 'span', $acronym, $attr );
		}
		return $phrase;
	}

	static public function statePhrase( $timestamp, $env, $html = FALSE, $languageTopic = 'main', $languageSection = 'phrases-time' ){
		$instance	= new CMF_Hydrogen_View_Helper_Timestamp( $timestamp );
		return $instance->toPhrase( $env, $html, $languageTopic, $languageSection );
	}
}
?>