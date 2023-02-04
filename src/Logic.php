<?php /** @noinspection PhpUnused */

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Text\CamelCase as CamelCase;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;
use ReflectionException;
use RuntimeException;

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Logic
{
	/**	@var	Environment					$env			Application Environment Object */
	protected Environment $env;

	/**	@var	CaptainResource				$captain		Event handler */
	protected CaptainResource $captain;

	/**	@var	Dictionary					$config			Configuration collection */
	protected Dictionary $config;

	/**	@var	LocalModuleLibraryResource	$modules		Module library */
	protected LocalModuleLibraryResource $modules;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$logicPool	= $env->getLogic();
		$key		= $logicPool->getKeyFromClassName( get_class( $this ) );
//		if( $logicPool->has( $key ) && $logicPool->isInstantiated( $key ) )
//			return $env->logic->get( $key );
		$this->env		= $env;
		$this->config	= $env->getConfig();
		$this->modules	= $env->getModules();
		$this->captain	= $env->getCaptain();
		if( !$logicPool->has( $key ) )
			$logicPool->add( $key, $this );
		$this->__onInit();
	}

	/**
	 *	@param		Environment			$env
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	public static function getInstance( Environment $env ): self
	{
		$logicPool	= $env->getLogic();
		$className	= static::class;
		$key		= $logicPool->getKeyFromClassName( $className );
		if( !$logicPool->has( $key ) )
			$logicPool->add( $key, $className );
		/** @var self $instance */
		$instance	= $logicPool->get( $key );
		return $instance;
	}

	//  --  PROTECTED  --  //

	protected function __clone()
	{
	}

	/**
	 *	Magic function called at the end of construction.
	 *	ATTENTION: In case of overriding, you MUST bubble down using parent::__onInit();
	 *	Otherwise you will lose the trigger for hook Env::init.
	 *
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit()
	{
	}

	protected function callHook( string $resource, string $event, ?object $context = NULL, array & $payload = NULL ): ?bool
	{
		$context	= $context ?: $this;
		return $this->captain->callHook( $resource, $event, $context, $payload );
	}

	/**
	 *	Tries to find model class for short model key and returns instance.
	 *	@access		protected
	 *	@param		string		$key		Key for model class (eG. 'mailGroupMember' for 'Model_Mail_Group_Member')
	 *	@return		object					Model instance
	 *	@throws		RuntimeException		if no model class could be found for given short model key
	 *	@throws		ReflectionException
	 *	@todo		create model pool environment resource and apply to created shared single instances instead of new instances
	 *	@todo		change \@return to CMF_Hydrogen_Model after CMF model refactoring
	 *	@see		duplicate code with CMF_Hydrogen_Controller::getModel
	 */
	protected function getModel( string $key ): object
	{
		$classNameWords	= ucwords( CamelCase::decode( $key ) );
		$className		= str_replace( ' ', '_', 'Model '.$classNameWords );
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Model class "'.$className.'" not found' );
		return ObjectFactory::createObject( $className, array( $this->env ) );
	}
}
