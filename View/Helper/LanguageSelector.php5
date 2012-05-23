<?php
/**
 *	Builds Language Menu using Tree Menu and CSS only.
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			01.02.2009
 *	@version		0.2
 *	@version		$Id$
 */
/**
 *	Builds Language Menu using Tree Menu and CSS only.
 *	@category		cmFrameworks
 *	@package		Hydrogen.View.Helper
 *	@extends		CMF_Hydrogen_View_Helper_Abstract
 *	@uses			UI_HTML_CSS_LinkSelect
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			01.02.2009
 *	@version		$Id$
 */
class CMF_Hydrogen_View_Helper_LanguageSelector extends CMF_Hydrogen_View_Helper_Abstract
{
	/**
	 *	Builds Language Menu.
	 *	@access		public
	 *	@param		string		$class		CSS Class
	 *	@return		string
	 */
	public function render( $class = "menu select m" )
	{
		if( !$this->hasEnv() )
			throw new RuntimeException( 'Set environment first' );
		$language	= $this->env->getLanguage();
		$words		= $language->getWords( 'main' );

		$locale		= $language->getLanguage();
		$locales	= $language->getLanguages();
		$locale		= $this->env->getSession()->get( 'language' );
		$languages	= $words['languages'];

		$list	= array();
		foreach( $locales as $languageKey )
		{
			$languageLabel	= $languages[$languageKey];
			$list[]	= array(
				'key'	=> $languageKey,
				'label'	=> '<img src="//icons.ceusmedia.com/famfamfam/flags/png/'.$languageKey.'.png" alt="" class="flag"/> '.$languageLabel,
				'url'	=> './?switchLanguageTo='.trim( $languageKey )
			);
		}
		$list			= UI_HTML_CSS_LinkSelect::build( "language", $list, $locale, $class );
		return '
<!-- LANGUAGE SELECTOR >> -->
<div class="menu select m">'.$list.'</div>
<!-- << LANGUAGE SELECTOR -->';
	}
}
?>