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
class CMF_Hydrogen_Logic
{
	/**	@var	CMF_Hydrogen_Environment								$env			Application Environment Object */
	protected $env;

	/**	@var	CMF_Hydrogen_Environment_Resource_Captain				$captain		Event handler */
	protected $captain;

	/**	@var	ADT_List_Dictionary										$config			Configuration collection */
	protected $config;

	/**	@var	CMF_Hydrogen_Environment_Resource_Module_Library_Local	$modules		Module library */
	protected $modules;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment		$env		Environment instance
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$key	= $env->getLogic()->getKeyFromClassName( get_class( $this ) );
//		if( $env->logic->has( $key ) && $env->logic->isInstantiated( $key ) )
//			return $env->logic->get( $key );
		$this->env		= $env;
		$this->config	= $env->getConfig();
		$this->modules	= $env->getModules();
		$this->captain	= $env->getCaptain();
		if( !$env->logic->has( $key ) )
			$env->logic->add( $key, $this );
		$this->__onInit();
	}

	public static function getInstance( CMF_Hydrogen_Environment $env ){
		$className	= get_called_class();
		$logicPool	= $env->getLogic();
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
	 *	@throws		\RuntimeException		if no model class could be found for given short model key
	 *	@todo		create model pool environment resource and apply to created shared single instances instead of new instances
	 *	@todo		change \@return to CMF_Hydrogen_Model after CMF model refactoring
	 *	@see		duplicate code with CMF_Hydrogen_Controller::getModel
	 */
	protected function getModel( string $key )
	{
		$classNameWords	= ucwords( \Alg_Text_CamelCase::decode( $key ) );
		$className		= str_replace( ' ', '_', 'Model '.$classNameWords );
		if( !class_exists( $className ) )
			throw new \RuntimeException( 'Model class "'.$className.'" not found' );
		return \Alg_Object_Factory::createObject( $className, array( $this->env ) );
	}
}
