<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Parse Error Info Site File.
 *
 *	Copyright (c) 2008-2023 Christian Würker (ceusmedia.de)
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
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;

/**
 *	Builds Todo Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class ParseErrors extends SiteInfoAbstraction
{
	/**
	 *	Creates Parser Error Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(): bool
	{
		$list		= [];
		foreach( $this->env->data->getFiles() as $file )
		{
			if( !$file->errors )
				continue;

			$label	= '<h4>'.$file->getBasename().'</h4><div class="file-uri">'.$file->getPathname()."</div>";
			$errors	= "<xmp>".$file->errors."</xmp><br/>";
			$list[]	= HtmlElements::ListItem( $label.$errors );
		}
		if( !$list )
			return FALSE;

		$this->verboseCreation( 'parseErrors' );

		$words	= $this->env->words['parseErrors'] ?? [];
		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'key'		=> 'parseErrors',
			'id'		=> 'info-parseErrors',
			'topic'		=> $words['heading'] ?? 'parseErrors',
			'content'	=> HtmlElements::unorderedList( $list, 0, ['class' => "classes"] ),
			'words'		=> $words,
			'footer'	=> $this->buildFooter(),
		);
		$template	= 'site/info/parseErrors';
		$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
		$content	= $this->loadTemplate( $template, $uiData );
		$this->saveFile( "errors.parser.html", $content );
		$this->appendLink( 'errors.parser.html', 'parseErrors', count( $list ) );
		return TRUE;
	}
}
