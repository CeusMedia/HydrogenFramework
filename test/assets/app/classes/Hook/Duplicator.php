<?php
class Hook_Duplicator extends \CeusMedia\HydrogenFramework\Hook
{
	public function onDuplicate(): bool
	{
		$type	= gettype( $this->payload['content'] ?? '' );
		$this->payload['content']	= match( $type ){
			'integer', 'double'	=> $this->payload['content'] * 2,
			default				=> str_repeat( $this->payload['content'] ?? '', 2 ),
		};
		return TRUE;
	}
}