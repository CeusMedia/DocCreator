<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Deprecation Info Site File.
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
class Deprecations extends SiteInfoAbstraction
{
	protected int $count		= 0;

	/**
	 *	Creates Deprecation Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite(): bool
	{
		$deprecations	= [];
		foreach( $this->env->data->getFiles() as $file ){
			foreach( $file->getClasses() as $class ){
				$classDeprecations	= [];
				$methodDeprecations	= [];

				$classUri	= "class.".$class->getId().".html";
				$classLink	= HtmlElements::Link( $classUri, $class->getName(), 'class' );

				if( $class->getDeprecations() ){
					foreach( $class->getDeprecations() as $deprecation )
						$classDeprecations[]	= HtmlElements::ListItem( $deprecation, 1, ['class' => "classItem"] );
					$this->count	+= count( $classDeprecations );
				}

				foreach( $class->getMethods() as $method ){
					if( !$method->getDeprecations() )
						continue;
					$list	= [];
					foreach( $method->getDeprecations() as $deprecation )
						if( trim( $deprecation ) )
							$list[]		= HtmlElements::ListItem( $deprecation, 2, ['class' => "methodItem"] );
					$methodList		= HtmlElements::unorderedList( $list, 2, ['class' => "methodList"] );
					$this->count	+= count( $list );
					$methodUrl		= 'class.'.$class->getId().'.html#class_method_'.$method->getName();
					$methodLink		= HtmlElements::Link( $methodUrl, $method->getName(), 'method' );
					$methodDeprecations[]	= HtmlElements::ListItem( $methodLink.$methodList, 1, ['class' => "method"] );
				}
				if( !$classDeprecations && !$methodDeprecations )
					continue;

				$methodDeprecations	= HtmlElements::unorderedList( $methodDeprecations, 1, ['class' => "methods"] );
				$classDeprecations	= HtmlElements::unorderedList( $classDeprecations, 1, ['class' => "classList"] );
				$deprecations[]		= HtmlElements::ListItem( $classLink.$classDeprecations.$methodDeprecations, 0, ['class' => "class"] );
			 }
		}
		if( $deprecations )
		{
			$this->verboseCreation( 'deprecations' );

			$words	= $this->env->words['deprecations'] ?? [];
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> 'deprecations',
				'id'		=> 'info-deprecations',
				'topic'		=> $words['heading'] ?? 'deprecations',
				'content'	=> HtmlElements::unorderedList( $deprecations, 0, ['class' => "classes"] ),
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/deprecations';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "deprecations.html", $content );
			$this->appendLink( 'deprecations.html', 'deprecations', count( $deprecations ) );
		}
		return count( $deprecations ) > 0;
	}
}
