<?php
/**
 *	Handler for module source library. Can read local folder or HTTP resource.
 *
 *	Copyright (c) 2012-2020 Christian Würker (ceusmedia.de)
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
 *	@copyright		2012-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Handler for module source library.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@extends		CMF_Hydrogen_Environment_Resource_Module_Library_Abstract
 *	@implements		CMF_Hydrogen_Environment_Resource_Module_Library_Interface
 *	@uses			CMF_Hydrogen_Environment_Resource_Module_Reader
 *	@uses			FS_File_RecursiveNameFilter
 *	@uses			Net_HTTP_Request_Sender
 *	@uses			Net_Reader
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 *	@todo			Finish by using CMM::SEA
 */
class CMF_Hydrogen_Environment_Resource_Module_Library_Source extends CMF_Hydrogen_Environment_Resource_Module_Library_Abstract implements CMF_Hydrogen_Environment_Resource_Module_Library_Interface
{
	protected $env;
	protected $modules		= array();
	protected $source;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment instance
	 *	@param		object						$source			Data object defining source by: {id: ..., type: [folder|http], path: ...}
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env, $source )
	{
		$this->env		= $env;
		$this->source	= $source;
		$this->scan( TRUE );
	}

	/**
	 *	Scan modules of source.
	 *	Should return a data object containing the result source and number of found modules.
	 *	@access	public
	 *	@param		boolean		$useCache		Flag: use cache if available
	 *	@param		boolean		$forceReload	Flag: clear cache beforehand if available
	 *	@return		object		Data object containing the result source and number of found modules
	 */
	public function scan( bool $useCache = FALSE, bool $forceReload = FALSE )
	{
		if( $useCache ){
			$cache			= $this->env->getCache();
			$cacheKeySource	= 'Sources/'.$this->source->id;
			if( $forceReload )
				$cache->remove( $cacheKeySource );
			if( $cache->has( $cacheKeySource ) ){
				$this->modules	= $cache->get( $cacheKeySource );
				return $this->scanResult = (object) array(
					'source'	=> 'cache',
					'count'		=> count( $this->modules ),
				);
			}
		}

		switch( $this->source->type ){
			case 'folder':
				$this->modules	= $this->getModulesFromFolder();
				break;
			case 'http':
				$this->modules	= $this->getModulesFromHttp();
				break;
			default:
				throw new RuntimeException( 'Source type "'.$this->source->type.'" is not supported' );
		}
		if( $useCache )
			$cache->set( $cacheKeySource, $this->modules );
		return $this->scanResult = (object) array(
			'source'	=> $this->source->type,
			'count'		=> count( $this->modules ),
		);
	}

	protected function getModulesFromFolder(): array
	{
		if( !file_exists( $this->source->path ) )
			throw new RuntimeException( 'Source path "'.$this->source->path.'" is not existing' );
#		@todo activate if source handling is implemented
#		if( !file_exists( $this->source->path.'source.xml' ) )
#			throw new RuntimeException( 'Source XML "'.$this->source->path.'source.xml" is not existing' );

		$list	= array();
		$index	= new FS_File_RecursiveNameFilter( $this->source->path, 'module.xml' );
		$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Source::scanFolder: init' );
		foreach( $index as $entry ){
			if( preg_match( "@/templates$@", $entry->getPath() ) )
				continue;
			$id		= preg_replace( '@^'.$this->source->path.'@', '', $entry->getPath() );
			$id		= str_replace( '/', '_', $id );

/*			@todo		remove this older module-wise caching
			$cacheKey	= 'Modules/'.$this->source->id.'/'.$id;
			if( $cache->has( $cacheKey ) ){
				$list[$id]	= $cache->get( $cacheKey );
#				$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Source::scanFolder: Module #'.$id.':cache' );
				continue;
			}*/
			$icon	= $entry->getPath().'/icon';
			$filePath	= $entry->getPathname();
			if( !is_readable( $filePath ) )
				$this->env->messenger->noteFailure( 'Module file "'.$filePath.'" is not readable.' );
			else{
				try{
					$module	= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $filePath, $id );
					$module->path				= $entry->getPath();
					$module->source				= $this->source->id;
					$module->versionAvailable	= $module->version;
					if( isset( $module->config['active'] ) )
						$module->isActive		= $module->config['active']->value;
					$module->icon	= NULL;
					if( file_exists( $icon.'.png' ) )
						$module->icon	= 'data:image/png;base64,'.base64_encode( FS_File_Reader::load( $icon.'.png' ) );
					else if( file_exists( $icon.'.ico' ) )
						$module->icon	= 'data:image/x-icon;base64,'.base64_encode( FS_File_Reader::load( $icon.'.ico' ) );
					$list[$id]	= $module;
				}
				catch( Exception $e ){
					$this->env->messenger->noteFailure( 'XML of available Module "'.$id.'" is broken ('.$e->getMessage().').' );
				}
//				if( $cache )
//					$cache->set( $cacheKey, $module );
			}
#			$this->env->clock->profiler->tick( 'Hydrogen: Environment_Resource_Module_Library_Source::scanFolder: Module #'.$id.':file' );
		}
		ksort( $list );
		return $list;
	}

	protected function getModulesFromHttp(): array
	{
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
				$content		= Net_Reader::readUrl( $icon.'.png' );
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
		return $modules;
	}
}
