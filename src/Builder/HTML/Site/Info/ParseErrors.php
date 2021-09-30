<?php
/**
 *	Builds Parse Error Info Site File.
 *
 *	Copyright (c) 2008-2021 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;

use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;

use UI_HTML_Elements as HtmlElements;

/**
 *	Builds Todo Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
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
		$list		= array();
		foreach( $this->env->data->getFiles() as $fileId => $file )
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

		$words	= isset( $this->env->words['parseErrors'] ) ? $this->env->words['parseErrors'] : array();
		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'key'		=> 'parseErrors',
			'id'		=> 'info-parseErrors',
			'topic'		=> isset( $words['heading'] ) ? $words['heading'] : 'parseErrors',
			'content'	=> HtmlElements::unorderedList( $list, 0, array( 'class' => "classes" ) ),
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
