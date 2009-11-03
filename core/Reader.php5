<?php
/**
 *	Recursive PHP File Reader for storing parsed Data.
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
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Reader.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
import( 'de.ceus-media.file.php.Lister' );
import( 'de.ceus-media.alg.time.Clock' );
import( 'de.ceus-media.alg.StringTrimmer' );
import( 'de.ceus-media.adt.php.Container' );
require_once( dirname( __FILE__ ).'/Parser.php5' );
/**
 *	Recursive PHP File Reader for storing parsed Data.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@uses			File_PHP_Lister
 *	@uses			Alg_Time_Clock
 *	@uses			Alg_StringTrimmer
 *	@uses			ADT_PHP_Container
 *	@uses			DocCreator_Core_Parser
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Reader.php5 739 2009-10-22 03:49:27Z christian.wuerker $
 */
class DocCreator_Core_Reader
{
	protected $config			= NULL;
	protected $path				= "";
	protected $plugins			= array();
	protected $verbose			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Configuration	$config		Configuration Array Object
	 *	@param		bool							$verbose	Flag: show Pregress in Console.
	 *	@return		void
	 */
	public function __construct( DocCreator_Core_Configuration $config, $verbose = TRUE )
	{
		$this->config	= $config;
		$this->verbose	= $verbose;
		$this->registerPlugins();
		set_time_limit( $this->config->getTimeLimit() );
	}

	/**
	 *	Reads all files within a Folder and stored parsed Data..
	 *	@access		protected
	 *	@param		ADT_PHP_Container	$data		Object containing collected Class Data
	 *	@param		XML_Element			$project	XML Element of Project from Configuration
	 *	@return		void
	 */
	protected function listClassFiles( ADT_PHP_Container $data, XML_Element $project )
	{
		$pathSource		= $this->config->getProjectPath( $project );

		$ignoreFiles	= $this->config->getProjectIgnoreFiles( $project );
		$ignoreFolders	= $this->config->getProjectIgnoreFolders( $project );
		$extensions		= $this->config->getProjectExtensions( $project );

		$sources	= explode( ",", $pathSource );
		foreach( $sources as $pathSource )
		{
			if( !file_exists( $pathSource ) )
				throw new RuntimeException( 'Source path "'.$pathSource.'" is not existing' );
			$lister		= new File_PHP_Lister( $pathSource, $extensions, $ignoreFolders, $ignoreFiles, FALSE );

			foreach( $lister as $entry )
			{
#				if( !preg_match( "@^[A-Z]@", $entry->getFilename() ) )
#					continue;

				$fileName	= str_replace( "\\", "/", basename( $entry->getPathname() ) );
				$pathName	= dirname( str_replace( "\\", "/", $entry->getPathname() ) )."/";
				$innerPath	= substr( $pathName, strlen( $pathSource ) );			//  get inner Path Name

				if( $this->verbose )
				{
					$filePath	= $innerPath.$fileName;								//  get full File Path
					$fileLabel	= Alg_StringTrimmer::trimCentric( $filePath, 60 );	//  trim File Label
					remark( "Parsing: ".$fileLabel );
				}
#				ob_start();
				$clock	= new Alg_Time_Clock();										//  setup Clock
				$parser	= new DocCreator_Core_Parser();								//  setup Parser

				$file	= $parser->parseFile( $entry->getPathname(), $pathSource );	//  parse File and return Data Object
				$file->errors			= ob_get_clean();							//  store Parser Errors
				$file->time['parse']	= $clock->stop( 6, 0 );						//  store time needed
				$data->setFile( $file->getId(), $file );							//  store File in Data Container
			}
		}
	}

	public function readFiles()
	{
		$data	= new ADT_PHP_Container;												//  init Data Container Object
		$clock	= new Alg_Time_Clock();												//  start outer Clock

		//  --  READ FILES  --  //		
		$clock2	= new Alg_Time_Clock();												//  start inner Clock
		foreach( $this->config->getProjects() as $project )
		{
			$this->listClassFiles( $data, $project );
			$this->setDefaultCategoryAndPackage( $data, $project );
		}
		$data->indexClasses();														//  create class index in container
		$data->timeParse	= $clock2->stop( 6, 0 );								//  note needed time

		//  --  APPLY PLUGINS  --  //		
		$clock2	= new Alg_Time_Clock();												//  start inner Clock
		foreach( $this->plugins as $pluginName => $plugin )							//  iterate registered Plugins
		{
			remark( "Plugin: ".$pluginName );
			$plugin->extendData( $data );											//  apply plugin
		}
		$data->timeRelations	= $clock2->stop( 6, 0 );							//  note needed time

		//  --  SAVE DATA  --  //		
		$data->timeTotal	= $clock->stop( 6, 0 );									//  stop outer Clock
		return $data;
	}

	protected function registerPlugins()
	{
		foreach( $this->config->getReaderPlugins() as $pluginName )
		{
			$pluginName	= trim( $pluginName );
			$classKey	= 'reader.plugin.'.$pluginName;
			$className	= 'Reader_Plugin_'.$pluginName;
			import( $classKey );
			$plugin		= new $className( $this->config, $this->verbose );
			$this->plugins[$pluginName]	= $plugin;
		}
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		ADT_PHP_Container	$data
	 *	@param		XML_Element			$project
	 *	@return		void
	 */
	protected function setDefaultCategoryAndPackage( $data, XML_Element $project )
	{
		$category	= $project->category->default->getValue();
		$package	= $project->package->default->getValue();

		foreach( $data->getFiles() as $file )
		{
			if( !$file->getCategory() )
				$file->setCategory( $category );
			if( !$file->getPackage() )
				$file->setPackage( $package );
			foreach( $file->getClasses() as $class )
			{
				if( !$class->getCategory() )
					$class->setCategory( $category );
				if( !$class->getPackage() )
					$class->setPackage( $package );
			}
		}
	}
}
?>