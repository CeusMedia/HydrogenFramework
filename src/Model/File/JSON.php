<?php
class CMF_Hydrogen_Model_File_JSON extends CMF_Hydrogen_Model_File_Abstract
{
	public function create( string $fileName, $content ): int
	{
//		@todo enable this line after abstract file model has env support
//		if( $this->env->getPhp()->version->isAtLeast( '5.4.0' ) )
		if( version_compare( phpversion(), '5.4.0' ) >= 0 )
			$json		= json_encode( $content, JSON_PRETTY_PRINT );
		else
			$json		= ADT_JSON_Formater::format( json_encode( $content ) );
		return \FS_File_Writer::save( $this->path.$fileName, $json );
	}

	public function delete( string $fileName ): bool
	{
		return \FS_File_Writer::delete( $this->path.$fileName );
	}

	public function exists( string $fileName ): bool
	{
		return file_exists( $this->path.$fileName );
	}

	public function read( string $fileName )
	{
		return \FS_File_JSON_Reader::load( $this->path.$fileName );
	}

	public function update( string $fileName, $content ): int
	{
		$current	= $this->read( $fileName );
//		@todo enable this line after abstract file model has env support
//		if( $this->env->getPhp()->version->isAtLeast( '5.4.0' ) )
		if( version_compare( phpversion(), '5.4.0' ) >= 0 )
			$json		= json_encode( $content, JSON_PRETTY_PRINT );
		else
			$json		= ADT_JSON_Formater::format( json_encode( $content ) );
		if( $json == $current )
			return FALSE;
		return \FS_File_Writer::save( $this->path.$fileName, $json );
	}
}
