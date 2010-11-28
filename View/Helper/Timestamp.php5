<?php
class CMF_Hydrogen_View_Helper_Timestamp extends CMF_Hydrogen_View_Helper_Abstract{

	protected $timestamp			= NULL;
	public static $formatDatetime	= 'Y-m-d H:i:s';
	public static $formatDate		= 'Y-m-d';
	public static $formatTime		= 'H:i:s';

	public function __construct( $timestamp ){
		$this->timestamp	= $timestamp;
	}

	public function toDatetime( $format = NULL, $html = FALSE ){
		if( !$this->timestamp )
			return '-';
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
			$acronym	= UI_HTML_Elements::Acronym( $phrase, $datetime );
			$phrase		= UI_HTML_Tag::create( 'span', $acronym, $attr );
		}
		return $phrase;
	}
}
?>