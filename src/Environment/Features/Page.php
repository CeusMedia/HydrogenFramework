<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Resource\Page as PageResource;

trait Page
{
	/**	@var	PageResource			$page		Page Object */
	protected PageResource $page;

	/**
	 *	Get resource to communicate with chat server.
	 *	@access		public
	 *	@return		PageResource
	 */
	public function getPage(): PageResource
	{
		return $this->page;
	}

	/**
	 *	Initialize page frame resource.
	 *	@access		protected
	 *	@param		boolean		$pageJavaScripts	Flag: compress JavaScripts, default: TRUE
	 *	@param		boolean		$packStyleSheets	Flag: compress Stylesheet, default: TRUE
	 *	@return		static
	 */
	protected function initPage( bool $pageJavaScripts = TRUE, bool $packStyleSheets = TRUE ): static
	{
		$this->page	= new PageResource( $this );
		$this->page->setPackaging( $pageJavaScripts, $packStyleSheets );
		$this->page->setBaseHref( $this->getBaseUrl( self::$configKeyBaseHref ) );
		$this->page->applyModules();

		$words		= $this->getLanguage()->getWords( 'main', FALSE );
		if( isset( $words['main']['title'] ) )
			$this->page->setTitle( $words['main']['title'] );
		$this->runtime->reach( 'env: page' );
		return $this;
	}
}
