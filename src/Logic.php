<?php
/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework;

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Library\Local as LocalModuleLibraryResource;
use ADT_List_Dictionary as Dictionary;
use Alg_Object_Factory as ObjectFactory;
use Alg_Text_CamelCase as CamelCase;
use RuntimeException;

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Logic
{
	/**	@var	Environment					$env			Application Environment Object */
	protected $env;

	/**	@var	CaptainResource				$captain		Event handler */
	protected $captain;

	/**	@var	Dictionary					$config			Configuration collection */
	protected $config;

	/**	@var	LocalModuleLibraryResource	$modules		Module library */
	protected $modules;

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

	public static function getInstance( Environment $env )
	{
		$logicPool	= $env->getLogic();
		$className	= get_called_class();
		$key		= $logicPool->getKeyFromClassName( $className );
		if( !$logicPool->has( $key ) )
			$logicPool->add( $key, $className );
		return $logicPool->get( $key );
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

	protected function callHook( string $resource, string $event, $context = NULL, $payload = NULL )
	{
		$context	= $context ? $context : $this;
		return $this->captain->callHook( $resource, $event, $context, $payload );
	}

	/**
	 *	Tries to find model class for short model key and returns instance.
	 *	@access		protected
	 *	@param		string		$key		Key for model class (eG. 'mailGroupMember' for 'Model_Mail_Group_Member')
	 *	@return		object					Model instance
	 *	@throws		RuntimeException		if no model class could be found for given short model key
	 *	@todo		create model pool environment resource and apply to created shared single instances instead of new instances
	 *	@todo		change \@return to CMF_Hydrogen_Model after CMF model refactoring
	 *	@see		duplicate code with CMF_Hydrogen_Controller::getModel
	 */
	protected function getModel( string $key )
	{
		$classNameWords	= ucwords( CamelCase::decode( $key ) );
		$className		= str_replace( ' ', '_', 'Model '.$classNameWords );
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Model class "'.$className.'" not found' );
		return ObjectFactory::createObject( $className, array( $this->env ) );
	}
}
