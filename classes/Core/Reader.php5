<?php
/**
 *	Recursive PHP File Reader for storing parsed Data.
 *
 *	Copyright (c) 2008-2013 Christian Würker (ceusmedia.de)
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
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Reader.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
/**
 *	Recursive PHP File Reader for storing parsed Data.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@uses			File_PHP_Lister
 *	@uses			Alg_Time_Clock
 *	@uses			Alg_Text_Trimmer
 *	@uses			ADT_PHP_Container
 *	@uses			DocCreator_Core_Parser
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Reader.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 *	@todo			fix error noted in 'setDefaultCategoryAndPackage'
 *	@todo			Code Doc (members)
 */
class DocCreator_Core_Reader{

	protected $config			= NULL;
	protected $path				= "";
	protected $plugins			= array();
	protected $verbose			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		DocCreator_Core_Configuration	$config		Configuration Array Object
	 *	@param		Console_Output					$out		Instance of output handler.
	 *	@param		bool							$verbose	Flag: show Pregress in Console.
	 *	@return		void
	 */
	public function __construct( $env, $verbose = TRUE ){
		$this->env		= $env;
		$this->config	= $env->config;
		$this->verbose	= $verbose;
		$this->registerPlugins();
		set_time_limit( $this->config->getTimeLimit() );
	}

	/**
	 *	Reads all files within a Folder and stored parsed Data..
	 *	@access		protected
	 *	@param		XML_Element			$project	XML Element of Project from Configuration
	 *	@return		void
	 */
	protected function listClassFiles( XML_Element $project ){
		$pathSource		= $this->config->getProjectPath( $project );
		$ignoreFiles	= $this->config->getProjectIgnoreFiles( $project );
		$ignoreFolders	= $this->config->getProjectIgnoreFolders( $project );
		$extensions		= $this->config->getProjectExtensions( $project );

		$sources	= explode( ",", $pathSource );
		foreach( $sources as $pathSource ){
			if( !file_exists( ( $pathSource = strlen( trim( $pathSource ) ) ? $pathSource : "./" ) ) )
				throw new RuntimeException( 'Source path "'.$pathSource.'" is not existing' );
			$lister		= new File_PHP_Lister( $pathSource, $extensions, $ignoreFolders, $ignoreFiles, FALSE );
			$list[$pathSource]	= array();

			foreach( $lister as $entry ){
#				if( !preg_match( "@^[A-Z]@", $entry->getFilename() ) )
#					continue;
				$list[$pathSource][]	= $entry;
			}
		}
		return $list;
	}

	protected function parseFiles( ADT_PHP_Container $data, $project, $list ){
		$sources	= explode( ",", $this->config->getProjectPath( $project ) );
		if( $this->verbose ){
			$count	= 0;
			$total	= 0;
			foreach( $sources as $pathSource ){
				$pathSource = strlen( trim( $pathSource ) ) ? $pathSource : "./";
				$total	= count( $list[$pathSource] );
			}
			$this->env->out->newLine( "Found ".$total." files." );
			$this->env->out->newLine();
		}

		foreach( $sources as $pathSource ){
			$pathSource = strlen( trim( $pathSource ) ) ? $pathSource : "./";
			foreach( $list[$pathSource] as $entry ){
				if( $this->verbose ){
					$count++;
					$fileName	= str_replace( "\\", "/", basename( $entry->getPathname() ) );
					$pathName	= dirname( str_replace( "\\", "/", $entry->getPathname() ) )."/";
					$innerPath	= substr( $pathName, strlen( $pathSource ) );		//  get inner Path Name
					$filePath	= $innerPath.$fileName;								//  get full File Path
					$percentage	= str_pad( round( $count / $total * 100 ), 2, " ", STR_PAD_LEFT );
					$this->env->out->sameLine( "Parsing (".$percentage."%) ".$filePath );
				}
				$clock	= new Alg_Time_Clock();										//  setup Clock
				$parser	= new File_PHP_Parser_Regular();							//  setup Parser

				$file	= $parser->parseFile( $entry->getPathname(), $pathSource );	//  parse File and return Data Object
				$file->errors			= ob_get_clean();							//  store Parser Errors
				$file->time['parse']	= $clock->stop( 6, 0 );						//  store time needed

				$this->realizeCategoryAndPackage( $file, $project );
				$data->setFile( $file->getId(), $file );							//  store File in Data Container
			}
		}
		if( $this->verbose ){
			$this->env->out->sameLine( "Parsing done." );
			$this->env->out->newLine();
		}
	}

	/**
	 *	Reads all suitable Files for all defined Projects, created Indices and applies Reader Plugins.
	 *	@access		public
	 *	@return		ADT_PHP_Container
	 *	@todo		rewrite: create a Container for each Project and merge afterwards, see note in 'setDefaultCategoryAndPackage'
	 */
	public function readFiles(){
		$data	= new ADT_PHP_Container;											//  init Data Container Object
		$clock1	= new Alg_Time_Clock();												//  start outer Clock

		//  --  READ FILES  --  //		
		$clock2	= new Alg_Time_Clock();												//  start inner Clock
		foreach( $this->config->getProjects() as $project ){
			$fileList	= $this->listClassFiles( $project );
			$this->parseFiles( $data, $project, $fileList );
//			$this->setDefaultCategoryAndPackage( $data, $project );
//			$this->setForcedCategoryAndPackage( $data, $project );
		}
		$data->indexClasses();														//  create class index in container
		$data->indexInterfaces();													//  create interface index in container
		$data->timeParse	= $clock2->stop( 6, 0 );								//  note needed time

		//  --  APPLY PLUGINS  --  //		
		$clock2	= new Alg_Time_Clock();												//  start inner Clock
		foreach( $this->plugins as $pluginName => $plugin ){						//  iterate registered Plugins
			$this->env->out->sameLine( "Plugin: ".$pluginName );
			$plugin->extendData( $data );											//  apply plugin
		}
		$this->env->out->sameLine( "Plugins applied." );
		$this->env->out->newLine();
		$data->timeRelations	= $clock2->stop( 6, 0 );							//  note needed time

		//  --  SAVE DATA  --  //
		$data->timeTotal	= $clock1->stop( 6, 0 );									//  stop outer Clock
		return $data;
	}

	protected function realizeCategoryAndPackage( $file, $project ){
		if( isset( $project->category ) ){
			$category = $project->category->getValue();
			if( !$file->getCategory() )
				$file->setCategory( $category );
			foreach( $file->getClasses() as $class )
				if( !$class->getCategory() )
					$class->setCategory( $category );
			foreach( $file->getInterfaces() as $interface )
				if( !$interface->getCategory() )
					$interface->setCategory( $category );
		}
		if( isset( $project->package ) ){
			$package = $project->package->getValue();
			if( !$file->getPackage() )
				$file->setPackage( $package );
			foreach( $file->getClasses() as $class )
				if( !$class->getPackage() )
					$class->setPackage( $package );
			foreach( $file->getInterfaces() as $interface )
				if( !$interface->getPackage() )
					$interface->setPackage( $package );
		}
		if( $category =  $this->env->config->getProjectForcedCategory( $project ) ){
			$file->setCategory( $category );
 			foreach( $file->getClasses() as $class )
				$class->setCategory( $category );
			foreach( $file->getInterfaces() as $interface )
				$interface->setCategory( $category );
		}
		if( $package = $this->env->config->getProjectForcedPackage( $project ) ){
			$file->setPackage( $package );
 			foreach( $file->getClasses() as $class )
				$class->setPackage( $package );
			foreach( $file->getInterfaces() as $interface )
				$interface->setPackage( $package );
		}
	}

	/** 
	 *	Assigns Reader Plugins to be applied after parsing all Projects.
	 *	@access		protected
	 *	@return		void
	 */
	protected function registerPlugins(){
		foreach( $this->config->getReaderPlugins() as $pluginName ){
			$pluginName	= trim( $pluginName );
			$className	= 'DocCreator_Reader_Plugin_'.$pluginName;
			if( !class_exists( $className ) )
				throw new RuntimeException( 'Invalid reader plugin "'.$pluginName.'"' );
			$reflection	= new ReflectionClass( $className );
			$plugin		= $reflection->newInstanceArgs( array( $this->env, $this->verbose ) );
			$this->plugins[$pluginName]	= $plugin;
		}
	}

	/**
	 *	Sets default Category and Package for incomplete Classes and Interfaces found in latest parsed Project Files.
	 *	This is applied after every Project.
	 *	@access		protected
	 *	@param		ADT_PHP_Container	$data			
	 *	@param		XML_Element			$project		Project to get values for
	 *	@return		void
	 *	@todo		There is a possible error if a project does not set defaults but the next project is. Than all incomplete files of the last project will be assigned to the next one. A workaround would be to have several containers which will be merged in the end.
	 */
	protected function setDefaultCategoryAndPackage( $data, XML_Element $project ){
		$category = $project->category->getValue();
		if( $category ){
			foreach( $data->getFiles() as $file ){
				if( !$file->getCategory() )
					$file->setCategory( $category );
 				foreach( $file->getClasses() as $class ){
					remark( 'Category: '.$class->getName().' :: '.$class->getCategory() );
					if( !$class->getCategory() )
						$class->setCategory( $category );
				}
				foreach( $file->getInterfaces() as $interface )
					if( !$interface->getCategory() )
						$interface->setCategory( $category );
			}
		}
		$package = $project->package->getValue();
		if( $package ){
			foreach( $data->getFiles() as $file ){
				if( !$file->getPackage() )
					$file->setPackage( $package );
 				foreach( $file->getClasses() as $class ){
					remark( 'Package: '.$class->getName().' :: '.$class->getPackage() );
					if( !$class->getPackage() )
						$class->setPackage( $package );
				}
				foreach( $file->getInterfaces() as $interface )
					if( !$interface->getPackage() )
						$interface->setPackage( $package );
			}
		}
	}
}
?>
