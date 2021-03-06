<?php
/**
 *	General Runner of DocCreator Application.
 *
 *	Copyright (c) 2008-2020 Christian Würker (ceusmedia.de)
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
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Runner.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
namespace CeusMedia\DocCreator\Core;
/**
 *	General Runner of DocCreator Application.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Core
 *	@uses			File_INI_Reader
 *	@uses			Alg_Time_Clock
 *	@uses			DocCreator_Core_Configuration
 *	@uses			DocCreator_Core_Environment
 *	@uses			DocCreator_Core_Reader
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2020 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: Runner.php5 85 2012-05-23 02:31:06Z christian.wuerker $
 */
class Runner{

	protected $configFile		= NULL;
	protected $configTool		= array();
	protected $configProject	= array();
	protected $pathProject;
	protected $options			= NULL;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string		$configFile		Name of Configuration File, absolute or relative
	 *	@param		bool		$verbose		Flag: show Information
	 *	@param		bool		$trace			Flag: show Exception Trace
	 *	@return		void
	 */
	public function __construct( $configFile, $verbose = NULL, $trace = NULL ){
		$this->loadToolConfig();
		$this->out		= new \CLI_Output();
		if( $configFile )
			$this->setConfigFile( $configFile );
		if( !is_null( $verbose ) )
			$this->setVerbose( $verbose );
		if( !is_null( $trace ) )
			$this->setTrace( $trace );

		@date_default_timezone_set( @date_default_timezone_get() );					//  set default time zone
	}

	/**
	 *	Enable or disable creation of Doc Files (Classes, Interfaces, Packages, Categories).
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Doc Files
	 *	@return		void
	 */
	public function enableCreator( $bool = TRUE ){
		$this->configProject->setSkip( 'creator', !$bool );
	}

	/**
	 *	Enable or disable creation of Info Sites.
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Info Sites
	 *	@return		void
	 */
	public function enableInfo( $bool = TRUE ){
		$this->configProject->setSkip( 'info', !$bool );
	}

	/**
	 *	Enable or disable parsing of Source Files.
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch parsing of Source Files
	 *	@return		void
	 */
	public function enableParser( $bool = TRUE ){
		$this->configProject->setSkip( 'parser', !$bool );
	}

	/**
	 *	Enable or disable creation of Resources (Images, StyleSheets, JavaScripts).
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Resources
	 *	@return		void
	 */
	public function enableResources( $bool = TRUE ){
		$this->configProject->setSkip( 'resources', !$bool );
	}

	/**
	 *	Loads Configuration of Projects from set absolute or relative XML Configuration File.
	 *	@access		protected
	 *	@return		void
	 */
	protected function loadProjectConfig(){
		//  --  LOAD CUSTOM PROJECT CONFIG  --  //
		if( !$this->configFile )
			throw new \RuntimeException( 'No config file set' );
		if( !file_exists( $this->configFile ) )
			throw new \RuntimeException( 'Config file "'.$this->configFile.'" not found' );

		$this->configProject	= new \CeusMedia\DocCreator\Core\Configuration( $this->configFile );
		$this->env				= new \CeusMedia\DocCreator\Core\Environment( $this->configProject, $this->configTool, $this->out );
		if( $this->configProject->getTimeLimit() >= 0 )
			set_time_limit( $this->configProject->getTimeLimit() );
		$this->setVerbose( $this->configProject->getVerbose() );
	}

	/**
	 *	Loads Configuration of DocCreator itself.
	 *	@access		protected
	 *	@return		void
	 */
	protected function loadToolConfig(){
		$uri	= dirname( dirname( dirname( __FILE__ ) ) )."/config/config.ini";
		if( !file_exists( $uri ) )
			throw new \RuntimeException( 'No tool config file given' );
		$this->configTool	= parse_ini_file( $uri, TRUE );
	}

	public function main(){
		if( !$this->configFile )
			throw new \RuntimeException( "No config file set." );

		$this->pathTool	= dirname( dirname( dirname( __FILE__ ) ) );
//		try{
			$clock		= new \Alg_Time_Clock;
			if( $this->configProject->getVerbose() ){
				$this->out->newLine( $this->configTool['application']['name']." v".$this->configTool['application']['version'] );
				$this->out->newLine();
				$this->out->newLine( "Project Config: ".$this->configFile );
#				if( $this->getOption( 'showConfig' ) )
#					$this->showConfig();
			}

			if( $this->configProject->getSkip( 'parser' ) ){
				if( $this->configProject->getVerbose( 'skip' ) )
					$this->out->newLine( 'Skip: Parser + Reader + Reader Plugins' );
			}
			else{
				$doc	= new \CeusMedia\DocCreator\Core\Reader( $this->env, $this->configProject->getVerbose() );
				$data	= $doc->readFiles();
				$this->env->saveContainer( $data );												//  save Data to Serial File
			}

			$this->out->newLine();
			$this->runCreator();
			$usage	= getrusage();
			$this->out->newLine( "Done in ".$clock->stop( 0, 1 )." seconds (".round( $usage["ru_utime.tv_usec"] / 1000000, 1 ).")" );
			$this->out->newLine();

#			if( !empty( $this->configProject['quite'] ) )
#				ob_get_clean();
/*		}
		catch( Exception $e ){
			if( !$this->configProject->getTrace() ){
				print "Error: ".$e->getMessage() . PHP_EOL;
			}
			else{
				$trace	= $e->getTraceAsString();
				$trace	= preg_replace( '/#([0-9])+ /', "#\\1\t", $trace );
				$trace	= str_replace( "): ", "):\n\t", $trace );
				print "Exception: ".$e->getMessage() . PHP_EOL;
				print "File: ".$e->getFile() . PHP_EOL;
				print "Line: ".$e->getLine() . PHP_EOL;
				print "Trace:\n".$trace . PHP_EOL;
			}
		}*/
	}

	/**
	 *	Start creation of Doc Files applying one or more Builders to Data Container.
	 *	@access		protected
	 *	@return		void
	 *	@throws		RuntimeException if a Builder Class is not existing
	 */
	protected function runCreator(){
		foreach( $this->configProject->getBuilders() as $builder ){
			$format		= $builder->getAttribute( 'format' );
			$className	= '\\CeusMedia\\DocCreator\\Builder\\'.$format.'\\Creator';
			if( !class_exists( $className ) )
				throw new \RuntimeException( 'Builder class "'.$className.'" is not existing' );
			new $className( $this->env, $builder, $this->configProject->getVerbose() );
		}
	}

	public function setBuilderTargetPath( $path = NULL ){
		$this->configProject->setBuilderTargetPath( $path );
	}

	/**
	 *	Set an other XML Configuration File and load it.
	 *	@access		public
	 *	@param		string		$configFile		URI of XML Configuration File
	 *	@return		void
	 */
	public function setConfigFile( $configFile ){
		$this->configFile	= $configFile;
		$this->loadProjectConfig();
	}

	public function setErrorMail( $mail ){
		$this->configProject->setMailReceiver( $mail );
	}

	public function setProjectBasePath( $path ){
		$this->configProject->setProjectBasePath( $path );
	}

#	public function setErrorLog( $fileName ){
#		$this->setOption( 'file.log.error', $fileName );
#	}

#	public function setOption( $key, $value ){
#		$this->configProject['creator.'.$key]	= $value;
#	}

	public function setQuite(){
		$this->setVerbose( FALSE );
	}

	public function setTrace(){
		$this->configProject->setTrace( TRUE );
	}

	public function setVerbose( $bool = TRUE ){
		$this->configProject->setVerbose( 'general', $bool );
	}
}
?>
