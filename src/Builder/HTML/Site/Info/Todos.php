<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Todo Info Site File.
 *
 *	Copyright (c) 2008-2025 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2025 Christian Würker
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
 *	@copyright		2008-2025 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Todos extends SiteInfoAbstraction
{
	protected int $count	= 0;

	/**
	 *	Creates Todo Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 *	@todo		support Interfaces, too
	 */
	public function createSite(): bool
	{
		$todos		= [];
		foreach( $this->env->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
				$classTodos		= [];
				$methodTodos	= [];

				$classUri	= "class.".$class->getId().".html";
				$classLink	= HtmlElements::Link( $classUri, $class->getName(), 'class' );

				if( $class->getTodos() ){
					foreach( $class->getTodos() as $todo )
						$classTodos[]	= HtmlElements::ListItem( $todo, 1, ['class' => "classItem"] );
					$this->count	+= count( $classTodos );
				}

				foreach( $class->getMethods() as $methodName => $methodData ){
					if( !$methodData->getTodos() )
						continue;
					$list	= [];
					foreach( $methodData->getTodos() as $todo )
						$list[]		= HtmlElements::ListItem( $todo, 2, ['class' => "methodItem"] );
					$methodList		= HtmlElements::unorderedList( $list, 2, ['class' => "methodList"] );
					$this->count	+= count( $list );
					$methodUrl		= 'class.'.$class->getId().".html#class_method_".$methodName;
					$methodLink		= HtmlElements::Link( $methodUrl, $methodName, 'method' );
					$methodTodos[]	= HtmlElements::ListItem( $methodLink.$methodList, 1, ['class' => "method"] );
				}
				if( !$classTodos && !$methodTodos )
					continue;

				$methodTodos	= HtmlElements::unorderedList( $methodTodos, 1, ['class' => "methods"] );
				$classTodos		= HtmlElements::unorderedList( $classTodos, 1, ['class' => "classList"] );
				$todos[$class->getName()]		= HtmlElements::ListItem( $classLink.$classTodos.$methodTodos, 0, ['class' => "class"] );
			}
		}
		ksort( $todos );
		if( $todos ){
			$this->verboseCreation( 'todos' );

			$words	= $this->env->words['todos'] ?? [];
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> 'todos',
				'id'		=> 'info-todos',
				'topic'		=> $words['heading'] ?? 'todos',
				'content'	=> HtmlElements::unorderedList( $todos, 0, ['class' => "classes"] ),
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/todos';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "todos.html", $content );
			$this->appendLink( 'todos.html', 'todos', count( $todos ) );
		}
		return count( $todos ) > 0;
	}
}
