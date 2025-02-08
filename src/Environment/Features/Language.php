<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;

trait Language
{
	use RuntimeFeature;

	/**	@var	LanguageResource			$language		Language support object */
	protected LanguageResource $language;

	/**
	 *	Returns Language Object.
	 *	@access		public
	 *	@return		LanguageResource
	 */
	public function getLanguage(): LanguageResource
	{
		return $this->language;
	}

	protected function initLanguage(): static
	{
		$this->language		= new LanguageResource( $this );
		$this->runtime->reach( 'env: language' );
		return $this;
	}
}
