<?php
/**
 *	Handler for module source library. Can read local folder or HTTP resource.
 *
 *	Copyright (c) 2012-2016 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Handler for module source library.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@implements		CMF_Hydrogen_Environment_Resource_Module_Library
 *	@uses			CMF_Hydrogen_Environment_Resource_Module_Reader
 *	@uses			FS_File_RecursiveNameFilter
 *	@uses			Net_HTTP_Request_Sender
 *	@uses			Net_Reader
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 *	@todo			Finish by usind CMM::SEA
 */
class CMF_Hydrogen_Environment_Resource_Module_Library_Source implements CMF_Hydrogen_Environment_Resource_Module_Library{

	protected $env;
	protected $modules		= array();
	protected $source;

	public function __construct( CMF_Hydrogen_Environment $env, $source ){
		$this->env		= $env;
		$this->source	= $source;
		$this->scan();
	}

	public function get( $moduleId ){
		if( $this->isInstalled( $moduleId ) )
			return $this->modulesInstalled[$moduleId];
		throw new RuntimeException( 'Module "'.$moduleId.'" is not installed' );
	}

	public function getAll(){
		return $this->modules;
	}

	public function has( $moduleId ){
		return array_key_exists( $moduleId, $this->modules );
	}

	protected function scanFolder(){
		if( !file_exists( $this->source->path ) )
			throw new RuntimeException( 'Source path "'.$this->source->path.'" is not existing' );
#		@todo activate if source handling is implemented
#		if( !file_exists( $this->source->path.'source.xml' ) )
#			throw new RuntimeException( 'Source XML "'.$this->source->path.'source.xml" is not existing' );
		$cache			= $this->env->getCache();
		$cacheKeySource	= 'Sources/'.$this->source->id;
		if( $cache->has( $cacheKeySource ) ){
			$this->modules	= $cache->get( $cacheKeySource );
			return;
		}

		$list	= array();
		$index	= new FS_File_RecursiveNameFilter( $this->source->path, 'module.xml' );
		$this->env->clock->profiler->tick( 'CMFR_Library_Source::scanFolder: init' );
		foreach( $index as $entry ){
			if( preg_match( "@/templates$@", $entry->getPath() ) )
				continue;
			$id		= preg_replace( '@^'.$this->source->path.'@', '', $entry->getPath() );
			$id		= str_replace( '/', '_', $id );

			$cacheKey	= 'Modules/'.$this->source->id.'/'.$id;
			if( $cache->has( $cacheKey ) ){
				$list[$id]	= $cache->get( $cacheKey );
#				$this->env->clock->profiler->tick( 'CMF_Library_Source::scanFolder: Module #'.$id.':cache' );
				continue;
			}
			$icon	= $entry->getPath().'/icon';
			$filePath	= $entry->getPathname();
			if( !is_readable( $filePath ) )
				$this->env->messenger->noteFailure( 'Module file "'.$filePath.'" is not readable.' );
			else{
				try{
					$obj	= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $filePath, $id );
					$obj->path		= $entry->getPath();
					$obj->file		= $filePath;
					$obj->source	= $this->source->id;
					$obj->_source	= $this->source;
					$obj->id		= $id;
					$obj->versionAvailable	= $obj->version;
					$obj->icon	= NULL;
					if( file_exists( $icon.'.png' ) )
						$obj->icon	= 'data:image/png;base64,'.base64_encode( FS_File_Reader::load( $icon.'.png' ) );
					else if( file_exists( $icon.'.ico' ) )
						$obj->icon	= 'data:image/x-icon;base64,'.base64_encode( FS_File_Reader::load( $icon.'.ico' ) );
					$list[$id]	= $obj;
				}
				catch( Exception $e ){
					$this->env->messenger->noteFailure( 'XML of available Module "'.$id.'" is broken ('.$e->getMessage().').' );
				}
				if( $cache )
					$cache->set( $cacheKey, $obj );
			}
#			$this->env->clock->profiler->tick( 'CMFR_Library_Source::scanFolder: Module #'.$id.':file' );
		}
		ksort( $list );
		$this->modules	= $list;
		$cache->set( $cacheKeySource, $list );
	}

	protected function scanHttp(){
		$host		= parse_url( $this->source->path, PHP_URL_HOST );
		$path		= parse_url( $this->source->path, PHP_URL_PATH );
		$request	= new Net_HTTP_Request_Sender( $host, $path.'?do=list' );

		$request->addHeaderPair( 'Accept', 'application/json' );
		$response	= $request->send();
		if( !in_array( $response->getStatus(), array( 200 ) ) )										//  @todo		extend by more HTTP codes, like 30X
			throw new RuntimeException( 'Source URL "'.$this->source->path.'" is not existing (Code '.$response->getStatus().')' );
		$mimeType	= $response->getHeader( 'Content-Type', TRUE )->getValue();
		if( $mimeType != 'application/json' )
			throw new RuntimeException( 'Source did not return JSON data' );
		$modules	= @json_decode( $response->getBody() );
		if( !$modules )
			throw new RuntimeException( 'Source return invalid JSON data' );
		foreach( $modules as $module ){
			$module->source				= $this->source->id;
			$module->path				= $this->source->path.str_replace( '_', '/', $module->id );
			$module->versionAvailable	= $module->version;

			$icon	= $module->path.'/icon';
			try{
				$content = Net_Reader::readUrl( $icon.'.png' );
				$module->icon	= 'data:image/png;base64,'.base64_encode( $content );
			}
			catch( Exception $e ){}
			try{
				$content		= Net_Reader::readUrl( $icon.'.ico' );
				$module->icon	= 'data:image/png;base64,'.base64_encode( $content );
			}
			catch( Exception $e ){}

#			print_m( $modules );
#			die;

/*				$obj->file		= $entry->getPathname();
				$obj->icon	= NULL;
*/
		}
		$this->modules	= $modules;
	}

	public function scan(){
		switch( $this->source->type ){
			case 'folder':
				$this->scanFolder();
				break;
			case 'http':
				$this->scanHttp();
				break;
			default:
				throw new RuntimeException( 'Source type "'.$this->source->type.'" is not supported' );
		}
	}
}
?>
