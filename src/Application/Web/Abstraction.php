<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Base application class for MVC web application.
 *
 *	Copyright (c) 2007-2022 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Application\Web;

use CeusMedia\HydrogenFramework\Application\Abstraction as ApplicationAbstraction;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;
use ReflectionException;

/**
 *	Base application class for MVC web application.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2022 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 *	@deprecated		extend WebAbstraction and implement interface instead
 */
abstract class Abstraction extends ApplicationAbstraction
{
	protected array $components			= [];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment|NULL		$env				Framework Environment
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __construct( ?WebEnvironment $env = NULL )
	{
		parent::__construct( $env );
	}

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
	 *	Display report of missing modules as HTML.
	 *	This method can be customized in applications.
	 *	@access		protected
	 *	@param		array		$modules		List of module IDs
	 *	@return		void
	 */
	protected function reportMissingModules( array $modules )
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
	 *	@return		string
	 */
	protected function view( string $templateFile = "master.php" ): string
	{
		$payload	= ['templateFile' => ''];
		$this->env->getCaptain()->callHook( 'App', 'getMasterTemplate', $this, $payload );
		$masterTemplate	= $payload['templateFile'];

		switch( $masterTemplate ){
			case '':
			case 'default':
			case 'inherit':
				$templateFile	= 'master.php';
				break;
			case 'theme':
				$pathTheme		= $this->env->getPage()->getThemePath();
				$templateFile	= $pathTheme.'master.php';
				break;
			default:
				$templateFile	= $masterTemplate;
		}

		$view	= new View( $this->env );
		return $view->loadTemplateFile( $templateFile, $this->components );
	}
}
