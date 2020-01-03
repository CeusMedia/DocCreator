<?php
/**
 *	Builds Deprecation Info Site File.
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
 *	@version		$Id: Deprecations.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;
/**
 *	Builds Deprecation Info Site File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Deprecations.php5 77 2010-11-23 06:31:24Z christian.wuerker $
 */
class Deprecations extends \CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction
{
	protected $count		= 0;

	/**
	 *	Creates Deprecation Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		$content		= "";
		$deprecations	= array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$classDeprecations	= array();
				$methodDeprecations	= array();

				$classUri	= "class.".$class->getId().".html";
				$classLink	= \UI_HTML_Elements::Link( $classUri, $class->getName(), 'class' );

				if( $class->getDeprecations() )
				{
					foreach( $class->getDeprecations() as $deprecation )
						$classDeprecations[]	= \UI_HTML_Elements::ListItem( $deprecation, 1, array( 'class' => "classItem" ) );
					$this->count	+= count( $classDeprecations );
				}

				foreach( $class->getMethods() as $method )
				{
					if( !$method->getDeprecations() )
						continue;
					$list	= array();
					foreach( $method->getDeprecations() as $deprecation )
						if( trim( $deprecation ) )
							$list[]		= \UI_HTML_Elements::ListItem( $deprecation, 2, array( 'class' => "methodItem" ) );
					$list	= \UI_HTML_Elements::unorderedList( $list, 2, array( 'class' => "methodList" ) );
					$this->count	+= count( $list );
					$methodUrl		= 'class.'.$class->getId().'.html#class_method_'.$method->getName();
					$methodLink		= \UI_HTML_Elements::Link( $methodUrl, $method->getName(), 'method' );
					$methodDeprecations[]	= \UI_HTML_Elements::ListItem( $methodLink.$list, 1, array( 'class' => "method" ) );
				}
				if( !$classDeprecations && !$methodDeprecations )
					continue;

				$methodDeprecations	= \UI_HTML_Elements::unorderedList( $methodDeprecations, 1, array( 'class' => "methods" ) );
				$classDeprecations	= \UI_HTML_Elements::unorderedList( $classDeprecations, 1, array( 'class' => "classList" ) );
				$deprecations[]		= \UI_HTML_Elements::ListItem( $classLink.$classDeprecations.$methodDeprecations, 0, array( 'class' => "class" ) );
			 }
		}
		if( $deprecations )
		{
			$this->verboseCreation( 'deprecations' );

			$words	= isset( $this->env->words['deprecations'] ) ? $this->env->words['deprecations'] : array();
			$uiData	= array(
				'title'		=> $this->env->builder->title->getValue(),
				'key'		=> 'deprecations',
				'id'		=> 'info-deprecations',
				'topic'		=> isset( $words['heading'] ) ? $words['heading'] : 'deprecations',
				'content'	=> \UI_HTML_Elements::unorderedList( $deprecations, 0, array( 'class' => "classes" ) ),
				'words'		=> $words,
				'footer'	=> $this->buildFooter(),
			);
			$template	= 'site/info/deprecations';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "deprecations.html", $content );
			$this->appendLink( 'deprecations.html', 'deprecations', count( $deprecations ) );
		}
		return (bool) count( $deprecations );
	}
}
?>
