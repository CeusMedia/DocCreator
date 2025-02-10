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
 *	...
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2025 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class MethodOrder extends SiteInfoAbstraction
{
	/**
	 *	Creates Change Log Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 *	@todo		support Interfaces, too
	 */
	public function createSite(): bool
	{
		$count		= 0;
		$list		= [];
		foreach( $this->env->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
				$actual		= [];
				$ordered	= [];
				foreach( $class->getMethods( FALSE ) as $methodName => $method ){
					switch( $method->getAccess() ){
						case 'protected':
							$prefix	= '_2';
							break;
						case 'private':
							$prefix	= '_3';
							break;
						case 'public':
						default:
							$prefix	= '_1';
							break;
					}
					$prefix	.= $method->isStatic() ? '_1|' : '_2|';
					$ordered[]	= $prefix.$methodName;
					$actual[]	= $methodName;
				}
				natCaseSort( $ordered );
				$ordered	= array_map( static function( string $item ): string{
					return substr( $item, 5 );
				}, $ordered );
				if( array_values( $actual ) == array_values( $ordered ) )
					continue;
				do{
					$line1 = array_shift( $actual );
					$line2 = array_shift( $ordered );
					if( $line1 != $line2 )
						break;
				}
				while( count( $actual ) && count( $ordered ) );
				$count++;
				$link	= HtmlElements::Link( 'class.'.$class->getId().'.html', $class->getNamespacedName(), 'class' );
				$label	= $link.": ".$line1." | ".$line2;
				$list[$class->getName()]	= HtmlElements::ListItem( $label, 0, ['class' => 'class'] );
			}
		}
		ksort( $list );
		if( !$count )
			return FALSE;

		$this->verboseCreation( 'methodOrder' );

		$words	= $this->env->words['methodOrder'] ?? [];
		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'key'		=> 'methodOrder',
			'id'		=> 'info-methodOrder',
			'topic'		=> $words['heading'] ?? 'methodOrder',
			'content'	=> HtmlElements::unorderedList( $list ),
			'words'		=> $words,
			'footer'	=> $this->buildFooter(),
		);
		$template	= 'site/info/methodOrder';
		$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
		$content	= $this->loadTemplate( $template, $uiData );
		$this->saveFile( "methodOrder.html", $content );
		$this->appendLink( 'methodOrder.html', 'methodOrder', $count );
		return TRUE;
	}
}
