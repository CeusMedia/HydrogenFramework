<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Module definition.
 *
 *	Copyright (c) 2022 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2022 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Module;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Author;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Company;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Config;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Deprecation;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Files;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Hook;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Job;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Installation;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\License;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Relations;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\SQL;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Version;

/**
 *	Module definition.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2022 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Definition
{
	public string $id;
	public ?string $source				= NULL;
	public string $file;
	public ?string $uri					= NULL;
	public ?string $path				= NULL;
	public string $title;
	public string $category;
	public string $description;
	public bool $isActive				= TRUE;
	public bool $isInstalled			= FALSE;
	public array $frameworks			= [];
//	public string $version;

	/**	@var Version $version */
	public Version $version;

	/**	@var ?Deprecation $deprecation */
	public ?Deprecation $deprecation	= NULL;

	/**	@var array<Company> $companies */
	public array $companies				= [];

	/**	@var array<Author> $authors */
	public array $authors				= [];

	/**	@var array<License> $authors */
	public array $licenses				= [];

	/**	@var Files $files */
	public Files $files;

	/** @var array<Config> $config */
	public array $config				= [];

	/**	@var Relations $relations */
	public Relations $relations;

	/** @var array<string,SQL> $sql */
	public array $sql					= [];

	public array $links					= [];

	/** @var array<string,Hook> $hooks */
	public array $hooks					= [];

	/** @var array<string,Job> $jobs */
	public array $jobs					= [];

	/** @var ?Installation $install */
	public ?Installation $install		= NULL;

	public ?string $price				= NULL;
	public ?string $icon				= NULL;

	protected ?Dictionary $configAsDictionary	= NULL;

	/**
	 *	Constructor.
	 *	@param		string			$id			Module ID
	 *	@param		string			$version	Version of module
	 *	@param		string			$file		Path to XML file holding the module definition
	 *	@param		string|NULL		$uri		Path to module (=folder of module file)
	 */
	public function __construct( string $id, string $version, string $file, ?string $uri = NULL )
	{
		$this->id			= $id;
		$this->file			= $file;
		$this->uri			= $uri ?? ( realpath( $file ) ?: NULL );
		$this->version		= new Version( $version );
		$this->files		= new Files();
		$this->relations	= new Relations();
		$this->install		= new Installation();
	}

	/**
	 *	Returns set config objects as dictionary.
	 *	@access		public
	 *	@return		Dictionary
	 */
	public function getConfigAsDictionary(): Dictionary
	{
		if( NULL === $this->configAsDictionary ){
			$dictionary	= new Dictionary();
			array_walk($this->config, static function( Config $config ) use ( $dictionary ){
				if( NULL === $config->type )
					return;
				@settype( $config->value, $config->type );
				$dictionary->set( $config->key, $config->value );
			} );
			$this->configAsDictionary	= $dictionary;
		}
		return $this->configAsDictionary;
	}
}
