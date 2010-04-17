<?php
/**
 *	Builds Search File.
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
 *	@version		$Id: Search.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
/**
 *	Builds Search File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Search.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_Info_Search extends Builder_HTML_CM1_Site_Info_Abstract
{
	/**
	 *	@deprecated		not used anymore, since Search is running on Server
	 */
	private function buildTermIndex()
	{
		return;
		$list	= array();
			remark( "Data: ".count( $this->env->data['files'] ) );
		foreach( $this->env->data['files'] as $filePath => $fileData )
		{
			if(!$fileData['class'])
				continue;
			$terms	= array();
			foreach( $fileData['search']['terms'] as $term => $count )
				$terms[]	= UI_HTML_Elements::ListItem( $term );
#			remark( $filePath.": ".count( $terms ) );

			$terms	= UI_HTML_Elements::unorderedList( $terms, 0, array( 'class' => 'terms' ) );
			$fileId	= $fileData['file']->getId();
			$uri	= "files/".$classKey.".html";
			$label	= $fileData['class']['name'];
			$link	= UI_HTML_Elements::Link( $uri, $label, NULL, $this->linkTarget );
			$item	= UI_HTML_Tag::create( 'div', $link.$terms, array( 'class' => 'file' ) );
			$list[]	= $item;
		}
		$list	= implode( "\n", $list );
		return $list;
	}

	/**
	 *	Builds Tree View.
	 *	@access		public
	 *	@return		string
	 */
	public function createSite()
	{
		if( $this->env->verbose )
			remark( "Creating Site: Search" );
		$this->createTermList( $this->pathTarget );
		$uiData	= array(
			'words'		=> $this->env->words['search'],
			'list'		=> $this->buildTermIndex(),
			'footer'	=> $this->buildFooter(),
		);
		$content	= $this->loadTemplate( "site/info/search", $uiData );
		$this->saveFile( "search.html", $content );

		$template	= $this->pathTheme.'templates/search.php';
		$htaccess	= file_get_contents( $template );
		$this->saveFile( "search.php", $htaccess );
		$this->appendLink( 'search.html', 'search' );
	}

	/**
	 *	Collects all Search Terms of all Classes and stores a serialized Term List in Doc Folder.
	 *	This serial will be used by the Search Script, which is triggered by the built HTML Search Form and copied as 'resource'.
	 *	@access		private
	 *	@param		string		$pathTarget			Doc Folder
	 *	@return		int			Number of saved Bytes
	 */
	private function createTermList( $pathTarget )
	{
		$data		= array();
		$files		= $this->env->data->getFiles();
		foreach( $files as $fileId => $file )
			foreach( $file->getClasses() as $classId => $class )
				$data[$class->getId()]	= $class->search;
		return File_Writer::save( $pathTarget."terms.serial", serialize( $data ) );
	}
}
?>