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
 *	@version		$Id: Changes.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.site.info.Abstract' );
/**
 *	...
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Changes.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_Info_MethodOrder extends Builder_HTML_CM1_Site_Info_Abstract
{
	/**
	 *	Creates Change Log Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		$count		= 0;
		$content	= "";
		$list		= array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$methods	= array_keys( $class->getMethods( FALSE ) );
				$ordered	= $methods;
				natCaseSort( $ordered );
				if( array_values( $methods ) == array_values( $ordered ) )
					continue;
				do{
					$line1 = array_shift( $methods );
					$line2 = array_shift( $ordered );
					if( $line1 != $line2 )
						break;
				}
				while( count( $methods ) && count( $ordered ) );
				$count++;
				$label	= $class->getName().": ".$line1." | ".$line2;
				$list[]	= UI_HTML_Elements::ListItem( $label );
			}
		}
		if( !$count )
			return FALSE;

		$this->verboseCreation( 'methodOrder' );

		$words	= isset( $this->env->words['methodOrder'] ) ? $this->env->words['methodOrder'] : array();
		$uiData	= array(
			'key'		=> 'methodOrder',
			'id'		=> 'info-methodOrder',
			'title'		=> isset( $words['heading'] ) ? $words['heading'] : 'methodOrder',
			'content'	=> UI_HTML_Elements::unorderedList( $list ),
			'words'		=> $words,
		);
		$template	= 'site/info/methodOrder';
		$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
		$content	= $this->loadTemplate( $template, $uiData );
		$this->saveFile( "methodOrder.html", $content );
		$this->appendLink( 'methodOrder.html', 'methodOrder', $count );
		return TRUE;
	}
}
?>