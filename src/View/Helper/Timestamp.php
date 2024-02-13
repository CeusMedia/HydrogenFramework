<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	View helper for converting and displaying timestamps.
 *
 *	Copyright (c) 2010-2024 Christian Würker (ceusmedia.de)
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
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper;

use CeusMedia\Common\Alg\Time\DurationPhraser as TimeDurationPhraser;
use CeusMedia\Common\Alg\Time\DurationPhraseRanges as TimeDurationPhrases;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

use InvalidArgumentException;
/**
 *	View helper for converting and displaying timestamps.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo 			enable environment after interface and abstract support $env on construction
 */
class Timestamp extends Abstraction
{
	protected ?int $timestamp			= NULL;

	protected string $stringEmpty			= "";

	public static string $formatDatetime	= 'Y-m-d H:i:s';

	public static string $formatDate		= 'Y-m-d';

	public static string $formatTime		= 'H:i:s';

	/**
	 *	@todo 		enable environment after interface and abstract support $env on construction
	 */
	public function __construct( /*Environment $env,*/ ?int $timestamp, string $stringEmpty = '-' )
	{
		$this->timestamp	= $timestamp;
		$this->stringEmpty	= $stringEmpty;
	}

	public function toDate( string $format = NULL, bool $html = FALSE ): string
	{
		if( NULL === $this->timestamp || 0 === $this->timestamp )
			return $this->stringEmpty;
		$format	= $format ?: self::$formatDate;
		$date	= date( $format, $this->timestamp );
		if( $html )
			$date	= HtmlTag::create( 'span', $date, ['class' => 'date'] );
		return $date;
	}

	public function toDatetime( string $format = NULL, bool $html = FALSE ): string
	{
		if( NULL === $this->timestamp || 0 === $this->timestamp )
			return $this->stringEmpty;
		$format	= $format ?: self::$formatDatetime;
		$date	= date( $format, $this->timestamp );
		if( $html )
			$date	= HtmlTag::create( 'span', $date, ['class' => 'datetime'] );
		return $date;
	}

	public function toPhrase( Environment $env, bool $html = FALSE, string $languageTopic = 'main', string $languageSection = 'phrases-time' ): string
	{
		if( NULL === $this->timestamp || 0 === $this->timestamp )
			return $this->stringEmpty;

		$words	= $env->getLanguage()->getWords( $languageTopic );
		if( !isset( $words[$languageSection] ) )
			throw new InvalidArgumentException( 'Invalid language section "'.$languageSection.'" in topic "'.$languageTopic.'"' );
		$phraser	= new TimeDurationPhraser( new TimeDurationPhrases( $words[$languageSection] ) );
		$phrase		= $phraser->getPhraseFromTimestamp( $this->timestamp );

		if( $html ){
			$attr		= array( 'class' => 'phrase' );
			$datetime	= $this->toDatetime();
			$acronym	= HtmlTag::create( 'abbr', $phrase, array( 'title' => $datetime ) );
			$phrase		= HtmlTag::create( 'span', $acronym, $attr );
		}
		return $phrase;
	}

	public function toTime( string $format = NULL, bool $html = FALSE ): string
	{
		if( NULL === $this->timestamp || 0 === $this->timestamp )
			return $this->stringEmpty;
		$format	= $format ?: self::$formatTime;
		$time	= date( $format, $this->timestamp );
		if( $html )
			$time	= HtmlTag::create( 'span', $time, ['class' => 'time'] );
		return $time;
	}

	public static function statePhrase( ?int $timestamp, Environment $env, bool $html = FALSE, string $languageTopic = 'main', string $languageSection = 'phrases-time' ): string
	{
		$instance	= new self( $timestamp );
		return $instance->toPhrase( $env, $html, $languageTopic, $languageSection );
	}
}
