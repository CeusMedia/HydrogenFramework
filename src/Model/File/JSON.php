<?php
namespace CeusMedia\HydrogenFramework\Model\File;

use CeusMedia\Common\ADT\JSON\Pretty as JsonPretty;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;

class JSON extends Abstraction
{
	/**
	 *	@param		string		$fileName
	 *	@param		mixed		$content
	 *	@return		int
	 */
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

	/**
	 *	@param		string		$fileName
	 *	@return		bool
	 */
	public function delete( string $fileName ): bool
	{
		return FileWriter::delete( $this->path.$fileName );
	}

	/**
	 *	@param		string		$fileName
	 *	@return		bool
	 */
	public function exists( string $fileName ): bool
	{
		return file_exists( $this->path.$fileName );
	}

	/**
	 *	@param		string		$fileName
	 *	@return		array|object
	 */
	public function read( string $fileName )
	{
		return JsonFileReader::load( $this->path.$fileName );
	}

	/**
	 *	@param		string		$fileName
	 *	@param		mixed		$content
	 *	@return		int
	 */
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
