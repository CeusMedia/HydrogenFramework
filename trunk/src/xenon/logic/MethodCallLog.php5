<?php
class MethodCallLog
{
	private static $path = '';

	public static function setPath( $path )
	{
		self::$path = $path;
	}

	public static function log( $methodName, $cached = FALSE )
	{
		$cached	= ( $cached && StaticCache::$enabled ) ? "--" : "";
		error_log( time()." ".$methodName.$cached."\n", 3, self::$path . "logs/methods.log" );
	}
}
?>