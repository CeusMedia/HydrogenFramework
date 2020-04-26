<?php
class Tool_Migration_Modifier
{
	public static function addReturnTypeOfMethods( array $lines, string $returnType, array $methods = array() ): array
	{
		$regExp		= '/(function %s\(\))\s*\{/s';
		$content	= join( PHP_EOL, $lines );
		$original	= $content;
		foreach( $methods as $method )
			$content	= preg_replace(
				sprintf( $regExp, $method ),
				"\\1: ".$returnType."\n\t{",
				$content
			);
		if( $content !== $original )
			$lines	= preg_split( '/\r?\n/', $content );
		return $lines;
	}

	public static function breakCommentsInLines( array $lines ): array
	{
		$nrInserted	= 0;
		$list	= array();
		foreach( $lines as $nr => $line ){
			$matches	= array();
			if( preg_match_all( '@^(\s*)(\S.+)(\s+)(//\s+)(\S.+)$@U', $line, $matches ) ){
				$list[]		= $matches[1][0].$matches[4][0].$matches[5][0];
				$line		= $matches[1][0].$matches[2][0];
			}
			$list[]	= $line;
		}
		return $list;
	}

	public static function clearDocVersionInLines( array $lines ): array
	{
		foreach( $lines as $nr => $line ){
			if( preg_match( '/^\s*\*\s+@version\s+\$Id\$$/', $line ) )
				unset( $lines[$nr] );
		}
		return $lines;
	}

	public static function clearEndingPhpTagInLines( array $lines ): array
	{
		$lines	= array_reverse( $lines );
		foreach( $lines as $nr => $line ){
			if( in_array( trim( $line ), array( "?>", "" ), TRUE ) ){
				unset( $lines[$nr] );
			}
			else
				break;
		}
		array_unshift( $lines, '' );
		return array_reverse( $lines );
	}


	public static function removeIndentsInEmptyLines( array $lines ): array
	{
		foreach( $lines as $nr => $line ){
			if( preg_match( '/^\s+$/', $line ) )
				$lines[$nr]	= '';
		}
		return $lines;
	}

	public static function updateCopyrightYearInLines( array $lines, string $yearFromQuotedRegExp, string $yearTo ): array
	{
		foreach( $lines as $nr => $line ){
			$lines[$nr]	= preg_replace( '/(\s+)('.$yearFromQuotedRegExp.')(\s+)/', "\\1\\2-".$yearTo."\\3", $line );
			$lines[$nr]	= preg_replace( '/-'.$yearFromQuotedRegExp.'(\s+)/', "-".$yearTo."\\1", $line );
			$lines[$nr]	= preg_replace( '/'.$yearFromQuotedRegExp.'(\s+)/', $yearTo."\\1", $line );
		}
		return $lines;
	}

	/**
	 *	@todo		implement
	 */
	public static function updateLineBreak( array $lines ): array
	{
		return $lines;
	}

	public static function updateTestSetUpAndTearDown( array $lines ): array
	{
		$methods	= array( 'setUp', 'tearDown' );
		return static::addReturnTypeOfMethods( $lines, 'void', $methods );
	}
}
