<?php
class CMF_Hydrogen_View_Helper_Tracker_Piwik extends CMF_Hydrogen_View_Helper_Abstract{

	public function render(){
		$config	= $this->env->getConfig();
		$page	= $this->env->getPage();
		if( !$config->get( 'tracker.enabled' ) )
			return '';
		if( !$config->get( 'tracker.id' ) )
			throw new RuntimeException( 'Track code is missing in config::tracker.id' );
		$path	= $config->get( 'tracker.uri' );
		$page->js->addUrl( $path.'piwik.js' );
		$script	= '
try {
	var piwikTracker = Piwik.getTracker("'.$path.'piwik.php", 2);
	piwikTracker.trackPageView();
	piwikTracker.enableLinkTracking();
} catch( err ) {}
';
		$page->js->addScript( $script );
		$noscript	= UI_HTML_Tag::create( 'noscript',
			UI_HTML_Tag::create( 'p',
				UI_HTML_Tag::create( 'img', NULL, array(
					'src'	=> $path.'piwik.php?idsite='.$config->get( 'tracker.id' ),
					'style'	=> 'border: 0',
					'alt'	=> ''
				) )
			)
		);
		return $noscript;
	}
}
?>