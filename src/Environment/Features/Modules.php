<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;
use CeusMedia\HydrogenFramework\Environment\Features\ConfigByIni as ConfigurationFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Config as ModuleConfig;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface;
use ReflectionException;

trait Modules
{
	use ConfigurationFeature;
	use RuntimeFeature;

	/**	@var	LibraryInterface			$modules		Handler for installed modules */
	protected LibraryInterface $modules;

	/**
	 *	Returns initially set up handler for (usually local) module library.
	 *	@access		public
	 *	@return		LibraryInterface
	 */
	public function getModules(): LibraryInterface
	{
		return $this->modules;
	}

	/**
	 *	@param		string		$moduleId
	 *	@return		bool
	 */
	public function hasModule( string $moduleId ): bool
	{
		return $this->hasModules() && $this->modules->has( $moduleId );
	}

	/**
	 *	@return		bool
	 */
	public function hasModules(): bool
	{
		return 0 !== $this->modules->count();
	}

	/**
	 *	Sets up a handler for (usually local) module library.
	 *	@access		protected
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		FileNotExistingException	if strict and file is not existing or given path is not a file
	 *	@throws		IoException					if strict and file is not readable
	 *	@todo		remove support for base_config::module.acl.public
	 */
	protected function initModules(): static
	{
		$this->runtime->reach( 'env: initModules: start', 'Started setup of modules.' );
		$public	= [];
		if( strlen( trim( $this->config->get( 'module.acl.public', '' ) ) ) ){
			Deprecation::getInstance()
				->setErrorVersion( '0.8.7.2' )
				->setExceptionVersion( '0.8.9' )
				->message( 'Using config::module.acl.public is deprecated. Use ACL instead!' );
			$public	= explode( ',', $this->config->get( 'module.acl.public' ) );					//  get current public link list
		}

		$this->modules	= new LocalModuleLibraryResource( $this );
		$this->modules->stripFeatures( [
			'sql',
			'versionLog',
			'companies',
			'authors',
			'licenses',
			'price',
			'file',
			'uri',
			'category',
			'description',
		] );

		foreach( $this->modules->getAll() as $moduleId => $module ){								//  iterate all local app modules
			$prefix	= 'module.'.strtolower( $moduleId );											//  build config key prefix of module
			$this->config->set( $prefix, TRUE );													//  enable module in configuration
			foreach( $module->config as $key => $value ){											//  iterate module configuration pairs
				if( $value instanceof ModuleConfig && NULL !== $value->type ){						//  is module config definition object
					/** @var  $value */
					@settype( $value->value, $value->type );										//  cast value by set type
					$this->config->set( $prefix.'.'.$key, $value->value );							//  set configuration pair
				}
				else																				//  legacy @todo remove
					$this->config->set( $prefix.'.'.$key, $value );									//
			}

			foreach( $module->links as $link ){														//  iterate module links
				if( $link->access == "public" ){													//  link is public
					$link->path	= $link->path ?: 'index/index';
					$path		= str_replace( '/', '_', $link->path );									//  get link path
					if( !in_array( $path, $public ) )												//  link is not in public link list
						$public[]	= $path;														//  add link to public link list
				}
			}
		}
		if( !( $this instanceof RemoteEnvironment ) )
			$this->modules->callHook( 'Env', 'initModules', $this );								//  call related module event hooks
		$this->config->set( 'module.acl.public', implode( ',', array_unique( $public ) ) );			//  save public link list
		$this->runtime->reach( 'env: initModules: end', 'Finished setup of modules.' );
		return $this;
	}
}
