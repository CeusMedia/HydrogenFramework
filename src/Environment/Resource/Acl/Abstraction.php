<?php /** @noinspection PhpUnused */

/**
 *	Abstract access control list resource.
 *
 *	Copyright (c) 2011-2025 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Environment\Resource\Acl;

use CeusMedia\HydrogenFramework\Environment as Environment;
use InvalidArgumentException;

/**
 *	Abstract access control list resource.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Acl
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class Abstraction
{
	protected Environment $env;

	public const ROLE_ACCESS_NONE		= 0;
	public const ROLE_ACCESS_ACL		= 1;
	public const ROLE_ACCESS_FULL		= 128;

	protected array $controllerActions	= [];
	protected array $rights				= [];
	protected array $roles				= [];
	/*	@var		$publicLinks				Map of links with public access */
	protected array $linksPublic		= [];
	protected array $linksPublicInside	= [];
	protected array $linksPublicOutside	= [];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env	Environment Object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	Indicates whether access to a controller action is allowed for role of current user.
	 *	Needs session resource. Works only if user is logged and assigned role is existing.
	 *	@access		public
	 *	@param		string		$controller		Name of controller
	 *	@param		string		$action			Name of action
	 *	@return		bool
	 */
	public function has( string $controller = 'index', string $action = 'index' ): bool
	{
		if( !$this->env->has( 'session' ) )
			return FALSE;
		$roleId	= $this->env->getSession()->get( 'auth_role_id', '' );
		$right	= $this->hasRight( (string) $roleId, $controller, $action );
//		remark( 'Controller: '.$controller.' | Action: '.$action.' | Right: '.$right );
		return $right > 0;
	}

	/**
	 *	Return list controller actions or matrix of controllers and actions of role.
	 *	@abstract
	 *	@public
	 *	@param		string|NULL		$controller		Controller to list actions for, otherwise return matrix
	 *	@param		string|NULL		$roleId			Specified role, otherwise current role
	 *	@return		array							List of actions or matrix of controllers and actions
	 */
	abstract public function index( ?string $controller = NULL, ?string $roleId = NULL ): array;

	public function getPublicInsideLinks(): array
	{
		return $this->linksPublicInside;
	}

	public function getPublicOutsideLinks(): array
	{
		return $this->linksPublicOutside;
	}

	public function getPublicLinks(): array
	{
		return $this->linksPublic;
	}

	/**
	 *	Indicates whether a role is system operator and has access to all controller actions.
	 *	@access		public
	 *	@param		string		$roleId			Role ID
	 *	@return		boolean
	 */
	public function hasFullAccess( string $roleId ): bool
	{
		if( !$roleId )
			return FALSE;
		$role	= $this->getRole( $roleId );
		if( !$role )
			throw new InvalidArgumentException( 'Role with ID '.$roleId.' is not existing' );
		return isset( $role->access ) && $role->access == self::ROLE_ACCESS_FULL;
	}

	/**
	 *	Indicates whether a role has no access as all.
	 *	@access		public
	 *	@param		string		$roleId			Role ID
	 *	@return		boolean
	 */
	public function hasNoAccess( string $roleId ): bool
	{
		if( !$roleId )
			return FALSE;
		$role	= $this->getRole( $roleId );
		if( !$role )
			throw new InvalidArgumentException( 'Role with ID '.$roleId.' is not existing' );
		return !isset( $role->access ) || $role->access == self::ROLE_ACCESS_NONE;
	}

	/**
	 *	Indicates whether access to a controller action is allowed for a given role.
	 *	@access		public
	 *	@param		string		$roleId			Role ID
	 *	@param		string		$controller		Name of controller
	 *	@param		string		$action			Name of action
	 *	@return		integer		Right state
	 *
	 *	Return statuses:
	 *	-2: outside but logged in
	 *	-1: no access at all
	 *	 0: no access
	 *	 1: access by right
	 *	 2: access at all
	 *	 3: public access
	 *	 4: public access if outside
	 *	 5: public access if inside
	 */
	public function hasRight( string $roleId, string $controller = 'index', string $action = 'index' ): int
	{
		$controller	= strtolower( str_replace( '/', '_', $controller ) );
		$linkPath	= $controller && $action ? $controller.'_'.$action : '';

		if( in_array( $linkPath, $this->linksPublic ) )
			return 3;
		if( $roleId ){
			if( in_array( $linkPath, $this->linksPublicInside ) )
				return 5;
			if( in_array( $linkPath, $this->linksPublicOutside ) )
				return -2;
			if( $this->hasFullAccess( $roleId ) )
				return 2;
			if( $this->hasNoAccess( $roleId ) )
				return -1;
			$rights	= $this->getRights( $roleId );
			if( isset( $rights[$controller] ) && in_array( $action, $rights[$controller] ) )
				return 1;
		}
		else{
			if( in_array( $linkPath, $this->linksPublicOutside ) )
				return 4;
		}
		return 0;
	}

	/**
	 *	Sets a list of links with public access.
	 *	@access		public
	 *	@param		array		$links			Map of links, e.g. auth_login
	 *	@param		string		$mode			Mode: set (default) or append
	 *	@return		self
	 */
	public function setPublicLinks( array $links, string $mode = 'set' ): self
	{
		if( count( $links ) ){
			if( $mode === 'append' )
				foreach( $links as $link )
					$this->linksPublic[]	= $link;
			else
				$this->linksPublic	= $links;
		}
		return $this;
	}

	/**
	 *	Sets a list of links with public access.
	 *	@access		public
	 *	@param		array		$links			Map of links, e.g. auth_login
	 *	@param		string		$mode			Mode: set (default) or append
	 *	@return		self
	 */
	public function setPublicInsideLinks( array $links, string $mode = 'set' ): self
	{
		if( count( $links ) ){
			if( $mode === 'append' )
				foreach( $links as $link )
					$this->linksPublicInside[]	= $link;
			else
				$this->linksPublicInside	= $links;
		}
		return $this;
	}

	/**
	 *	Sets a list of links with public access.
	 *	@access		public
	 *	@param		array		$links			Map of links, e.g. auth_login
	 *	@param		string		$mode			Mode: set (default) or append
	 *	@return		self
	 */
	public function setPublicOutsideLinks( array $links, string $mode = 'set' ): self
	{
		if( count( $links ) ){
			if( $mode === 'append' )
				foreach( $links as $link )
					$this->linksPublicOutside[]	= $link;
			else
				$this->linksPublicOutside	= $links;
		}
		return $this;
	}

	/**
	 *	Allows access to a controller action for a role.
	 *	@abstract
	 *	@access		public
	 *	@param		string		$roleId			Role ID
	 *	@param		string		$controller		Name of Controller
	 *	@param		string		$action			Name of Action
	 *	@return		integer
	 */
	abstract public function setRight( string $roleId, string $controller, string $action ): int;

	//  --  PROTECTED  --  //

	abstract protected function getRights( string $roleId ): array;

	/**
	 *	Returns Role.
	 *	@abstract
	 *	@access		protected
	 *	@param		string		$roleId			Role ID
	 *	@return		array|object
	 */
	abstract protected function getRole( string $roleId );
}
