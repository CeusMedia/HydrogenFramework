<?php
namespace CeusMedia\HydrogenFramework\Model\File;

use CeusMedia\Common\ADT\JSON\Pretty as JsonPretty;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;

class JSON extends Abstraction
{
	public function create( string $fileName, $content ): int
	{
//		@todo enable this line after abstract file model has env support
//		if( $this->env->getPhp()->version->isAtLeast( '5.4.0' ) )
		if( version_compare( phpversion(), '5.4.0' ) >= 0 )
			$json		= json_encode( $content, JSON_PRETTY_PRINT );
		else
			$json		= JsonPretty::print( json_encode( $content ) );
		return FileWriter::save( $this->path.$fileName, $json );
	}

	public function delete( string $fileName ): bool
	{
		return FileWriter::delete( $this->path.$fileName );
	}

	public function exists( string $fileName ): bool
	{
		return file_exists( $this->path.$fileName );
	}

	public function read( string $fileName )
	{
		return JsonFileReader::load( $this->path.$fileName );
	}

	public function update( string $fileName, $content ): int
	{
		$current	= $this->read( $fileName );
//		@todo enable this line after abstract file model has env support
//		if( $this->env->getPhp()->version->isAtLeast( '5.4.0' ) )
		if( version_compare( phpversion(), '5.4.0' ) >= 0 )
			$json		= json_encode( $content, JSON_PRETTY_PRINT );
		else
			$json		= JsonPretty::print( json_encode( $content ) );
		if( $json == $current )
			return 0;
		return FileWriter::save( $this->path.$fileName, $json );
	}
}
