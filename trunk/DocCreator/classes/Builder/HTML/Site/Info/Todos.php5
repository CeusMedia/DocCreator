<?php
/**
 *	Builds Todo Info Site File.
 *
 *	Copyright (c) 2008-2009 Christian Würker (ceus-media.de)
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
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Todos.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
/**
 *	Builds Todo Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Todos.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Site_Info_Todos extends DocCreator_Builder_HTML_Site_Info_Abstract{

	/**
	 *	Creates Todo Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 *	@todo		support Interfaces, too
	 */
	public function createSite(){
		$content	= "";
		$todos		= array();
		foreach( $this->env->data->getFiles() as $fileId => $file ){
			foreach( $file->getClasses() as $classId => $class ){
				$classTodos		= array();
				$methodTodos	= array();

				$classUri	= "class.".$class->getId().".html";
				$classLink	= UI_HTML_Elements::Link( $classUri, $class->getName(), 'class' );

				if( $class->getTodos() ){
					foreach( $class->getTodos() as $todo )
						$classTodos[]	= UI_HTML_Elements::ListItem( $todo, 1, array( 'class' => "classItem" ) );
					$this->count	+= count( $classTodos );
				}

				foreach( $class->getMethods() as $methodName => $methodData ){
					if( !$methodData->getTodos() )
						continue;
					$list	= array();
					foreach( $methodData->getTodos() as $todo )
						$list[]		= UI_HTML_Elements::ListItem( $todo, 2, array( 'class' => "methodItem" ) );
					$list	= UI_HTML_Elements::unorderedList( $list, 2, array( 'class' => "methodList" ) );
					$this->count	+= count( $list );
					$methodUrl		= 'class.'.$class->getId().".html#class_method_".$methodName;
					$methodLink		= UI_HTML_Elements::Link( $methodUrl, $methodName, 'method' );
					$methodTodos[]	= UI_HTML_Elements::ListItem( $methodLink.$list, 1, array( 'class' => "method" ) );	
				}
				if( !$classTodos && !$methodTodos )
					continue;

				$methodTodos	= UI_HTML_Elements::unorderedList( $methodTodos, 1, array( 'class' => "methods" ) );
				$classTodos		= UI_HTML_Elements::unorderedList( $classTodos, 1, array( 'class' => "classList" ) );
				$todos[$class->getName()]		= UI_HTML_Elements::ListItem( $classLink.$classTodos.$methodTodos, 0, array( 'class' => "class" ) );
			}
		}
		ksort( $todos );
		if( $todos ){
			$this->verboseCreation( 'todos' );

			$words	= isset( $this->env->words['todos'] ) ? $this->env->words['todos'] : array();
			$uiData	= array(
				'key'		=> 'todos',
				'id'		=> 'info-todos',
				'title'		=> isset( $words['heading'] ) ? $words['heading'] : 'todos',
				'content'	=> UI_HTML_Elements::unorderedList( $todos, 0, array( 'class' => "classes" ) ),
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/todos';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "todos.html", $content );
			$this->appendLink( 'todos.html', 'todos', count( $todos ) );
		}
		return (bool) count( $todos );
	}
}
?>

