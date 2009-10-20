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
 *	@version		$Id: Search.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 */
/**
 *	Builds Search File.
 *	@category		cmTools
 *	@package		DocCreator_Builder_HTML_CM1_Site_Info
 *	@extends		Builder_HTML_CM1_Site_Info_Abstract
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Search.php5 721 2009-10-20 00:45:13Z christian.wuerker $
 */
class Builder_HTML_CM1_Site_Info_Search extends Builder_HTML_CM1_Site_Info_Abstract
{
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
		);
		$content	= $this->env->loadTemplate( "site/info/search", $uiData );
		$this->saveFile( "search.html", $content );

		$htaccess	= file_get_contents( $this->env->getBuilderPath().'templates/search.php' );
		$this->saveFile( "search.php", $htaccess );
		$this->appendLink( 'search.html', 'search' );
	}

	private function createTermList( $pathTarget )
	{
		$data		= array();
		$files		= $this->env->data->getFiles();
		foreach( $files as $fileId => $file )
		{
			foreach( $file->getClasses() as $classId => $class )
			{
				$data[]	= array(
					'fileId'	=> $fileId,
					'fileName'	=> $file->getPathname(),
					'classId'	=> $classId,
					'className'	=> $class->getName(),
					'terms'		=> $file->search['terms']
				);
			}
		}
		File_Writer::save( $pathTarget."terms.serial", serialize( $data ) );
	}

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
				$terms[]	= '<li>'.$term.'</li>';
#			remark( $filePath.": ".count( $terms ) );

			$terms	= '<ul class="terms">'.join( $terms ).'</ul>';
			$fileId	= $fileData['file']->getId();
			$uri	= "files/".$classKey.".html";
			$label	= $fileData['class']['name'];
			$list[]	= '<div class="file"><a href="'.$uri.'" target="'.$this->linkTarget.'">'.$label.'</a>'.$terms.'</div>';
		}
		$list	= implode( "\n", $list );
		return $list;
	}
}
?>