<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Base application class for MVC web application.
 *
 *	Copyright (c) 2007-2024 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Application;

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\ApplicationInterface;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;
use ReflectionException;

/**
 *	Base application class for MVC web application.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2024 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
abstract class WebAbstraction implements ApplicationInterface
{
	/**	@var	string				$classEnvironment		Class Name of Application Environment to build */
	public static string $classEnvironment					= WebEnvironment::class;

	public static array $modulesNeeded						= [];

	protected array $components			= [];

	/**	@var	WebEnvironment		$env					Application Environment Object */
	protected WebEnvironment $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment|NULL		$env				Framework Environment
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __construct( ?Environment $env = NULL )
	{
		if( NULL === $env ){
			$env	= ObjectFactory::createObject( static::$classEnvironment );
		}

		/** @var WebEnvironment $env */
		$this->env	= $env;

		if( static::$modulesNeeded )																//  needed modules are defined
			$this->checkNeededModules();															//  check for missing modules


		if( $this->env->getConfig()->get( 'system.compat.oldCommon', FALSE ) )			//  look into config for compat flag
			require_once 'vendor/ceus-media/common/src/compat8.php';								//  ... for CeusMedia::Common 0.8.x without namespaces
	}

	/**
	 *	Returns the environment, set or created on construction.
	 *	@return		Environment		Environment, set or created on construction
	 */
	public function getEnvironment() : Environment
	{
		return $this->env;
	}

	/**
	 *	@abstract
	 *	@access		public
	 *	@return		int|NULL
	 */
	abstract public function run(): ?int;

	//  --  PROTECTED  --  //

	/**
	 *	Finds missing modules if needed modules are defined.
	 *	Having such, the application will quit with a report.
	 *	@access		protected
	 *	@return		void
	 */
	protected function checkNeededModules(): void
	{
		$modulesGot	= array_keys( $this->env->getModules()->getAll() );								//  get installed modules
		$missing	= array_diff( static::$modulesNeeded, $modulesGot );							//  find missing modules
		if( $missing ){																				//  there are missing modules
			$this->reportMissingModules( $missing );												//  report missing modules to screen
			exit;																					//  quit execution
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function logOnComplete(): void
	{
		$captain	= $this->env->getCaptain();
		$data		= [
			'response'	=> $this->env->getResponse(),
			'microtime'	=> $this->env->getRuntime()->get( 6, 0 ),
			// ...
		];
		$captain->callHook( 'App', 'logOnComplete', $this, $data );
		// ...
	}

	/**
	 *	@param		string		$default		Master template file, default: master.php
	 *	@param		string		$hookEvent		default: getMasterTemplate
	 *	@return		string
	 */
	protected function realizeMasterOrErrorTemplateFile( string $default, string $hookEvent ): string
	{
		$payload	= ['templateFile' => ''];
		$this->env->getCaptain()->callHook( 'App', $hookEvent, $this, $payload );
		return match( $payload['templateFile'] ){
			'','default','inherit'	=> $default,
			'theme'					=> $this->env->getPage()->getThemePath().$default,
			default					=> $payload['templateFile'],
		};
	}

	/**
	 *	Display report of missing modules as HTML.
	 *	This method can be customized in applications.
	 *	@access		protected
	 *	@param		array		$modules		List of module IDs
	 *	@return		void
	 */
	protected function reportMissingModules( array $modules ): void
	{
		$config	= $this->env->getConfig();
		if( !$config->get( 'app.setup.url' ) )
			print( 'Module(s) missing: <ul><li>'.join( '</li><li>', $modules ).'</li></ul>' );

		$instanceId	= $config->get( 'app.setup.instanceId' );
		$baseUrl	= $config->get( 'app.setup.url' );
		$baseUrl	.= 'admin/module/installer/view/';
		$list	= [];
		foreach( $modules as $moduleId ){
			$url	= $baseUrl.$moduleId.'?selectInstanceId='.$instanceId;
			$list[]	= '<li><a href="'.$url.'">'.$moduleId.'</a></li>';
		}
		$list	= '<ul>'.join( $list ).'</ul>';
		print( 'Module(s) missing: '.$list );
	}

	/**
	 *	Sets collected View Components for Master View.
	 *	@access		protected
	 *	@param		array		$components
	 *	@return		self
	 */
	protected function setViewComponents( array $components = [] ): self
	{
		foreach( $components as $key => $component ){
			if( !array_key_exists( $key, $this->components ) )
				$this->components[$key]	= $component;
		}
		return $this;
	}

	/**
	 *	Collates View Components and puts out Master View.
	 *	@access		protected
	 *	@param		string		$templateFile		Master template file, default: master.php
	 *	@param		string		$hookEvent			default: getMasterTemplate
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function view( string $templateFile = 'master.php', string $hookEvent = 'getMasterTemplate' ): string
	{
		$templateFile	= $this->realizeMasterOrErrorTemplateFile( $templateFile, $hookEvent );

		$view	= new View( $this->env );
		return $view->loadTemplateFile( $templateFile, $this->components );
	}
}
