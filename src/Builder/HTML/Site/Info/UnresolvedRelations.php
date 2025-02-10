<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
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
 *	Builds Deprecation Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2025 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class UnresolvedRelations extends SiteInfoAbstraction
{
	protected int $count		= 0;

	/**
	 *	Creates Deprecation Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(): bool
	{
		$classList	= [];
		foreach( $this->env->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
				$list	= [];
				$this->checkUnresolvedAndAddListItemIfNot( $list, $class->getExtendedClass(), 'extends: ' );
				foreach( $class->getExtendingClasses() as $extendingClass )
					$this->checkUnresolvedAndAddListItemIfNot( $list, $extendingClass, 'extendedBy: ' );
				if( $list ){
					$url	= 'class.'.$class->getId().'.html';
					$link	= HtmlElements::Link( $url, $class->getName(), 'class' );
					$list	= HtmlElements::unorderedList( $list, 1 );
					$classList[]	= HtmlElements::ListItem( $link.$list, 0, ['class' => 'class'] );
				}
			}
			foreach( $file->getInterfaces() as $interface ){
				$list	= [];
				foreach( $interface->getExtendingInterfaces() as $extendingInterface )
					$this->checkUnresolvedAndAddListItemIfNot( $list, $extendingInterface, 'extendedBy: ' );
				foreach( $interface->getImplementingClasses() as $implementingClass )
					$this->checkUnresolvedAndAddListItemIfNot( $list, $implementingClass, 'implementedBy: ' );
				if( $list ){
					$url	= 'interface.'.$interface->getId().'.html';
					$link	= HtmlElements::Link( $url, $interface->getName(), 'interface' );
					$list	= HtmlElements::unorderedList( $list, 1 );
					$classList[]	= HtmlElements::ListItem( $link.$list, 0, ['class' => 'interface'] );
				}
			}
		}
		if( $this->count ){
			$this->verboseCreation( 'unresolvedRelations' );

			$words	= $this->env->words['unresolvedRelations'] ?? [];
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> 'unresolvedRelations',
				'id'		=> 'info-unresolvedRelations',
				'topic'		=> $words['heading'] ?? 'unresolvedRelations',
				'content'	=> '<div id="tree">'.HtmlElements::unorderedList( $classList ).'</div>',
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/unresolvedRelations';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "unresolvedRelations.html", $content );
			$this->appendLink( 'unresolvedRelations.html', 'unresolvedRelations', $this->count );
		}
		return $this->count > 0;
	}

	protected function checkUnresolvedAndAddListItemIfNot( &$list, $relation, $prefix = NULL )
	{
		if( !is_string( $relation ) )
			return;
		$this->count++;
		$list[]	= HtmlElements::ListItem( $prefix.$relation, 1 );
	}
}
