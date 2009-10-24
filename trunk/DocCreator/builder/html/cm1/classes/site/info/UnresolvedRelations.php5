<?php
/**
 *	...
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
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Deprecations.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'de.ceus-media.alg.UnusedVariableFinder' );
import( 'builder.html.cm1.classes.site.info.Abstract' );
/**
 *	Builds Deprecation Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Site_Info_Abstract
 *	@uses			Alg_UnusedVariableFinder
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Deprecations.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 *	@todo			Code Doc
 */
class Builder_HTML_CM1_Site_Info_UnresolvedRelations extends Builder_HTML_CM1_Site_Info_Abstract
{
	protected $count		= 0;

	/**
	 *	Creates Deprecation Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		$content	= "";
		$classList	= array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$list	= array();
				$this->checkUnresolvedAndAddListItemIfNot( $list, $class->getExtendedClass(), 'extends: ' );
				if( $class instanceof ADT_PHP_Class )
				{
					foreach( $class->getExtendingClasses() as $extendingClass )
						$this->checkUnresolvedAndAddListItemIfNot( $list, $extendingClass, 'extendedBy: ' );
				}
				else																			//  an Interface
				{
					foreach( $class->getExtendingInterfaces() as $extendingInterface )
						$this->checkUnresolvedAndAddListItemIfNot( $list, $extendingInterface, 'extendedBy: ' );
					foreach( $class->getImplementingClasses() as $implementingClass )
						$this->checkUnresolvedAndAddListItemIfNot( $list, $implementingClass, 'implementedBy: ' );
				}
				if( $list )
				{
					$url	= 'class.'.$class->getId().'.html';
					$link	= UI_HTML_Elements::Link( $url, $class->getName() );
					$list	= UI_HTML_Elements::unorderedList( $list, 1 );
					$classList[]	= UI_HTML_Elements::ListItem( $link.$list, 0, array( 'class' => 'class' ) );
				}
			}
		}
		if( $this->count )
		{
			$this->verboseCreation( 'unresolvedRelations' );

			$words	= isset( $this->env->words['unresolvedRelations'] ) ? $this->env->words['unresolvedRelations'] : array();
			$uiData	= array(
				'key'		=> 'unresolvedRelations',
				'id'		=> 'info-unresolvedRelations',
				'title'		=> isset( $words['heading'] ) ? $words['heading'] : 'unresolvedRelations',
				'content'	=> '<div id="tree">'.UI_HTML_Elements::unorderedList( $classList ).'</div>',
				'words'		=> $words,
			);
			$template	= 'site/info/unresolvedRelations';
			$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
			$content	= $this->loadTemplate( $template, $uiData );
			$this->saveFile( "unresolvedRelations.html", $content );
			$this->appendLink( 'unresolvedRelations.html', 'unresolvedRelations', $this->count );
		}
		return (bool) $this->count;
	}

	protected function checkUnresolvedAndAddListItemIfNot( &$list, $relation, $prefix = NULL )
	{
		if( !is_string( $relation ) )
			return;
		$this->count++;
		$list[]	= UI_HTML_Elements::ListItem( $prefix.$relation, 1 );
	}
}
?>