<?php

/** @var \CeusMedia\HydrogenFramework\View $view */

$include	= $view->loadTemplateFile( 'test/test.php' );

return $view->getData( 'topic' );
