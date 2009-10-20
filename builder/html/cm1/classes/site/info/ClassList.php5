<?php
/**
 *	Builds Class List Info Site File.
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
 *	@version		$Id: ClassList.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 */
import( 'builder.html.cm1.classes.site.info.Abstract' );
/**
 *	Builds Class List Info Site File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: ClassList.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_Info_ClassList extends Builder_HTML_CM1_Site_Info_Abstract
{
	public $linkTarget = 'content';
	

	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function createSite()
	{
		if( $this->env->verbose )
			remark( "Creating Site: Class List" );
		$uiData	= array(
			'words'		=> $this->env->words['classList'],
			'list'		=> $this->buildClassList(),
		);
		$content	= $this->env->loadTemplate( "site/info/classList", $uiData );
		$this->saveFile( "classes.html", $content );
		$this->appendLink( 'classes.html', 'classList' );
	}

	private function buildClassList()
	{
		$list	= array();
		foreach( $this->env->data->getFiles() as $fileId=> $file )
		{
			foreach( $file->getClasses() as $classId=> $class )
			{
				$uri	= "class.".$classId.".html";
				$label	= $class->getName();
				$list[$label.time()]	= '<div class="file"><a href="'.$uri.'" target="'.$this->linkTarget.'">'.$label.'</a></div>';
			}
		}
		ksort( $list );
		$last		= "";
		$letters	= array();
		$lines		= array();
		foreach( $list as $key => $item )
		{
			if( $last != $key[0] )
			{
				$letters[]	= $key[0];
				$lines[]	= '<div style="clear: left"></div><div class="letter" id="letter-'.$key[0].'">'.$key[0].'</div>';
			}
			$lines[]	= $item;
			$last		= $key[0];
		}
		
		$list	= array();
		for( $i=65; $i<91; $i++ )
		{
			$letter	= chr( $i );
			if( in_array( $letter, $letters ) )
				$list[]	= '<a href="#letter-'.$letter.'">'.$letter.'</a>&nbsp';
			else
				$list[]	= '<span class="letter-disabled">'.$letter.'</span>&nbsp';
		}
		$letters	= '<div id="list-letters">'.implode( $list ).'</div>';
		

		$list	= implode( "\n", $lines ).'<div style="clear: both"></div>';
		return $letters.$list;
	}
}
?>