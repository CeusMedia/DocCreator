<?php
/**
 *	Reads Configuration XML File.
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
 *	@package		DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id $
 */
import( 'de.ceus-media.xml.ElementReader' );
/**
 *	Reads Configuration XML File.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@uses			XML_ElementReader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id $
 *	@todo			Code Doc
 */
class DocCreator_Core_Configuration
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string			$fileName		Name of Configuration XML File
	 *	@return		void
	 */
	public function __construct( $fileName )
	{
		$reader		= new XML_ElementReader();
		$this->data	= $reader->readFile( $fileName );
	}

	/**
	 *	Returns Name of Archive File Name.
	 *	@access		public
	 *	@return		string			Relative Name of Archive File
	 */
	public function getArchiveFileName()
	{
		foreach( $this->data->creator->file as $file )
			if( $file->getAttribute( 'type' ) == "archive" )
				return $file->getValue();
	}

	/**
	 *	Returns Path to Documents to read for a given Builder Node.
	 *	@access		public
	 *	@param		XML_Element		$builder		Builder Node from XML File
	 *	@return		string
	 */
	public function getBuilderDocumentsPath( XML_Element $builder )
	{
		foreach( $builder->path as $path )
			if( $path->getAttribute( 'type' ) == "documents" )
				return $path->getValue();
	}

	/**
	 *	Returns XML Element with one or more Builder Plugin Nodes of a give Builder Node.
	 *	@access		public
	 *	@param		XML_Element		$builder		Builder Node from XML File
	 *	@return		XML_Element
	 */
	public function getBuilderPlugins( $builder )
	{
		return $builder->plugins->plugin;
	}

	/**
	 *	Returns XML Element with one or more Builder Nodes.
	 *	@access		public
	 *	@return		XML_Element
	 */
	public function getBuilders()
	{
		return $this->data->builders->builder;
	}

	/**
	 *	Returns Path to read created Files within for a given Builder Node.
	 *	@access		public
	 *	@param		XML_Element		$builder		Builder Node from XML File
	 *	@return		string
	 */
	public function getBuilderTargetPath( XML_Element $builder )
	{
		foreach( $builder->path as $path )
			if( $path->getAttribute( 'type' ) == "target" )
				return $path->getValue();
	}

	/**
	 *	Returns File URI of Error Log.
	 *	@access		public
	 *	@return		string
	 */
	public function getErrorLogFileName()
	{
		foreach( $this->data->creator->file as $file )
			if( $file->getAttribute( 'type' ) == "error" )
				return $file->getValue();
	}

	/**
	 *	Returns Mail Address of Error Mail Receiver if set.
	 *	@access		public
	 *	@return		string
	 */
	public function getMailReceiver()
	{
		return $this->data->creator->mail->getValue();	
	}

	/**
	 *	Returns List of File Extensions which should be read by Parser for a given Project Node.
	 *	@access		public
	 *	@param		XML_Element		$project		Project Node from XML File
	 *	@return		array
	 */
	public function getProjectExtensions( XML_Element $project )
	{
		$list	= array();
		foreach( $project->extensions->extension as $extension )
			$list[]	= $extension->getValue();
		return $list;
	}

	/**
	 *	Returns List of regex patterns of Files which should be ignored by Parser for a given Project Node.
	 *	@access		public
	 *	@param		XML_Element		$project		Project Node from XML File
	 *	@return		array
	 */
	public function getProjectIgnoreFiles( XML_Element $project )
	{
		$list	= array();
		foreach( $project->ignore->file as $file )
			$list[]	= $file->getValue();
		return $list;
	}

	/**
	 *	Returns List of regex patterns of Folders which should be ignored by Parser for a given Project Node.
	 *	@access		public
	 *	@param		XML_Element		$project		Project Node from XML File
	 *	@return		array
	 */
	public function getProjectIgnoreFolders( XML_Element $project )
	{
		$list	= array();
		foreach( $project->ignore->folder as $folder )
			$list[]	= $folder->getValue();
		return $list;
	}

	/**
	 *	Returns Path to for Parser to find Classes of a given Project Node.
	 *	@access		public
	 *	@param		XML_Element		$project		Project Node from XML File
	 *	@return		string
	 */
	public function getProjectPath( XML_Element $project )
	{
		return $project->path->getValue();	
	}

	/**
	 *	Returns XML Element with one or more Project Nodes.
	 *	@access		public
	 *	@return		XML_Element
	 */
	public function getProjects()
	{
		return $this->data->projects->project;
	}

	/**
	 *	Returns List of Reader Plugins which should be applied after parsing.
	 *	@access		public
	 *	@return		array
	 */
	public function getReaderPlugins()
	{
		$list	= array();
		foreach( $this->data->reader->plugin as $plugin )
			$list[]	= $plugin->getValue();
		return $list;
	}

	public function getSerialFileName()
	{
		foreach( $this->data->creator->file as $file )
			if( $file->getAttribute( 'type' ) == "serial" )
				return $file->getValue();
	}

	public function getSkip( $type )
	{
		$node	= $this->data->creator->skip;
		if( !$node->hasAttribute( $type ) )
			return NULL;
		$value	= $node->getAttribute( $type );
		switch( strtoupper( $value ) )
		{
			case 'FALSE':	return FALSE;
			case 'TRUE':	return true;
		}
		return $value;
	}

	public function getTimeLimit()
	{
		return $this->data->creator->timeLimit->getValue();
	}

	public function getVerbose( $type = NULL )
	{
		$type	= $type ? $type : "general";
		$node	= $this->data->creator->verbose;
		if( !$node->hasAttribute( $type ) )
			return NULL;
		$value	= $node->getAttribute( $type );
		switch( strtoupper( $value ) )
		{
			case 'FALSE':	return FALSE;
			case 'TRUE':	return true;
		}
		return $value;
	}

	public function setSkip( $type, $value )
	{
		$value	= $value ? TRUE : FALSE;
		$node	= $this->data->creator->skip;
		$node[$type]	= $value;
	}

	/**
	 *	Sets number of maximum seconds of execution.
	 *	@access		public
	 *	@param		int			$seconds		Number of seconds
	 *	@return		void
	 */
	public function setTimeLimit( $seconds )
	{
		return $this->data->creator->timeLimit->setValue( (int) $seconds );
	}

	public function setVerbose( $type, $value )
	{
		$type	= $type ? $type : "general";
		$value	= $value ? TRUE : FALSE;
		$node	= $this->data->creator->verbose;
		$node[$type]	= $value;
	}
}
?>