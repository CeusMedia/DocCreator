<?php
/**
 *	Creates Documentation Sites from Parser Data.
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
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Builder\HTML\Site;

use CeusMedia\DocCreator\Builder\HTML\Abstraction as HtmlBuilderAbstraction;
use CeusMedia\DocCreator\Builder\HTML\Site\Control as SiteControl;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 *	Creates Documentation Sites from Parser Data.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Builder_HTML_Site
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@todo			Code Doc
 */
class Builder extends HtmlBuilderAbstraction
{
	protected array $linkList		= [];

	/**
	 *	Creates several Sites: Index, Todos, Deprecated, ChangeLog, ReadMe and Statistics.
	 *	@access		public
	 *	@return		void
	 *	@throws		RuntimeException
	 *	@throws		ReflectionException
	 */
	public function createSites()
	{
		$pathTarget		= $this->env->getBuilderTargetPath();
		$format			= $this->env->builder->getAttribute( 'format' );

		self::removeFiles( $pathTarget, '/^(([^.]+\.html)|(.+\.svg)|(\.ht.+))$/' );					// remove formerly generated site files

		$plugins	= $this->env->getBuilderPlugins();
		foreach( $plugins as $plugin => $options ){
			$className	= '\\CeusMedia\\DocCreator\\Builder\\'.$format.'\\Site\\Info\\'.$plugin;
			if( !class_exists( $className ) )
				throw new RuntimeException( 'Invalid info site plugin "'.$plugin.'"' );
			$reflection	= new ReflectionClass( $className );
			$arguments	= array( $this->env, NULL, &$this->linkList, $options );
			$builder	= $reflection->newInstanceArgs( $arguments );
#				$builder->setProjectPath( $pathProject );
			$builder->setTargetPath( $pathTarget );
			$builder->createSite();
		}
		$this->createHtaccess( $pathTarget );
		$this->createFrameset( $pathTarget );
		$this->createControl( $pathTarget );
	}

	protected function createControl( $pathTarget )
	{
		$builder	= new SiteControl( $this->env );
		$builder->createControl( $this->linkList );
	}

	protected function createFrameset( string $pathTarget )
	{
		$data	= array(
			'language'	=> $this->env->builder->language->getValue(),
			'title'		=> $this->env->builder->title->getValue(),
		);
		$index		= $this->loadTemplate( "site/frameset", $data );
		file_put_contents( $pathTarget."index.html", $index );
	}

	protected function createHtaccess( string $pathTarget )
	{
		if( $this->env->verbose )
			$this->env->out->newLine( "Creating: .htaccess" );
		$source		= $this->pathTheme.'templates/htaccess';
		$htaccess	= file_get_contents( $source );
		file_put_contents( $pathTarget.".htaccess", $htaccess );
	}
}

