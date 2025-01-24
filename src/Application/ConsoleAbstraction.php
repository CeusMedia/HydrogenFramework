<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Application class for a console program.
 *
 *	Copyright (c) 2014-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2014-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Application;

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\ApplicationInterface;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Console as ConsoleEnvironment;
use ReflectionException;

/**
 *	Application class for a console program.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
abstract class ConsoleAbstraction implements ApplicationInterface
{
	/**	@var		string						$classEnvironment		Class Name of Application Environment to build */
	public static string $classEnvironment		= ConsoleEnvironment::class;

	/**	@var		array						$modulesNeeded */
	public static array $modulesNeeded			= [];

	/**	@var		ConsoleEnvironment			$env					Application Environment Object */
	protected ConsoleEnvironment $env;

	/**
	 *	@param		Environment|null $env
	 *	@throws		ReflectionException
	 */
	public function __construct( ?Environment $env = NULL )
	{
		/** @var ConsoleEnvironment $env */
		$env ??= ObjectFactory::createObject( static::$classEnvironment );

		$this->env	= $env;

		if( static::$modulesNeeded )																//  needed modules are defined
			$this->checkNeededModules();															//  check for missing modules

		if( $this->env->getConfig()->get( 'system.compat.oldCommon', FALSE ) )			//  look into config for compat flag
			require_once 'vendor/ceus-media/common/src/compat8.php';								//  ... for CeusMedia::Common 0.8.x without namespaces

//		$this->env->set( 'request', new Console_Command_ArgumentParser() );
	}

	/**
	 *	General main application method.
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
	 *	Display report of missing modules.
	 *	This method can be customized in applications, see CMF_Hydrogen_Application_Web_Abstract.
	 *	@access		protected
	 *	@param		array		$modules		List of module IDs
	 *	@return		void
	 */
	protected function reportMissingModules( array $modules ): void
	{
		print( 'Missing modules: '.join( ', ', $modules ) );
	}
}
