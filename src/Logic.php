<?php
/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	Abstract logic class for contextual singletons. Every environment can have one instance.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Logic{

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
	public function __construct( CMF_Hydrogen_Environment $env ){
		$key	= $env->getLogic()->getKeyFromClassName( get_class( $this ) );
		if( $env->logic->has( $key ) && $env->logic->isInstantiated( $key ) )
			return $env->logic->get( $key );
		$this->env	= $env;
		$this->config	= $env->getConfig();
		$this->modules	= $env->getModules();
		$this->captain	= $env->getCaptain();
		$this->__onInit();
	}

	protected function __clone(){
	}

	/**
	 *	Magic function called at the end of construction.
	 *	ATTENTION: In case of overriding, you MUST bubble down using parent::__onInit();
	 *	Otherwise you will lose the trigger for hook Env::init.
	 *
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(){
	}

	static public function getInstance( CMF_Hydrogen_Environment $env ){
		$className	= get_called_class();
		$key		= $env->getLogic()->getKeyFromClassName( $className );
		if( !$env->logic->has( $key ) )
			$env->logic->add( $key, $className );
		return $env->logic->get( $key );
	}
}
?>
