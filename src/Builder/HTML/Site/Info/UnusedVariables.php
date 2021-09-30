<?php
/**
 *	...
 *
 *	Copyright (c) 2008-2020 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: UnusedVariables.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;

use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;

/**
 *	Builds Deprecation Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2021 Christian Würker
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
		$content	= "";
		$finder		= new \Alg_UnusedVariableFinder();
		$classList	= array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$finder->readCode( $file->getSourceCode() );
				$unusedVariables	= $finder->getUnusedVars();
				if( !$unusedVariables )
					continue;

				$listMethods	= array();
				foreach( $unusedVariables as $method => $vars )
				{
					$listVars	= array();
					foreach( $vars as $var )
					{
						$count++;
						$span	= '<span class="var">'.$var.'</span>';
						$item	= \UI_HTML_Elements::ListItem( $span, 2, array( 'class' => "varItem" ) );
						$listVars[]	= $item;
					}
					if( !$listVars )
						continue;
					$link	= \UI_HTML_Elements::Link( 'class.'.$class->getId().'.html#class_method_'.$method, $method, 'method' );
					$list	= \UI_HTML_Elements::unorderedList( $listVars, 2, array( 'class' => 'varList' ) );
					$item	= \UI_HTML_Elements::ListItem( $link.$list, 1, array( 'class' => 'methodItem' ) );
					$listMethods[]	= $item;
				}
				if( !$listMethods )
					continue;
				$link	= \UI_HTML_Elements::Link( 'class.'.$class->getId().'.html', $class->getName(), 'class' );
				$list	= \UI_HTML_Elements::unorderedList( $listMethods, 1, array( 'class' => 'methodList' ) );
				$item	= \UI_HTML_Elements::ListItem( $link.$list, 0, array( 'class' => 'class' ) );
				$classList[$class->getName()]	= $item;
			}
		}
		ksort( $classList );
		if( $count )
		{
			$this->verboseCreation( 'unusedVariables' );

			$words	= isset( $this->env->words['unusedVariables'] ) ? $this->env->words['unusedVariables'] : array();
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> 'unusedVariables',
				'id'		=> 'info-unusedVariables',
				'topic'		=> isset( $words['heading'] ) ? $words['heading'] : 'unusedVariables',
				'content'	=> '<div id="tree">'.\UI_HTML_Elements::unorderedList( $classList ).'</div>',
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
