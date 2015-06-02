<?php
/**
 *	Builds Trigger Info Site File.
 *
 *	Copyright (c) 2008-2015 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Triggers.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
/**
 *	Builds Trigger Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2015 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Triggers.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Site_Info_Triggers extends DocCreator_Builder_HTML_Site_Info_Abstract{

	/**
	 *	Creates Trigger Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(){
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
			$method	= UI_HTML_Elements::Link( $uri, $trigger['method'], 'method' );
			$class	= $this->getTypeMarkUp( $class, TRUE );

			$info	= array();
			$info[]	= UI_HTML_Elements::ListItem( 'Class: '.$class );
			$info[]	= UI_HTML_Elements::ListItem( 'Method: '.$method );
			$info	= UI_HTML_Elements::unorderedList( $info );

			$type	= UI_HTML_Tag::create( 'dt', $trigger['name'] );
			$def	= UI_HTML_Tag::create( 'dd', $info.'<pre>'.$trigger['text'].'</pre>' );
			$list[]	= $type.$def;
		}
		$content	= UI_HTML_Tag::create( 'dl', implode( "\n", $list ) );

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
?>
