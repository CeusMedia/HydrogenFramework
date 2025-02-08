<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Environment\Features\Captain as CaptainFeature;
use CeusMedia\HydrogenFramework\Environment\Features\Modules as ModulesFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction as AbstractAclResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\AllPublic as AllPublicAclResource;
use ReflectionException;

trait AccessControl
{
	use CaptainFeature;
	use ModulesFeature;

	/** @var	AbstractAclResource			$acl			Implementation of access control list */
	protected AbstractAclResource $acl;

	/**
	 *	Initialize remote access control list.
	 *	@access		public
	 *	@return		AbstractAclResource	Instance of access control list object
	 */
	public function getAcl(): AbstractAclResource
	{
		return $this->acl;
	}

	/**
	 *	Initialize remote access control list if roles module is installed.
	 *	Calls hook and applies return class name. Otherwise, use all-public handler.
	 *	@access		protected
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	protected function initAcl(): static
	{
		$type		= AllPublicAclResource::class;
		if( $this->hasModules() ){																	//  module support and modules available
			$payload	= ['className' => NULL];
			$isHandled	= $this->getCaptain()->callHook( 'Env', 'initAcl', $this, $payload );	//  call related module event hooks
			if( $isHandled && NULL !== $payload['className'] )
				$type	= $payload['className'];
		}
		/** @var Abstraction $acl */
		$acl	= ObjectFactory::createObject( $type, array( $this ) );
		$this->acl	= $acl;

		$linksPublic		= [];
		$linksPublicOutside	= [];
		$linksPublicInside	= [];
		foreach( $this->getModules()->getAll() as $module ){
			foreach( $module->links as $link ){
				switch( $link->access ){
					case 'outside':
						$linksPublicOutside[]	= str_replace( "/", "_", $link->path );
						break;
					case 'inside':
						$linksPublicInside[]	= str_replace( "/", "_", $link->path );
						break;
					case 'public':
						$linksPublic[]	= str_replace( "/", "_", $link->path );
						break;
				}
			}
		}
		if( 0 !== count( $linksPublic ) )
			$this->acl->setPublicLinks( $linksPublic );
		if( 0 !== count( $linksPublicOutside ) )
			$this->acl->setPublicOutsideLinks( $linksPublicOutside );
		if( 0 !== count( $linksPublicInside ) )
			$this->acl->setPublicInsideLinks( $linksPublicInside );

		$this->runtime->reach( 'env: initAcl', 'Finished setup of access control list.' );
		return $this;
	}
}
