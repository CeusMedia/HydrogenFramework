<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace CeusMedia\HydrogenFramework\Model\File;

use CeusMedia\Common\ADT\JSON\Pretty as JsonPretty;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;

class JSON extends Abstraction
{
	/**
	 *	@param		string		$fileName
	 *	@param		mixed		$content
	 *	@return		int|FALSE
	 */
	public function create( string $fileName, mixed $content ): int|FALSE
	{
		$json	= json_encode( $content, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR );
		$bytes	= FileWriter::save( $this->path.$fileName, $json );
		return is_int( $bytes ) ? $bytes : FALSE;
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
	 *	@return		int|FALSE
	 */
	public function update( string $fileName, mixed $content ): int|FALSE
	{
		$current	= $this->read( $fileName );
		$json		= json_encode( $content, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR );
		$bytes		= FileWriter::save( $this->path.$fileName, $json );
		return is_int( $bytes ) ? $bytes : FALSE;
	}
}
