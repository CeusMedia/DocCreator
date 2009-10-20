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
class Builder_HTML_CM1_Site_Info_MethodAccess extends Builder_HTML_CM1_Site_Info_Abstract
{
	protected $key		= 'methodAccess';
	/**
	 *	Creates Change Log Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		$count		= 0;
		$classList	= array();
		foreach( $this->env->data->getFiles() as $file )
		{
			foreach( $file->getClasses() as $class )
			{
				$methodList	= array();
				foreach( $class->getMethods() as $method )
				{
					if( !$method->getAccess() )
					{
						$count++;
						$methodList[]	= UI_HTML_Elements::ListItem( $method->getName(), 1, array( 'class' => 'methodItem' ) );
					}
				}
				if( !$methodList )
					continue;
				$methodList		= UI_HTML_Elements::unorderedList( $methodList, 1, array( 'class' => 'methodList' ) );
				$classList[]	= UI_HTML_Elements::ListItem( $class->getName().$methodList, 0, array( 'class' => 'classItem' ) );
			}
		}
		if( !$classList )
			return FALSE;
		
		$this->verboseCreation( $this->key );

		$words	= isset( $this->env->words[$this->key] ) ? $this->env->words[$this->key] : array();
		$uiData	= array(
			'key'		=> $this->key,
			'id'		=> 'info-'.$this->key,
			'title'		=> isset( $words['heading'] ) ? $words['heading'] : $this->key,
			'content'	=> UI_HTML_Elements::unorderedList( $classList ),
			'words'		=> $words,
		);
		$template	= 'site/info/'.$this->key;
		$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
		$content	= $this->loadTemplate( $template, $uiData );
		$this->saveFile( $this->key.'.html', $content );
		$this->appendLink( $this->key.'.html', $this->key, $count );
		return TRUE;
	}
}
?>