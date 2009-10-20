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
 *	@package		DocCreator
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Reader.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'de.ceus-media.file.php.Lister' );
import( 'de.ceus-media.alg.time.Clock' );
import( 'de.ceus-media.alg.StringTrimmer' );
#import( 'de.ceus-media.file.php.Parser' );
import( 'classes.Parser' );
import( 'model.Container' );
/**
 *	Recursive PHP File Reader for storing parsed Data.
 *	@category		cmTools
 *	@package		DocCreator
 *	@uses			File_PHP_Lisser
 *	@uses			File_PHP_Parser
 *	@uses			Alg_Time_Clock
 *	@uses			Alg_StringTrimmer
 *	@uses			Model_Container
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Reader.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class Reader
{
	protected $config			= NULL;
	protected $path				= "";
	protected $plugins			= array();
	protected $verbose			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		ArrayObject		$config		Configuration Array Object
	 *	@param		bool			$verbose	Flag: show Pregress in Console.
	 *	@return		void
	 */
	public function __construct( ArrayObject $config, $verbose = TRUE )
	{
		$this->config	= $config;
		$this->verbose	= $verbose;
		$this->registerPlugins();
		set_time_limit( $this->config['creator.timeLimit'] );
	}

	protected function registerPlugins()
	{
		$plugins	= explode( ",", $this->config['creator.reader.plugins'] );
		foreach( $plugins as $pluginName )
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
	 *	Reads all files within a Folder and stored parsed Data..
	 *	@access		protected
	 *	@param		Model_Container	$data		Object containing collected Class Data
	 *	@return		void
	 */
	protected function listClassFiles( Model_Container $data )
	{
		$pathSource	= $this->config['project.path.source'];
		$pathTarget	= $this->config['doc.path'];

		$ignoreFiles	= explode( ",", $this->config['project.ignoreFiles'] );
		$ignoreFolders	= explode( ",", $this->config['project.ignoreFolders'] );
		$extensions		= explode( ",", $this->config['project.extensions'] );
		$extensions		= implode( "|", $extensions );

		$sources	= explode( ",", $pathSource );
		foreach( $sources as $pathSource )
		{
			$lister		= new File_PHP_Lister( $pathSource, $ignoreFolders, $ignoreFiles, FALSE );

			foreach( $lister as $entry )
			{
				if( !preg_match( "@\.(".$extensions.")$@", $entry->getFilename() ) )
					continue;
				if( !preg_match( "@^[A-Z]@", $entry->getFilename() ) )
					continue;

				$fileName	= str_replace( "\\", "/", basename( $entry->getPathname() ) );
#				remark( "fileName: ".$fileName );
				$pathName	= dirname( str_replace( "\\", "/", $entry->getPathname() ) )."/";
#				remark( "pathName: ".$pathName );
				$innerPath	= substr( $pathName, strlen( $pathSource ) );			//  get inner Path Name

				if( $this->verbose )
				{
					$filePath	= $innerPath.$fileName;								//  get full File Path
					$fileLabel	= Alg_StringTrimmer::trimCentric( $filePath, 60 );	//  trim File Label
					remark( "Parsing: ".$fileLabel );
				}
				ob_start();
				$clock	= new Alg_Time_Clock();										//  setup Clock
				$parser	= new Parser();												//  setup Parser

				$file	= $parser->parseFile( $entry->getPathname(), $pathSource );	//  parse File and return Data Object
				$file->errors			= ob_get_clean();							//  store Parser Errors
				$file->time['parse']	= $clock->stop( 6, 0 );						//  store time needed

				$data->setFile( $file->getId(), $file );							//  store File in Data Container
			}
		}
	}

	public function readFiles()
	{
		$data	= new Model_Container;												//  init Data Container Object
		$clock	= new Alg_Time_Clock();												//  start outer Clock

		//  --  READ FILES  --  //		
		$clock2	= new Alg_Time_Clock();												//  start inner Clock
		$this->listClassFiles( $data );
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
		$data->save( $this->config );												//  save Data to Serial File
	}
}
?>