<?php
/**
 *	Builds Trigger Info Site File.
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
 *	Builds Trigger Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Triggers extends SiteInfoAbstraction
{
	/**
	 *	Creates Trigger Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(): bool
	{
		if( !isset( $this->env->data->triggers ) )
			return FALSE;
		if( !$this->env->data->triggers )
			return FALSE;

		$this->verboseCreation( 'triggers' );

		$list		= array();
		foreach( $this->env->data->triggers as $triggerName => $triggers )
			foreach( $triggers as $nr => $trigger ){
			$class	= $this->env->getClassFromId( $trigger['classId'] );
			$uri	= 'class.'.$class->getId().'.html#class_method_'.$trigger['method'];
			$method	= HtmlElements::Link( $uri, $trigger['method'], 'method' );
			$class	= $this->getTypeMarkUp( $class, TRUE );

			$info	= array();
			$info[]	= HtmlElements::ListItem( 'Class: '.$class );
			$info[]	= HtmlElements::ListItem( 'Method: '.$method );
			$info	= HtmlElements::unorderedList( $info );

			$type	= HtmlTag::create( 'dt', $trigger['name'] );
			$def	= HtmlTag::create( 'dd', $info.'<pre>'.$trigger['text'].'</pre>' );
			$list[]	= $type.$def;
		}
		$content	= HtmlTag::create( 'dl', implode( "\n", $list ) );

		$words	= isset( $this->env->words['triggers'] ) ? $this->env->words['triggers'] : array();
		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'key'		=> 'triggers',
			'id'		=> 'info-triggers',
			'topic'		=> isset( $words['heading'] ) ? $words['heading'] : 'triggers',
			'content'	=> $content,
			'words'		=> $words,
			'footer'	=> $this->buildFooter(),
		);
		$template	= 'site/info/triggers';
		$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
		$content	= $this->loadTemplate( $template, $uiData );

		$this->saveFile( "triggers.html", $content );
		$this->appendLink( 'triggers.html', 'triggers', count( $list ) );
		return TRUE;
	}
}
