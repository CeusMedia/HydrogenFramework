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
		$json	= json_encode( $content, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR );
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
		$json		= json_encode( $content, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR );
		return FileWriter::save( $this->path.$fileName, $json );
	}
}
