<?php
class CMF_Hydrogen_Model_File_JSON extends CMF_Hydrogen_Model_File_Abstract{

	public function create( $fileName, $content ){
		if( version_compare( phpversion(), '5.4.0' ) >= 0 )
			$json		= json_encode( $content, JSON_PRETTY_PRINT );
		else
			$json		= ADT_JSON_Formater::format( json_encode( $content ) );
		return \FS_File_Writer::save( $this->path.$fileName, $json );
	}

	public function delete( $fileName ){
		return \FS_File_Writer::remove( $this->path.$fileName );
	}

	public function exists( $fileName ){
		return file_exists( $this->path.$fileName );
	}

	public function read( $fileName ){
		return \FS_File_JSON_Reader::load( $this->path.$fileName );
	}

	public function update( $fileName, $content ){
		$current	= $this->read( $fileName );
		if( version_compare( phpversion(), '5.4.0' ) >= 0 )
			$json		= json_encode( $content, JSON_PRETTY_PRINT );
		else
			$json		= ADT_JSON_Formater::format( json_encode( $content ) );
		if( $json == $current )
			return FALSE;
		return \FS_File_Writer::save( $this->path.$fileName, $json );
	}
}
?>
