<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

namespace CeusMedia\HydrogenFramework\Logic;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Text\CamelCase as CamelCase;
use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface;
use DomainException;
use ReflectionException;
use RuntimeException;

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class Abstraction
{
	/**	@var	Environment					$env			Application Environment Object */
	protected Environment $env;

	/**	@var	CaptainResource				$captain		Event handler */
	protected CaptainResource $captain;

	/**	@var	Dictionary					$config			Configuration collection */
	protected Dictionary $config;

	/**	@var	LibraryInterface			$modules		Module library */
	protected LibraryInterface $modules;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->config	= $env->getConfig();
		$this->modules	= $env->getModules();
		$this->captain	= $env->getCaptain();

		$this->__onInit();
	}

	/**
	 *	@param		Environment			$env
	 *	@return		static
	 *	@throws		ReflectionException
	 */
	abstract public static function getInstance( Environment $env ): static;


	//  --  PROTECTED  --  //


	/**
	 *	Magic function called at the end of construction.
	 *	ATTENTION: In case of overriding, you MUST bubble down using parent::__onInit();
	 *	Otherwise you will lose the trigger for hook Env::init.
	 *
	 *	@access		protected
	 *	@return		void
	 *	@codeCoverageIgnore
	 */
	protected function __onInit(): void
	{
	}

	/**
	 *	Trigger an event hooked by modules on load.
	 *	Events are defined by a resource key (= a scope) and an event key (= a trigger key).
	 *	Example: resource "Auth" and event "onLogout" will call all hook class methods, defined in
	 *	module definitions by <code><hook resource="Auth" event"onLogout">MyModuleHook::onAuthLogout</hook></code>.
	 *
	 *	There are 2 ways of carrying data between the hooked callback method and the calling object: context and payload.
	 *
	 * 	The context can provide a prepared data object or the calling object itself to the hook callback method.
	 *	The hook can read from and write into this given context object.
	 *
	 * 	The more strict way is to use a prepared payload list reference, which is a prepared array.
	 *	The payload list is an array (map) to work on within the hook callback method.
	 *	The calling object method can interpret/use the payload changes afterward.
	 *
	 *	@see		CaptainResource#callHook() Call hook in captain resource
	 *	@param		string		$resource		Name of resource (e.G. Page or View)
	 *	@param		string		$event			Name of hook event (e.G. onBuild or onRenderContent)
	 *	@param		object|NULL	$context		Context object, will be available inside hook as $context
	 *	@param		array|NULL	$payload		Map of hook payload data, will be available inside hook as $payload
	 *	@return		bool|NULL					TRUE if hook is chain-breaking, FALSE if hook is disabled or non-chain-breaking, NULL if no modules installed or no hooks defined
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			if method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 */
	public function callHook( string $resource, string $event, ?object $context = NULL, array & $payload = NULL ): ?bool
	{
		$payload	??= [];
		return $this->env->getCaptain()->callHook( $resource, $event, $context ?? $this, $payload );
	}

	/**
	 *	Tries to find model class for short model key and returns instance.
	 *	@access		protected
	 *	@param		string		$key		Key for model class (eG. 'mailGroupMember' for 'Model_Mail_Group_Member')
	 *	@return		object					Model instance
	 *	@throws		RuntimeException		if no model class could be found for given short model key
	 *	@throws		ReflectionException
	 *	@todo		create model pool environment resource and apply to created shared single instances instead of new instances
	 *	@see		duplicate code with Controller::getModel
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
