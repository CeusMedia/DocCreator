<?php
/**
 *	Abstraction of creator classes for builders.
 *
 *	Copyright (c) 2015-2023 Christian Würker (ceusmedia.de)
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
 *	@package		CeusMedia_DocCreator_Builder
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder;

use CeusMedia\Common\FS\Folder\RecursiveIterator as RecursiveFolderIterator;
use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\DocCreator\Core\Environment as Environment;
use CeusMedia\DocCreator\Builder\HTML\Abstraction as AbstractHtmlBuilder;
use RuntimeException;

/**
 *	Abstraction of creator classes for builders.
 *	To construct you own builder you will need to extend this as Creator.php5 in your builder folder.
 *  Command your builder actions on a (protected) method __onConstruct(), which is called on construction.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder
 *	@uses			Folder_RecursiveIterator
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2015-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
abstract class Abstraction
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment Object
	 *	@param		XmlElement		$builder		XML Node from Config of Builder to apply
	 *	@return		void
	 */
	public function __construct( Environment $env, XmlElement $builder )
	{
		$this->env		= $env;
		$this->config	= $this->env->config;
		$this->env->openBuilder( $builder );
		$this->env->load();
		$this->pathTarget	= $this->env->getBuilderTargetPath();
		$this->pathTheme	= $this->env->getBuilderThemePath();
		if( !file_exists( $this->pathTarget ) )
			mkDir( $this->pathTarget, 0775, TRUE );
		$this->__onConstruct();
	}

	abstract protected function __onConstruct();

	protected function copyResourcesRecursive( string $pathSource, string $pathTarget, string $label )
	{
		$pathSource	= $pathSource;
		$pathTarget	= $this->pathTarget.$pathTarget;
		if( !file_exists( $pathTarget ) )
			mkDir( $pathTarget, 0775, TRUE );

		AbstractHtmlBuilder::removeFiles( $pathTarget, '/^.+$/' );							// remove formerly copied resource files

		$index	= new RecursiveFolderIterator( $pathSource );
		$length	= strlen( $pathSource );
		if( $this->env->verbose )
			$this->env->out->sameLine( "Copying ".$label );
		foreach( $index as $entry ){
			$name	= substr( $entry->getPathname(), $length );
			if( $entry->isFile() ){
				if( preg_match( "@\.skip@i", $entry->getPathname() ) )
					continue;
				if( !@copy( $entry->getPathname(), $pathTarget.$name ) )
					throw new RuntimeException( 'File "'.$entry->getPathname().'" could not be copied to "'.$pathTarget.$name.'"' );
			}
			else if( $entry->isDir() && !file_exists( $pathTarget.$name ) )
				mkDir( $pathTarget.$name );
		}
	}
}
