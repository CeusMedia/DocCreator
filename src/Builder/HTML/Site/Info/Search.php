<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Builds Search File.
 *
 *	Copyright (c) 2008-2023 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Site\Info;

use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\DocCreator\Builder\HTML\Site\Info\Abstraction as SiteInfoAbstraction;

/**
 *	Builds Search File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site_Info
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Search extends SiteInfoAbstraction
{
	/**
	 *	Builds Tree View.
	 *	@access		public
	 *	@return		bool
	 */
	public function createSite(): bool
	{
		if( $this->env->verbose )
			$this->env->out->sameLine( "Creating site: Search" );
		$this->createTermList( $this->pathTarget );
		$uiData	= array(
			'title'		=> $this->env->builder->title->getValue(),
			'topics'	=> $this->env->words['search']['heading'],
			'words'		=> $this->env->words['search'],
			'footer'	=> $this->buildFooter(),
		);
		$content	= $this->loadTemplate( "site/info/search", $uiData );
		$this->saveFile( "search.html", $content );

		$template	= $this->pathTheme.'templates/search.php';
		$htaccess	= file_get_contents( $template );
		$this->saveFile( "search.php", $htaccess );
		$this->appendLink( 'search.html', 'search' );
		return TRUE;
	}

	/**
	 *	Collects all Search Terms of all Classes and stores a serialized Term List in Doc Folder.
	 *	This serial will be used by the Search Script, which is triggered by the built HTML Search Form and copied as 'resource'.
	 *	@access		private
	 *	@param		string		$pathTarget			Doc Folder
	 *	@return		integer		Number of saved Bytes
	 */
	private function createTermList( string $pathTarget ): int
	{
		$data		= [];
		$files		= $this->env->data->getFiles();
		foreach( $files as $file )
			foreach( $file->getClasses() as $class )
				$data[$class->getId()]	= $class->search;
		return FileWriter::save( $pathTarget."terms.serial", serialize( $data ), self::FILE_PERMS );
	}
}

