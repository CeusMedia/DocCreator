<?php
/**
 *	Builds for Index Tree for Classes or Files.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
namespace CeusMedia\DocCreator\Builder\HTML\Site;

use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;

use FS_File_Writer as FileWriter;
use UI_HTML_Elements as HtmlElements;
use UI_HTML_Tag as HtmlTag;

/**
 *	Builds for Index Tree for Classes or Files.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Control extends HtmlBuilderAbstraction
{
	protected $linkTarget	= "frm_content";

	private function buildLinks( array $linkList ): string
	{
		$list	= array();
		$words	= $this->env->words['links'];
		foreach( $linkList as $link ){
			$label	= isset( $words[$link['key']] ) ? $words[$link['key']] : $link['key'];
			$label	.= $link['count'] > 1 ? " (".$link['count'].")" : "";
			$class	= 'info '.$link['key'];
			$link	= HtmlElements::Link( $link['url'], $label, $class, $this->linkTarget );
			$list[]	= HtmlElements::ListItem( $link );
		}

		$uiData	= array(
			'list'	=> HtmlElements::unorderedList( $list, 0, array( 'class' => "links" ) ),
			'words'	=> $words,
		);
		return $this->loadTemplate( "site/links", $uiData );
	}

	protected function buildLogo(): string
	{
		$logo = $this->env->config->getBuilderLogo( $this->env->builder );
		if( $logo->source ){
			$attributes	= array(
				'src'		=> 'images/'.$logo->source,
				'alt'		=> $logo->title,
			);
			$image		= HtmlTag::create( 'img', NULL, $attributes );
			if( $logo->link ){
				$image	= HtmlTag::create( 'a', $image, array(
					'href'		=> $logo->link,
					'target'	=> '_top',
				) );
			}
			return $image;
		}
		return '';
	}

	/**
	 *	Builds complete Control Frame View.
	 *	@access		public
	 *	@return		void
	 *	@todo		rename to buildView
	 */
	public function createControl( array $linkList )
	{
		$pathTarget	= $this->env->getBuilderTargetPath();
		$builder	= new \CeusMedia\DocCreator\Builder\HTML\Site\Tree( $this->env );
		$builder->setTargetFrame( $this->linkTarget );
		$tree		= $builder->buildTree();
		$logo		= $this->buildLogo();

		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'tree'		=> $tree,
			'links'		=> $this->buildLinks( $linkList ),
			'logo'		=> $logo,
		);
		$content	= $this->loadTemplate( "site/control", $uiData );
        FileWriter::save( $pathTarget."control.html", $content );
	}
}

