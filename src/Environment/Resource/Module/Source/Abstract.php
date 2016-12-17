<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Source
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource.Module.Source
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
abstract class CMF_Hydrogen_Environment_Resource_Module_Source_Abstract{
	protected $env;
	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
	}
	abstract public function index();
}
?>
