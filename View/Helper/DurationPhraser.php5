<?php
class CMF_Hydrogen_View_Helper_DurationPhraser extends CMF_Hydrogen_View_Helper_Abstract{

	protected $phraser		= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->setEnv( $env );
	}

	/**
	 *	Sets path to ranges by language topic and language section (in loaded topic).
	 *	@access		public
	 *	@param		string		$topic			Key of language file
	 *	@param		string		$section		Section name in loaded language file
	 *	@throws		InvalidArgumentException	if section is not available in language file
	 *	@return		void
	 */
	public function setLanguage( $topic, $section ){
		$words		= $this->env->language->getWords( $topic );
		if( !isset( $words[$section] ) )
			throw new InvalidArgumentException( 'Invalid language section "'.$section.'" in topic "'.$topic.'"' );
		$this->phraser	= new Alg_Time_DurationPhraser( $words[$section] );
	}

	public function getFromTimestamp( $timestamp ){
		if( !$this->phraser )
			throw new RuntimeException( 'No language source set' );
		return $this->phraser->getPhraseFromTimestamp( $timestamp );
	}

	public function getFromSeconds( $seconds ){
		if( !$this->phraser )
			throw new RuntimeException( 'No language source set' );
		return $this->phraser->getPhraseFromSeconds( $seconds );
	}
}
?>
