<?php
class CMF_Hydrogen_Hook{

	static public function callHook( $resource, $event, $context, $module, $data ){
		return $env->getCaptain()->callHook( $resource, $event, $context, $data );
	}

	static protected function getModuleConfig( CMF_Hydrogen_Environment $env, $module ){
		return $env->getConfig()->get( 'module.'.strtolower( $module ).'.', TRUE );
	}

	static protected function sendMail( CMF_Hydrogen_Environment $env, Mail_Abstract $mail, $receivers = array() ){
		$language	= $env->getLanguage()->getLanguage();										// @todo apply user language
		foreach( $receivers as $receiver ){
			if( is_string( $receiver ) )
 				$receiver	= (object) array( 'email' => $receiver );
			if( is_array( $receiver ) )
 				$receiver	= (object) $receiver;
			if( !property_exists( $receiver, 'email' ) )
				throw new InvalidArgumentException( 'Given receiver is missing email address' );
			$env->getLogic()->mail->handleMail( $mail, $receiver, $language );
		}
	}
}
