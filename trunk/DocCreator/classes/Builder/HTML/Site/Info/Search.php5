<?php
/**
 *	Builds Search File.
 *
 *	Copyright (c) 2008 Christian Würker (ceus-media.de)
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
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Search.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
/**
 *	Builds Search File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_Site_Info
 *	@extends		DocCreator_Builder_HTML_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Search.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
class DocCreator_Builder_HTML_Site_Info_Search extends DocCreator_Builder_HTML_Site_Info_Abstract{

	/**
	 *	Builds Tree View.
	 *	@access		public
	 *	@return		string
	 */
	public function createSite(){
		if( $this->env->verbose )
			$this->env->out->sameLine( "Creating site: Search" );
		$this->createTermList( $this->pathTarget );
		$uiData	= array(
			'words'		=> $this->env->words['search'],
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
	private function createTermList( $pathTarget ){
		$data		= array();
		$files		= $this->env->data->getFiles();
		foreach( $files as $fileId => $file )
			foreach( $file->getClasses() as $classId => $class )
				$data[$class->getId()]	= $class->search;
		return File_Writer::save( $pathTarget."terms.serial", serialize( $data ) );
	}
}
?>
