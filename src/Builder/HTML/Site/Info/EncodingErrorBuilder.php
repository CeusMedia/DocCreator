<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Encoding Error Info Site File.
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
 *	Builds Encoding Error Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class EncodingErrorBuilder extends SiteInfoAbstraction
{
	/**
	 *	Creates Change Log Sites if any Change Log File is available in Project Folder.
	 *	@access		public
	 *	@param		string			$pathTarget		Path to save Sites in
	 *	@return		bool
	 */
	public function createSite( $pathTarget, &$linkList ): bool
	{
		return FALSE; //  disabled for now

		$list		= array();
		foreach( $this->env->data->getFiles() as $fileId => $file ){
			if( !$file->errors )
				continue;
			$errors	= $file->errors;
			$label	= "<b>".$file->getPathname()."</b>";
			$list[]	= HtmlElements::ListItem( $label.$errors );
		}
		if( $list ){
			if( $this->env->verbose )
				$this->env->out->sameLine( "Creating site: ".$this->env->words['links']['encodingErrors'] );
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'topic'		=> $this->env->words['encoding']['heading'],
				'list'		=> HtmlElements::unorderedList( $list ),
				'words'		=> $this->env->words['encoding'],
				'footer'	=> $this->buildFooter(),
			);
			$content	= $this->loadTemplate( 'site/info/encoding', $uiData );
			file_put_contents( $pathTarget."encoding.html", $content );
/*			$linkList[]	= array(
				'url'	=> 'triggers.html',
				'label'	=> $this->env->words['links']['triggers'],
				'count'	=> count( $list )
			);*/
			return TRUE;
		}
		return FALSE;
	}
}
