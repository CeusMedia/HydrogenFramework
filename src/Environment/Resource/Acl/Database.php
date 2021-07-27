<?php
/**
 *	Setup for access control list using a Database.
 *
 *	Copyright (c) 2010-2021 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource\Acl;

use CeusMedia\HydrogenFramework\Environment\Resource\Disclosure as DisclosureResource;
use CeusMedia\HydrogenFramework\Deprecation;
use OutOfRangeException;

/**
 *	Setup for access control list using a Database.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@deprecated		use module Resource_Authentication instead
 *	@todo			remove in version 0.9
 */
class Database extends Abstraction
{
	public function index( string $controller = NULL, $roleId = NULL ): array
	{
		$this->markDeprecation();
		if( $roleId === NULL ){
			if( !$this->env->has( 'session' ) )
				return array();
			$roleId	= $this->env->getSession()->get( 'roleId' );
		}
		if( $this->hasFullAccess( $roleId ) ){
			if( !$this->controllerActions )
				$this->scanControllerActions();
			if( $controller === NULL )
				return $this->controllerActions;
			$controller	= strtolower( str_replace( '/', '_', $controller ) );
			if( isset( $this->controllerActions[$controller] ) )
				return $this->controllerActions[$controller];
			return array();
		}
		else{
			$rights	= $this->getRights( $roleId );
			if( $controller === NULL )
				return $rights;
			$controller	= strtolower( str_replace( '/', '_', $controller ) );
			if( isset( $rights[$controller] ) )
				return $rights[$controller];
			return array();
		}
	}

	/**
	 *	Allowes access to a controller action for a role.
	 *	@access		public
	 *	@param		integer		$roleId			Role ID
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		integer
	 *	@todo 		refactor return type to string
	 */
	public function setRight( $roleId, string $controller, string $action )
	{
		$this->markDeprecation();
		if( $this->hasFullAccess( $roleId ) )
			return -1;
		if( $this->hasNoAccess( $roleId ) )
			return -2;
		$data	= array(
			'roleId'		=> $roleId,
			'controller'	=> Model_Role_Right::minifyController( $controller ),
			'action'		=> $action,
			'timestamp'		=> time()
		);
		$model	= new Model_Role_Right( $this->env );
		return $model->add( $data );
	}

	//  --  PROTECTED  --  //

	/**
	 *	Returns all rights of a role.
	 *	@access		protected
	 *	@param		integer		$roleId			Role ID
	 *	@return		array
	 */
	protected function getRights( $roleId ): array
	{
		if( $this->hasFullAccess( $roleId ) )
			return array();
		if( $this->hasNoAccess( $roleId ) )
			return array();
		if( !isset( $this->rights[$roleId] ) ){
			$model	= new Model_Role_Right( $this->env );
			$this->rights[$roleId]	= array();
			foreach( $model->getAllByIndex( 'roleId', $roleId ) as $right ){
				$controller = strtolower( str_replace( '/', '_', $right->controller ) );
				if( !isset( $this->rights[$roleId][$controller] ) )
					$this->rights[$roleId][$controller]	= array();
				$this->rights[$roleId][$controller][]	= $right->action;
			}
		}
		return $this->rights[$roleId];
	}

	/**
	 *	Returns Role.
	 *	@access		protected
	 *	@param		integer		$roleId			Role ID
	 *	@return		array|object
	 */
	protected function getRole( $roleId )
	{
		if( !$roleId )
			return array();
		if( !$this->roles ){
			$model	= new Model_Role( $this->env );
			foreach( $model->getAll() as $role )
				$this->roles[$role->roleId]	= $role;
		}
		if( !isset( $this->roles[$roleId] ) )
			throw new OutOfRangeException( 'Role with ID '.$roleId.' is not existing' );
		return $this->roles[$roleId];
	}

/*	protected function listActions( $controller ){
		$model	= new Model_Role_Right( $this->env );
		$list	= array();
		foreach( $model->getAllByIndex( 'controller', $controller ) as $action )
			if( !in_array( $action, $list ) )
				$list[]	= $action;
		return $list;
	}*/

	protected function listControllers(): array
	{
		$model	= new Model_Role_Right( $this->env );
		$list	= array();
		foreach( $model->getAll( array(), array( 'controller' => 'ASC' ) ) as $controller )
			if( !in_array( $controller, $list ) )
				$list[]	= $controller;
		return $list;
	}

	/**
	 *	Scan controller classes for actions using disclosure.
	 *	@access		protected
	 *	@return		void
	 */
	protected function scanControllerActions()
	{
		$disclosure	= new DisclosureResource();
		$classes	= $disclosure->reflect( 'classes/Controller/' );
		foreach( $classes as $className => $classData ){
			$className	= strtolower( str_replace( '/', '_', $className ) );
			$this->controllerActions[$className]	= array();
			foreach( $classData->methods as $methodName => $methodData )
				$this->controllerActions[$className][]	= $methodName;
		}
	}

	protected function markDeprecation()
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( 'Use module Resource_Authentication instead' );

	}
}
