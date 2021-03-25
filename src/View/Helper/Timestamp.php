<?php
/**
 *	View helper for converting and displaying timestamps.
 *
 *	Copyright (c) 2010-2021 Christian Würker (ceusmedia.de)
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
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	View helper for converting and displaying timestamps.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo 			enable environment after interface and abstract support $env on construction
 */
class CMF_Hydrogen_View_Helper_Timestamp extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $timestamp			= NULL;

	protected $stringEmpty			= "";

	public static $formatDatetime	= 'Y-m-d H:i:s';

	public static $formatDate		= 'Y-m-d';

	public static $formatTime		= 'H:i:s';

	/**
	 *	@todo 		enable environment after interface and abstract support $env on construction
	 */
	public function __construct( /*CMF_Hydrogen_Environment $env,*/ $timestamp, string $stringEmpty = "---" )
	{
		$this->timestamp	= $timestamp;
		$this->stringEmpty	= $stringEmpty;
	}

	public function toDate( string $format = NULL, bool $html = FALSE ): string
	{
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

	public function toDatetime( string $format = NULL, bool $html = FALSE ): string
	{
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

	public function toPhrase( CMF_Hydrogen_Environment $env, bool $html = FALSE, string $languageTopic = 'main', string $languageSection = 'phrases-time' ): string
	{
		if( !$this->timestamp )
			return '-';

		$words	= $env->getLanguage()->getWords( $languageTopic );
		if( !isset( $words[$languageSection] ) )
			throw new InvalidArgumentException( 'Invalid language section "'.$languageSection.'" in topic "'.$languageTopic.'"' );
		$phraser	= new Alg_Time_DurationPhraser( $words[$languageSection] );
		$phrase		= $phraser->getPhraseFromTimestamp( $this->timestamp );

		if( $html ){
			$attr		= array( 'class' => 'phrase' );
			$datetime	= $this->toDatetime();
			$acronym	= UI_HTML_Tag::create( 'abbr', $phrase, array( 'title' => $datetime ) );
			$phrase		= UI_HTML_Tag::create( 'span', $acronym, $attr );
		}
		return $phrase;
	}

	public function toTime( string $format = NULL, bool $html = FALSE ): string
	{
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

	public static function statePhrase( $timestamp, CMF_Hydrogen_Environment $env, bool $html = FALSE, string $languageTopic = 'main', string $languageSection = 'phrases-time' ): string
	{
		$instance	= new CMF_Hydrogen_View_Helper_Timestamp( $timestamp );
		return $instance->toPhrase( $env, $html, $languageTopic, $languageSection );
	}
}
