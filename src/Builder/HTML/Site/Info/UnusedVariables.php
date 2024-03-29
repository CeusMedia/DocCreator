<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
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

use CeusMedia\Common\Alg\UnusedVariableFinder;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;


/**
 *	Builds Deprecation Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class UnusedVariables extends SiteInfoAbstraction
{
	/**
	 *	Creates Deprecation Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(): bool
	{
		$count		= 0;
		$finder		= new UnusedVariableFinder();
		$classList	= [];
		foreach( $this->env->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
				$finder->readCode( $file->getSourceCode() );
				$unusedVariables	= $finder->getUnusedVars();
				if( !$unusedVariables )
					continue;

				$listMethods	= [];
				foreach( $unusedVariables as $method => $vars ){
					$listVars	= [];
					foreach( $vars as $var ){
						$count++;
						$span	= '<span class="var">'.$var.'</span>';
						$item	= HtmlElements::ListItem( $span, 2, ['class' => "varItem"] );
						$listVars[]	= $item;
					}
					if( !$listVars )
						continue;
					$link	= HtmlElements::Link( 'class.'.$class->getId().'.html#class_method_'.$method, $method, 'method' );
					$list	= HtmlElements::unorderedList( $listVars, 2, ['class' => 'varList'] );
					$item	= HtmlElements::ListItem( $link.$list, 1, ['class' => 'methodItem'] );
					$listMethods[]	= $item;
				}
				if( !$listMethods )
					continue;
				$link	= HtmlElements::Link( 'class.'.$class->getId().'.html', $class->getName(), 'class' );
				$list	= HtmlElements::unorderedList( $listMethods, 1, ['class' => 'methodList'] );
				$item	= HtmlElements::ListItem( $link.$list, 0, ['class' => 'class'] );
				$classList[$class->getName()]	= $item;
			}
		}
		ksort( $classList );
		if( $count ){
			$this->verboseCreation( 'unusedVariables' );

			$words	= $this->env->words['unusedVariables'] ?? [];
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> 'unusedVariables',
				'id'		=> 'info-unusedVariables',
				'topic'		=> $words['heading'] ?? 'unusedVariables',
				'content'	=> '<div id="tree">'.HtmlElements::unorderedList( $classList ).'</div>',
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/unusedVariables';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "unusedVariables.html", $content );
			$this->appendLink( 'unusedVariables.html', 'unusedVariables', $count );
		}
		return $count > 0;
	}
}
