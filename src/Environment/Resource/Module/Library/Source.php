<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 *	Handler for module source library. Can read local folder or HTTP resource.
 *
 *	Copyright (c) 2012-2022 Christian Würker (ceusmedia.de)
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
 *	@copyright		2012-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Module\Library;

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RecursiveNameFilter as RecursiveFileIndex;
use CeusMedia\Common\Net\HTTP\Header\Section;
use CeusMedia\Common\Net\HTTP\Reader as HttpReader;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\HydrogenFramework\Environment as Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface as LibraryInterface;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Abstraction as AbstractLibrary;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Reader as ModuleReader;
use Psr\SimpleCache\InvalidArgumentException;

use Exception;
use RuntimeException;

/**
 *	Handler for module source library.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
class Source extends AbstractLibrary implements LibraryInterface
{
	protected $env;
	protected $modules		= [];
	protected $source;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment instance
	 *	@param		object			$source			Data object defining source by: {id: ..., type: [folder|http], path: ...}
	 *	@return		void
	 *	@throws		InvalidArgumentException
	 */
	public function __construct( Environment $env, object $source )
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
	 *	@throws		InvalidArgumentException
	 */
	public function scan( bool $useCache = FALSE, bool $forceReload = FALSE ): object
	{
		$cache			= $this->env->getCache();
		$cacheKeySource	= NULL;
		if( $useCache ){
			$cacheKeySource	= 'Sources/'.$this->source->id;
			if( $forceReload )
				$cache->remove( $cacheKeySource );
			if( $cache->has( $cacheKeySource ) ){
				$this->modules	= $cache->get( $cacheKeySource );
				return $this->scanResult = (object) [
					'source'	=> 'cache',
					'count'		=> count( $this->modules ),
				];
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

		$list	= [];
		$index	= new RecursiveFileIndex( $this->source->path, 'module.xml' );
		$this->env->getRuntime()->reach( 'Hydrogen: Environment_Resource_Module_Library_Source::scanFolder: init' );
		foreach( $index as $entry ){
			if( preg_match( "@/templates$@", $entry->getPath() ) )
				continue;
			$id		= preg_replace( '@^'.$this->source->path.'@', '', $entry->getPath() );
			$id		= str_replace( '/', '_', $id );

/*			@todo		remove this older module-wise caching
			$cacheKey	= 'Modules/'.$this->source->id.'/'.$id;
			if( $cache->has( $cacheKey ) ){
				$list[$id]	= $cache->get( $cacheKey );
#				$this->env->clock->reach( 'Hydrogen: Environment_Resource_Module_Library_Source::scanFolder: Module #'.$id.':cache' );
				continue;
			}*/
			$icon	= $entry->getPath().'/icon';
			$filePath	= $entry->getPathname();
			if( !is_readable( $filePath ) )
				$this->env->getMessenger()->noteFailure( 'Module file "'.$filePath.'" is not readable.' );
			else{
				try{
					$module	= ModuleReader::load( $filePath, $id );
					$module->path				= $entry->getPath();
					$module->source				= $this->source->id;
					$module->versionAvailable	= $module->version;
					$module->isActive			= TRUE;
					if( isset( $module->config['active'] ) )
						$module->isActive		= $module->config['active']->value;
					$module->icon	= NULL;
					if( file_exists( $icon.'.png' ) )
						$module->icon	= 'data:image/png;base64,'.base64_encode( FileReader::load( $icon.'.png' ) );
					else if( file_exists( $icon.'.ico' ) )
						$module->icon	= 'data:image/x-icon;base64,'.base64_encode( FileReader::load( $icon.'.ico' ) );
					$list[$id]	= $module;
				}
				catch( Exception $e ){
					$this->env->getMessenger()->noteFailure( 'XML of available Module "'.$id.'" is broken ('.$e->getMessage().').' );
				}
//				if( $cache )
//					$cache->set( $cacheKey, $module );
			}
#			$this->env->clock->reach( 'Hydrogen: Environment_Resource_Module_Library_Source::scanFolder: Module #'.$id.':file' );
		}
		ksort( $list );
		return $list;
	}

	protected function getModulesFromHttp(): array
	{
		$host		= parse_url( $this->source->path, PHP_URL_HOST );
		$path		= parse_url( $this->source->path, PHP_URL_PATH );
		$reader		= new HttpReader();
		$headers	= Section::getInstance()->addFieldPair( 'Accept', 'application/json' );
		$response	= $reader->get( $host.$path.'?do=list', $headers );
		$status		= $reader->getCurlInfo( CURLINFO_HTTP_CODE );

		if( 200 !== $status )																//  @todo		extend by more HTTP codes, like 30X
			throw new RuntimeException( 'Source URL "'.$this->source->path.'" is not existing (Code '.$status.')' );
		if( $reader->getResponseHeader( 'Content-Type' ) !== 'application/json' )
			throw new RuntimeException( 'Source did not return JSON data' );
		$modules	= json_decode( $response->getBody(), JSON_THROW_ON_ERROR );
		foreach( $modules as $module ){
			$module->source				= $this->source->id;
			$module->path				= $this->source->path.str_replace( '_', '/', $module->id );
			$module->versionAvailable	= $module->version;

			$icon	= $module->path.'/icon';
			try{
				$content		= NetReader::readUrl( $icon.'.png' );
				$module->icon	= 'data:image/png;base64,'.base64_encode( $content );
			}
			catch( Exception $e ){}
			try{
				$content		= NetReader::readUrl( $icon.'.ico' );
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
