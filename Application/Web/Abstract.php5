<?php
class CMF_Hydrogen_Application_Web_Abstract extends CMF_Hydrogen_Application_Abstract{

	protected $components			= array();

	protected function logOnComplete()
	{
		$responseLength	= $this->env->getResponse()->getLength();
		$responseTime	= $this->env->getClock()->stop( 6, 0 );
		// ...
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
		$path	= $this->env->getConfig()->get( 'path.templates' );
		return $view->loadTemplateFile( $path.$templateFile, $this->components );
	}
}
?>
