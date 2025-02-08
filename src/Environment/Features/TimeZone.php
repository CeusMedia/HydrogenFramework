<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

trait TimeZone
{
	public static ?string $timezone			= NULL;

	/**
	 *	Sets time zone by given static time zone key.
	 *	Defaults to system time zone.
	 *
	 *	@return		static
	 */
	protected function setTimeZone(): static
	{
		date_default_timezone_set( @date_default_timezone_get() );									//  avoid having no timezone set
		if( !empty( static::$timezone ) )															//  a timezone has be set externally before
			date_default_timezone_set( static::$timezone );											//  set this timezone
		return $this;
	}
}
