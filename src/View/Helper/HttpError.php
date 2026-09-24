<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */

/**
 *	Helper to render basic content for HTTP errors, like an invalid request or problems during service execution.
 *
 *	Copyright (c) 2026 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\View\Helper;

use CeusMedia\Common\Exception\HTTP\Client as ClientException;
use CeusMedia\Common\Exception\HTTP\Server as ServerException;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	Helper to render basic content for HTTP errors, like an invalid request or problems during service execution.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.View.Helper
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class HttpError
{
	protected Environment $env;
	protected ClientException|ServerException|NULL $exception	= NULL;

	/**
	 *	@access		pubic
	 *	@param		Environment		$env		Environment object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@access		public
	 *	@return		string
	 */
	public function render(): string
	{
		if( NULL === $this->exception )
			return '';

		$description	= $this->exception->getDescription();
		$suggestion		= $this->exception->getSuggestion();

		$parts	= [HtmlTag::create( 'h2', $this->exception->getMessage() )];

		if( '' !== $description )
			$parts[]	= HtmlTag::create( 'div', $description, ['class' => 'error-description'] );
		if( '' !== $suggestion )
			$parts[]	= HtmlTag::create( 'div', $suggestion, ['class' => 'error-suggestion'] );

		$parts[]	= HtmlTag::create( 'hr' );
		$iconHome		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-home'] );
		$parts[]	= HtmlTag::create( 'a', $iconHome.'&nbsp;Home', ['href' => './', 'class' => 'btn'] );
		return join( $parts );
	}

	/**
	 *	@access		public
	 *	@param		ClientException|ServerException	$exception
	 *	@return		static
	 */
	public function setException( ClientException|ServerException $exception ): static
	{
		$this->exception	= $exception;
		return $this;
	}
}
