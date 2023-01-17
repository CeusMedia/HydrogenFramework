<?php

use CeusMedia\Common\CLI;
use CeusMedia\Common\FS;
use CeusMedia\Common\FS\Folder;
use CeusMedia\HydrogenFramework\View\Helper\Diff;

class Tool_Migration_Applier
{
	protected array $modifiers	= [];

	protected Folder $folder;

	public function apply(): object
	{
		if( !$this->modifiers )
			throw new RangeException( 'No modifiers set' );
		return $this->handleFolder( $this->folder );
	}

	public function setModifiers( array $modifiers ): self
	{
		$this->modifiers	= $modifiers;
		return $this;
	}

	public function setRootFolder( Folder $folder ): self
	{
		$this->folder	= $folder;
		return $this;
	}

	//  --  PRIVATE  --  //

	private function handleFolder( $folder ): object
	{
//		remark( "FOLDER: ".$folder->getPathName() );
		$nrFiles		= 0;
		$nrFilesChanged	= 0;
		foreach($folder->index( FS::TYPE_FILE ) as $fileName => $file ){
			if( preg_match( '/\.php.2$/', $file->getName() ) )
				unlink( $file->getPathName() );
			if( !preg_match( '/\.php$/', $file->getName() ) )
				continue;
			$nrFiles++;
			$content	= $file->getContent();
			$lines		= preg_split( '/\r?\n/', $content );
			foreach( $this->modifiers as $modifier ){
				$modifierCallback	= array( $modifier[0], $modifier[1] );
				$modifierArguments	= array_slice( $modifier, 2 );
				$callbackArguments	= array_merge( array( $lines ), $modifierArguments );
				$lines	= call_user_func_array( $modifierCallback, $callbackArguments );
			}

			if( $content !== join( PHP_EOL, $lines ) ){
//				FS_File_Writer::saveArray( $file->getPathName().'.2', $lines );
				$nrFilesChanged++;
				CLI::out( "- File: ".$folder->getPathName().$file->getName() );
/*				foreach( $this->diff( preg_split( '/\r?\n/', $content ), $lines ) as $line ){
					if( !empty( $line['d'] ) )
						foreach( $line['d'] as $deletedLine )
							CLI::out( CLI_Color::colorize( $deletedLine, 'white', 'red' ) );
					if( !empty( $line['i'] ) )
						foreach( $line['i'] as $insertedLine )
							CLI::out( CLI_Color::colorize( $insertedLine, 'white', 'green' ) );
				}*/
			}
			$file->setContent( join( PHP_EOL, $lines ) );
		}

		foreach( $folder->index( FS::TYPE_FOLDER ) as $folderName => $folder ){
			$stats	= $this->handleFolder( $folder );
			$nrFiles		+= $stats->nrFiles;
			$nrFilesChanged	+= $stats->nrFilesChanged;
		}
		return (object) [
			'nrFiles'			=> $nrFiles,
			'nrFilesChanged'	=> $nrFilesChanged,
		];
	}

	private function diff( array $old, array $new ): array
	{
		return Diff::diff( $old, $new );
	}
}
