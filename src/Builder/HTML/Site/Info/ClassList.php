<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Class List Info Site File.
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
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;


/**
 *	Builds Class List Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class ClassList extends SiteInfoAbstraction
{
	public string $linkTarget = '_self';

	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function createSite()
	{
		if( $this->env->verbose )
			$this->env->out->sameLine( "Creating: Class List" );
		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'topic'		=> $this->env->words['classList']['heading'],
			'words'		=> $this->env->words['classList'],
			'list'		=> $this->buildClassList(),
			'footer'	=> $this->buildFooter(),
		);
		$content	= $this->loadTemplate( "site/info/classList", $uiData );
		$this->saveFile( "classes.html", $content );
		$this->appendLink( 'classes.html', 'classList' );
	}

	private function buildClassList(): string
	{
		$divClear	= HtmlTag::create( 'div', '', array( 'style' => 'clear: both' ) );
		$list		= array();
		foreach( $this->env->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
				$uri	= 'class.'.$class->getId().'.html';
				$label	= $this->getLabel( $class );
				$link	= HtmlElements::Link( $uri, $label, 'class', $this->linkTarget );
				$div	= HtmlTag::create( 'div', $link, array( 'class' => 'class' ) );
				$list[$label.time()]	= $div;
			}
			foreach( $file->getInterfaces() as $interface ){
				$uri	= 'interface.'.$interface->getId().'.html';
				$label	= $this->getLabel( $interface );
				$link	= HtmlElements::Link( $uri, $label, 'interface', $this->linkTarget );
				$div	= HtmlTag::create( 'div', $link, array( 'class' => 'interface' ) );
				$list[$label.time()]	= $div;
			}
		}
		ksort( $list );
		$last		= "";
		$letters	= array();
		$lines		= array();
		foreach( $list as $key => $item ){
			if( $last != $key[0] ){
				$letters[]	= $key[0];
				$divLetter	= HtmlTag::create(
					'div',
					$key[0],
					array(
						'class'	=> 'letter',
						'id'	=> 'letter-'.$key[0]
					)
				);
				$lines[]	= $divClear.$divLetter;
			}
			$lines[]	= $item;
			$last		= $key[0];
		}

		$list	= array();
		for( $i=65; $i<91; $i++ ){
			$letter	= chr( $i );
			if( in_array( $letter, $letters ) )
				$item	= HtmlElements::Link( '#letter-'.$letter, $letter );
			else
				$item	= HtmlTag::create( 'span', $letter, array( 'class' => 'letter-disabled' ) );
			$list[]	= $item.'&nbsp;';
		}
		$list		= implode( $list );
		$letters	= HtmlTag::create( 'div', $list, array( 'id' => 'list-letters' ) );
		$list		= implode( "\n", $lines ).$divClear;
		return $letters.$list;
	}

	private function getLabel( $artefact ): string
	{
		if( !empty( $this->options['prefix'] ) )
			return preg_replace( '/'.$this->options['prefix'].'/', '', $artefact->getName() );
		return $artefact->getName();
	}
}

