<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

class Logic_Shared extends SharedLogic
{
	public string $uuid;

	public function duplicateContent( mixed $content ): mixed
	{
		$payload	= ['content' => $content];
		$this->callHook( 'Test', 'duplicate', $this, $payload );
		return $payload['content'];
	}

	protected function __onInit(): void
	{
		$this->uuid = ID::uuid();
	}
}