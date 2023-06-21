<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Reads Configuration XML File.
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
 *	@package		CeusMedia_DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

namespace CeusMedia\DocCreator\Core;

use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\Common\XML\ElementReader as XmlElementReader;
use Exception;
use RuntimeException;

/**
 *	Reads Configuration XML File.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@todo			Code Doc
 */
class Configuration
{
	/**	@var		XmlElement		$data			XML root node of config XML file */
	public XmlElement $data;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string			$fileName		Name of Configuration XML File
	 *	@return		void
	 *	@throws		RuntimeException
	 *	@throws		Exception
	 */
	public function __construct( string $fileName )
	{
		$reader		= new XmlElementReader();
		$this->data	= $reader->readFile( $fileName );
	}

	/**
	 *	Returns Name of Archive File Name.
	 *	@access		public
	 *	@return		string|NULL			Relative Name of Archive File
	 */
	public function getArchiveFileName(): ?string
	{
		foreach( $this->data->creator->file as $file )
			if( $file->getAttribute( 'name' ) == "archive" )
				return $this->getTempPath()."/".$file->getValue();
		return NULL;
	}

	/**
	 *	Returns Path to Documents to read for a given Builder Node.
	 *	@access		public
	 *	@param		XmlElement		$builder		Builder Node from XML File
	 *	@return		string|NULL
	 */
	public function getBuilderDocumentsPath( XmlElement $builder ): ?string
	{
		foreach( $builder->path as $path )
			if( $path->getAttribute( 'type' ) == "documents" )
				return preg_replace( "@^\[/path/to/DocCreator/\]@", "", $path->getValue() );
		return NULL;
	}

	public function getBuilderLogo( XmlElement $builder ): object
	{
		$logo		= (object) [
			'source'	=> NULL,
			'title'		=> NULL,
			'link'		=> NULL
		];
		if( isset( $builder->logo ) ){
			$logo->source = $builder->logo->getValue();
			if( $builder->logo->hasAttribute( 'title' ) )
				$logo->title = $builder->logo->getAttribute( 'title');
			if( $builder->logo->hasAttribute( 'href' ) )
				$logo->link = $builder->logo->getAttribute( 'href');
		}
		return $logo;
	}

	/**
	 *	Returns XML Element with one or more Builder Option Nodes of a give Builder Node.
	 *	@access		public
	 *	@param		XmlElement		$builder		Builder Node from XML File
	 *	@return		array<XmlElement>
	 */
	public function getBuilderOptions( XmlElement $builder ): array
	{
		$list	= [];
		foreach( $builder->children() as $child )
			if( 'option' === $child->getName() )
				$list[]	= $child;
		return $list;
	}

	/**
	 *	Returns XML Element with one or more Builder Plugin Nodes of a give Builder Node.
	 *	@access		public
	 *	@param		XmlElement		$builder		Builder Node from XML File
	 *	@return		array<XmlElement>
	 */
	public function getBuilderPlugins( XmlElement $builder ): array
	{
		$list	= [];
		foreach( $builder->children() as $child )
			if( 'plugin' === $child->getName() )
				$list[]	= $child;
		return $list;
	}

	/**
	 *	Returns XML Element with one or more Builder Nodes.
	 *	@access		public
	 *	@return		array<XmlElement>
	 */
	public function getBuilders(): array
	{
		$list	= [];
		foreach( $this->data->children() as $child )
			if( 'builder' === $child->getName() )
				$list[]	= $child;
		return $list;
	}

	/**
	 *	Returns Path to read created Files within for a given Builder Node.
	 *	@access		public
	 *	@param		XmlElement		$builder		Builder Node from XML File
	 *	@return		string|NULL
	 */
	public function getBuilderTargetPath( XmlElement $builder ): ?string
	{
		foreach( $builder->path as $path ){
			if( $path->getAttribute( 'type' ) == "target" ){
//				remark("getBuilderTargetPath: ".$path->getValue());
				$path	= preg_replace( "@^\[/path/to/DocCreator/\]@", "", $path->getValue() );
				return str_replace( array( "[", "]" ), "", $path );
			}
		}
		return NULL;
	}

	/**
	 *	Returns File URI of Error Log.
	 *	@access		public
	 *	@return		string|NULL
	 */
	public function getErrorLogFileName(): ?string
	{
		foreach( $this->data->creator->file as $file )
			if( $file->getAttribute( 'name' ) == "error" )
				return $this->getLogPath()."/".$file->getValue();
		return NULL;
	}

	/**
	 *	Returns path to store log files in.
	 *	Defaults "log/" if not set in project configuration.
	 *	@access		public
	 *	@return		string		Configured path to log files, default: log/
	 */
	public function getLogPath(): string
	{
		foreach( $this->data->creator->path as $path )
			if( $path->getAttribute( 'name' ) == "log" )
				return $path->getValue();
		return "log/";
	}

	/**
	 *	Returns Mail Address of Error Mail Receiver if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getMailReceiver(): string
	{
		return $this->data->creator->mail->getValue();
	}

	/**
	 *	Returns List of File Extensions which should be read by Parser for a given Project Node.
	 *	@access		public
	 *	@param		XmlElement		$project		Project Node from XML File
	 *	@return		array
	 */
	public function getProjectExtensions( XmlElement $project ): array
	{
		$list	= [];
		foreach( $project->extension as $extension )
			$list[]	= $extension->getValue();
		return $list;
	}

	public function getProjectForcedCategory( XmlElement $project ): ?string
	{
		if( $project->category->hasAttribute( "by" ) )
			if( $project->category->getAttribute( "by" ) == "force" )
				return $project->category->getValue();
		return NULL;
	}

	public function getProjectForcedPackage( XmlElement $project ): ?string
	{
		if( $project->package->hasAttribute( "by" ) )
			if( $project->package->getAttribute( "by" ) == "force" )
				return $project->package->getValue();
		return NULL;
	}

	/**
	 *	Returns List of regex patterns of Files which should be ignored by Parser for a given Project Node.
	 *	@access		public
	 *	@param		XmlElement		$project		Project Node from XML File
	 *	@return		array
	 */
	public function getProjectIgnoreFiles( XmlElement $project ): array
	{
		$list	= [];
		foreach( $project->ignore as $ignore )
			if( $ignore->getAttribute( 'type' ) === "file" )
				$list[]	= $ignore->getValue();
		return $list;
	}

	/**
	 *	Returns List of regex patterns of Folders which should be ignored by Parser for a given Project Node.
	 *	@access		public
	 *	@param		XmlElement		$project		Project Node from XML File
	 *	@return		array
	 */
	public function getProjectIgnoreFolders( XmlElement $project ): array
	{
		$list	= [];
		foreach( $project->ignore as $ignore )
			if( $ignore->getAttribute( 'type' ) === "folder" )
				$list[]	= $ignore->getValue();
		return $list;
	}

	/**
	 *	Returns Path to for Parser to find Classes of a given Project Node.
	 *	@access		public
	 *	@param		XmlElement		$project		Project Node from XML File
	 *	@return		string
	 */
	public function getProjectPath( XmlElement $project ): string
	{
		$path	= preg_replace( "@^\[/path/to/DocCreator/\]@", "", $project->path->getValue() );
		return str_replace( array( "[", "]" ), "", $path );
	}

	/**
	 *	Returns XML Element with one or more Project Nodes.
	 *	@access		public
	 *	@return		array<XmlElement>
	 */
	public function getProjects(): array
	{
		$list	= [];
		foreach( $this->data->children() as $child )
			if( 'project' === $child->getName() )
				$list[]	= $child;
		return $list;
	}

	/**
	 *	Returns List of Reader Plugins which should be applied after parsing.
	 *	@access		public
	 *	@return		array
	 */
	public function getReaderPlugins(): array
	{
		$list	= [];
		foreach( $this->data->reader->plugin as $plugin )
			$list[]	= $plugin->getValue();
		return $list;
	}

	public function getSerialFileName(): ?string
	{
		foreach( $this->data->creator->file as $file )
			if( $file->getAttribute( 'name' ) == "serial" )
				return $this->getTempPath()."/".$file->getValue();
		return NULL;
	}

	public function getSkip( string $type ): ?bool
	{
		$node	= $this->data->creator->skip;
		if( !$node->hasAttribute( $type ) )
			return NULL;
		$value	= $node->getAttribute( $type );
		switch( strtoupper( $value ) )
		{
			case 'FALSE':	return FALSE;
			case 'TRUE':	return TRUE;
		}
		return $value;
	}

	/**
	 *	Returns path to store temporary files in.
	 *	Defaults "tmp/" if not set in project configuration.
	 *	@access		public
	 *	@return		string		Configured path to log files, default: tmp/
	 */
	public function getTempPath(): string
	{
		foreach( $this->data->creator->path as $path )
			if( $path->getAttribute( 'name' ) == "temp" )
				return $path->getValue();
		return "tmp/";
	}

	/**
	 *	Return maximum execution time in seconds.
	 *	@access		public
	 *	@return		integer 	Configured seconds, default: -1
	 */
	public function getTimeLimit(): int
	{
		foreach( $this->data->creator->limit as $limit )
			if( $limit->getAttribute( 'name' ) == "time" )
				return $limit->getValue();
		return -1;
	}

	public function getTrace(): bool
	{
		return (boolean) $this->getVerbose( "trace" );
	}

	public function getVerbose( ?string $type = NULL ): ?bool
	{
		$type	= $type ?: "general";
		$node	= $this->data->creator->verbose;
		if( !$node->hasAttribute( $type ) )
			return NULL;
		$value	= $node->getAttribute( $type );
		switch( strtoupper( $value ) ){
			case 'FALSE':	return FALSE;
			case 'TRUE':	return TRUE;
		}
		return $value;
	}

	public function setBuilderTargetPath( string $path = NULL ): self
	{
//		remark( "setBuilderTargetPath: ".$path );
		foreach( $this->data->builder as $builder ){
			foreach( $builder->path as $builderPath ){
				$value	= $builderPath->getValue();
				if( $path === NULL )
					$value = str_replace( array( "[", "]" ), "", $value );
				else if( preg_match( "/\[.*\]/", $value ) )
					$value	= preg_replace( "/\[.*\]/", $path, $value );
				else
					$value	= $path;
				$builderPath->setValue( $value );
			}
		}
		return $this;
	}

	public function setProjectBasePath( string $path = NULL ): self
	{
		foreach( $this->data->project as $project ){
			$value	= (string) $project->path;
			if( $path === NULL )
				$value = str_replace( array( "[", "]" ), "", $value );
			else if( preg_match( "/\[.*\]/", $value ) )
				$value	= preg_replace( "/\[.*\]/", $path, $value );
			else
				$value	= $path;
			$project->path->setValue( $value );
		}
		return $this;
	}

	public function setSkip( string $type, bool $value ): self
	{
		$node	= $this->data->creator->skip;
		$node[$type]	= $value;
		return $this;
	}

	/**
	 *	Sets number of maximum seconds of execution.
	 *	@access		public
	 *	@param		int			$seconds		Number of seconds
	 *	@return		self
	 */
	public function setTimeLimit( int $seconds ): self
	{
		$this->data->creator->setAttribute( 'timelimit', $seconds );
		return $this;
	}

	/**
	 *	Switches display of exception trace on error.
	 *	@access		public
	 *	@param		bool		$boolean		Flag: show exception trace on error
	 *	@return		self
	 */
	public function setTrace( bool $boolean ): self
	{
		$this->setVerbose( 'trace', $boolean );
		return $this;
	}

	public function setVerbose( string $type, bool $value ): self
	{
		$type	= $type ?: "general";
		$node	= $this->data->creator->verbose;
		$node[$type]	= $value;
		return $this;
	}
}
