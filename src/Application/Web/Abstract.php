<?php
/**
 *	Base application class for MVC web application.
 *
 *	Copyright (c) 2007-2016 Christian Würker (ceusmedia.de)
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
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Base application class for MVC web application.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Application.Web
 *	@extends		CMF_Hydrogen_Application_Abstract
 *	@uses			CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2007-2016 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			Code Documentation
 */
abstract class CMF_Hydrogen_Application_Web_Abstract extends CMF_Hydrogen_Application_Abstract{

	protected $components			= array();

	protected function logOnComplete()
	{
		$responseLength	= $this->env->getResponse()->getLength();
		$responseTime	= $this->env->getClock()->stop( 6, 0 );
		// ...
	}

	protected function reportMissingModules( $modules ){
		$config	= $this->env->getConfig();
		if( !$config->get( 'app.setup.url' ) )
			print( 'Module(s) missing: <ul><li>'.join( '</li><li>', $modules ).'</li></ul>' );

		$instanceId	= $config->get( 'app.setup.instanceId' );
		$baseUrl	= $config->get( 'app.setup.url' );
		$baseUrl	.= 'admin/module/installer/view/';
		$list	= array();
		foreach( $modules as $moduleId ){
			$url	= $baseUrl.$moduleId.'?selectInstanceId='.$instanceId;
			$list[]	= '<li><a href="'.$url.'">'.$moduleId.'</a></li>';
		}
		$list	= '<ul>'.join( $list ).'</ul>';
		print( 'Module(s) missing: '.$list );
	}

	/**
	 *	Sets collacted View Components for Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function setViewComponents( $components = array() )
	{
		foreach( $components as $key => $component )
		{
			if( !array_key_exists( $key, $this->components ) )
				$this->components[$key]	= $component;

		}
	}

	/**
	 *	Collates View Components and puts out Master View.
	 *	@access		protected
	 *	@return		void
	 */
	protected function view( $templateFile = "master.php" )
	{
		$view	= new CMF_Hydrogen_View( $this->env );
		return $view->loadTemplateFile( $templateFile, $this->components );
	}
}
?>
