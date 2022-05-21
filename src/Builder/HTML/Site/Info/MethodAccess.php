<?php
/**
 *	...
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
 *	...
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class MethodAccess extends SiteInfoAbstraction
{
	protected $key		= 'methodAccess';
	/**
	 *	Creates Change Log Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(): bool
	{
		$count		= 0;
		$classList	= array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$methodList	= array();
				foreach( $class->getMethods() as $method )
				{
					if( !$method->getAccess() )
					{
						$count++;
						$url			= 'class.'.$class->getId().'.html#class_method_'.$method->getName();
						$link			= HtmlElements::Link( $url, $method->getName(), 'method' );
						$methodList[]	= HtmlElements::ListItem( $link, 1, array( 'class' => 'method' ) );
					}
				}
				if( !$methodList )
					continue;
				$methodList		= HtmlElements::unorderedList( $methodList, 1, array( 'class' => 'methods' ) );
				$url			= 'class.'.$class->getId().'.html';
				$link			= HtmlElements::Link( $url, $class->getName(), 'class' );
				$classList[]	= HtmlElements::ListItem( $link.$methodList, 0, array( 'class' => 'classes' ) );
			}
		}
		if( !$classList )
			return FALSE;

		$this->verboseCreation( $this->key );

		$words	= isset( $this->env->words[$this->key] ) ? $this->env->words[$this->key] : array();
		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'key'		=> $this->key,
			'id'		=> 'info-'.$this->key,
			'topic'		=> isset( $words['heading'] ) ? $words['heading'] : $this->key,
			'content'	=> HtmlElements::unorderedList( $classList ),
			'words'		=> $words,
			'footer'	=> $this->buildFooter(),
		);
		$template	= 'site/info/'.$this->key;
		$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
		$content	= $this->loadTemplate( $template, $uiData );
		$this->saveFile( $this->key.'.html', $content );
		$this->appendLink( $this->key.'.html', $this->key, $count );
		return TRUE;
	}
}
