<?php
/*
	Paul's Simple Diff Algorithm v 0.1
	(C) Paul Butler 2007 <http://www.paulbutler.org/>
	May be used and distributed under the zlib/libpng license.

	This code is intended for learning purposes; it was written with short
	code taking priority over performance. It could be used in a practical
	application, but there are a few ways it could be optimized.

	Given two arrays, the function diff will return an array of the changes.
	I won't describe the format of the array, but it will be obvious
	if you use print_r() on the result of a diff on some test data.

	htmlDiff is a wrapper for the diff command, it takes two strings and
	returns the differences in HTML. The tags used are <ins> and <del>,
	which can easily be styled with CSS.
*/
class CMF_Hydrogen_View_Helper_Diff{

	static public function diff( $old, $new ){
		$maxlen	= 0;
		$oldMax	= 0;
		$newMax	= 0;
		foreach( $old as $oldIndex => $oldValue ){
			$newKeys = array_keys( $new, $oldValue );
			foreach( $newKeys as $newIndex ){
				$matrix[$oldIndex][$newIndex] = 1;
				if( isset( $matrix[$oldIndex - 1][$newIndex - 1] ) )
					$matrix[$oldIndex][$newIndex] = $matrix[$oldIndex - 1][$newIndex - 1] + 1;
				if($matrix[$oldIndex][$newIndex] > $maxlen){
					$maxlen	= $matrix[$oldIndex][$newIndex];
					$oldMax	= $oldIndex + 1 - $maxlen;
					$newMax	= $newIndex + 1 - $maxlen;
				}
			}
		}
		if( $maxlen == 0 )
			return array( array( 'd' => $old, 'i' => $new ) );
		return array_merge(
			self::diff( array_slice( $old, 0, $oldMax ), array_slice( $new, 0, $newMax ) ),
			array_slice( $new, $newMax, $maxlen ),
			self::diff( array_slice( $old, $oldMax + $maxlen ), array_slice( $new, $newMax + $maxlen ) )
		);
	}

	static public function htmlDiff( $old, $new ){
		$ret	= '';
		$diff	= self::diff( explode( ' ', $old ), explode(' ', $new ) );
		foreach( $diff as $k ){
			if( is_array( $k ) ){
				$ret	.= ( !empty( $k['d'] ) ? "<del>".implode( ' ', $k['d'] )."</del> ":'' );
				$ret	.= ( !empty( $k['i'] ) ? "<ins>".implode( ' ', $k['i'] )."</ins> ":'' );
			}
			else $ret .= $k . ' ';
		}
		return $ret;
	}
}
