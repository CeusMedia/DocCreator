<?php
/**
 *	General Runner of DocCreator Application.
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
 *	@version		$Id: DocCreator.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'de.ceus-media.file.ini.Reader' );
import( 'de.ceus-media.alg.time.Clock' );
import( 'de.ceus-media.ui.DevOutput' );
require_once( dirname( __FILE__ ).'/Configuration.php5' );
require_once( dirname( __FILE__ ).'/Environment.php5' );
require_once( dirname( __FILE__ ).'/Reader.php5' );
/**
 *	General Runner of DocCreator Application.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@uses			File_INI_Reader
 *	@uses			Alg_Time_Clock
 *	@uses			DocCreator_Core_Configuration
 *	@uses			DocCreator_Core_Environment
 *	@uses			DocCreator_Core_Reader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: DocCreator.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class DocCreator_Core_Runner
{
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
	 *	@return		void
	 */
	public function __construct( $configFile, $verbose = NULL )
	{
		CMC_Loader::registerNew( 'php5' );
		$this->loadToolConfig();
		$this->setConfigFile( $configFile );
		if( !is_null( $verbose ) )
			$this->setVerbose( $verbose );
	}
	
	/** 
	 *	Enable or disable creation of Doc Files (Classes, Interfaces, Packages, Categories).
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Doc Files
	 *	@return		void
	 */
	public function enableCreator( $bool = TRUE )
	{
		$this->configProject->setSkip( 'creator', !$bool );
	}
	
	/** 
	 *	Enable or disable creation of Info Sites.
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Info Sites
	 *	@return		void
	 */
	public function enableInfo( $bool = TRUE )
	{
		$this->configProject->setSkip( 'info', !$bool );
	}
	
	/** 
	 *	Enable or disable parsing of Source Files.
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch parsing of Source Files
	 *	@return		void
	 */
	public function enableParser( $bool = TRUE )
	{
		$this->configProject->setSkip( 'parser', !$bool );
	}

	/** 
	 *	Enable or disable creation of Resources (Images, StyleSheets, JavaScripts).
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Resources
	 *	@return		void
	 */
	public function enableResources( $bool = TRUE )
	{
		$this->configProject->setSkip( 'resources', !$bool );
	}
	
#	public function getOption( $key, $default = NULL )
#	{
#		if( isset( $this->configProject['creator.'.$key] ) )
#			return $this->configProject['creator.'.$key];
#		return $default;
#	}

	/**
	 *	@deprecated		currently disabled, check if useful, otherwise clean config also (mail receiver etc.)
	 */
	public function handleError( $number, $message, $file, $line )
	{
		ob_start();
		$baseUri	= str_replace( "\\", "/", dirname( __FILE__ ) )."/";
		remark( $message );
		$exception	= new RuntimeException( $message );
		$file	= "";
		foreach( $exception->getTrace() as $step )
		{
			if( $file )
			{
				$class		= isset( $step['class'] ) ? $step['class'] : "";
				$function	= isset( $step['function'] ) ? $step['function'] : "";
				$line		= $class ? $class." -> ".$function : ( $function ? $function : "" );
				if( $line )
					remark( $line."()" );
			}
			if( isset( $step['file'] ) )
			{
				$file	= preg_replace( '@^'.$baseUri.'@', "", str_replace( "\\", "/", $step['file'] ) );
				remark( $file.":".$step['line'] );
			}
			$file	= TRUE;
			remark( str_repeat( "-", "77" ) );
		}
		$content	= ob_get_clean();
		$receiver	= $this->configProject->getMailReceiver();
		if( $receiver )
		{
			$subject	= array_shift( explode( "\n", $message ) );
			mail( $receiver, $subject, $message );
		}
		$logFile	= $this->configProject->getErrorLogFileName();
		if( $logFile )
		{
			error_log( time().":". base64_encode( $content )."\n", 3, $logFile );
		}
		if( $this->configProject->getVerbose() )
		{
			die( $content );
		}
	}

	/**
	 *	Loads Configuration of Projects from set absolute or relative XML Configuration File.
	 *	@access		protected
	 *	@return		void
	 */
	protected function loadProjectConfig()
	{
		//  --  LOAD CUSTOM PROJECT CONFIG  --  //
		if( !$this->configFile )
			throw new RuntimeException( 'No config file set' );
		if( !file_exists( $this->configFile ) )
			throw new RuntimeException( 'Config file "'.$this->configFile.'" not found' );

		$this->configProject	= new DocCreator_Core_Configuration( $this->configFile );
		$this->env				= new DocCreator_Core_Environment( $this->configProject, $this->configTool );
		$this->setVerbose( $this->configProject->getVerbose() );
	}

	/**
	 *	Loads Configuration of DocCreator itself.
	 *	@access		protected
	 *	@return		void
	 */
	protected function loadToolConfig()
	{
		$uri	= dirname( dirname( __FILE__ ) )."/config/config.ini";
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'No tool config file given' );
		$this->configTool	= parse_ini_file( $uri, TRUE );
	}

	public function main()
	{
		$pathOld		= getCwd();
		$this->pathTool	= dirname( dirname( __FILE__ ) );
		chdir( $this->pathTool );
		try
		{
			$clock		= new Alg_Time_Clock;

#			if( $this->getOption( 'showConfigOnly' ) )
#			{
#				$this->showConfig();
#				return;
#			}
#			else if( !$this->configProject->getVerbose() )
#				ob_start( 'trashOutput' );

			if( $this->configProject->getVerbose() )
			{
				remark( "run ".$this->configTool['application']['name']." v".$this->configTool['application']['version'] );
#				remark( "for ".$this->configProject['project.name']." v".$this->configProject['project.version'] );
				remark( "" );
				remark( "Project Config: ".$this->configFile );
#				if( $this->getOption( 'showConfig' ) )
#					$this->showConfig();
			}

			if( $this->configProject->getSkip( 'parser' ) )
			{
				if( $this->configProject->getVerbose( 'skip' ) )
					remark( 'Skip: Parser + Reader + Reader Plugins' );
			}
			else
			{
				$doc	= new DocCreator_Core_Reader( $this->configProject, $this->configProject->getVerbose() );
				$data	= $doc->readFiles();
				$this->env->saveContainer( $data );												//  save Data to Serial File
			}
			
			$this->runCreator();
			remark( "Time: ".$clock->stop( 0, 1 )." seconds" );

#			if( !empty( $this->configProject['quite'] ) )
#				ob_get_clean();
		}
		catch( Exception $e )
		{
			$trace	= $e->getTraceAsString();
			$trace	= preg_replace( '/#([0-9])+ /', "#\\1\t", $trace );
			$trace	= str_replace( "): ", "):\n\t", $trace );
			remark( "\n".$e->getMessage() );
			remark( "\nFile: ".$e->getFile() );
			remark( "Line: ".$e->getLine() );
			remark( "Trace:\n".$trace );
		}
		chdir( $pathOld );
	}

	/**
	 *	Start creation of Doc Files applying one or more Builders to Data Container.
	 *	@access		protected
	 *	@return		void
	 *	@throws		RuntimeException if a Builder Class is not existing
	 */
	protected function runCreator()
	{
		foreach( $this->configProject->getBuilders() as $builder )
		{
			$format		= $builder->getAttribute( 'format' );
			$converter	= $builder->getAttribute( 'converter' );

			$loader		= new CMC_Loader( 'php5' );
			$loader->setPrefix( 'Builder_'.$format.'_'.$converter.'_' );
			$loader->setPath( 'Builder/'.$format.'/'.$converter.'/classes/' );
			$loader->registerAutoloader();

#			$classKey	= 'builder.'.$format.'.'.$converter.'.classes.Creator';
			$className	= 'Builder_'.$format.'_'.$converter.'_Creator';
#			import( $classKey );
			if( !class_exists( $className ) )
				throw new RuntimeException( 'Builder class "'.$className.'" is not existing' );
			new $className( $this->env, $builder, $this->configProject->getVerbose() );
		}
	}

	/**
	 *	Set an other XML Configuration File and load it.
	 *	@access		public
	 *	@param		string		$configFile		URI of XML Configuration File 
	 *	@return		void
	 */
	public function setConfigFile( $configFile )
	{
		$this->configFile	= $configFile;
		$this->loadProjectConfig();		
	}

#	public function setErrorLog( $fileName )
#	{
#		$this->setOption( 'file.log.error', $fileName );
#	}

	public function setErrorMail( $mail )
	{
		$this->configProject->setMailReceiver( $mail );
	}
	
#	public function setOption( $key, $value )
#	{
#		$this->configProject['creator.'.$key]	= $value;
#	}

	public function setQuite()
	{
		$this->setVerbose( FALSE );
	}
	
	public function setVerbose( $bool = TRUE )
	{
		$this->configProject->setVerbose( 'general', $bool );
	}

	/**
	 *	Prints current Configuration.
	 *	@deprecated		should not work since config is xml base -> to be reworked or deleted
	 */
	protected function showConfig( $indent = 20 )
	{
		remark( "Settings:" );
		foreach( $this->configProject as $key => $value )
		{
			$key	.= str_repeat( " ", 30 - strlen( $key ) );
			if( utf8_encode( utf8_decode( $value ) ) == $value )
				$value	= utf8_decode( $value );
			remark( $key.$value );
		}
	}
}
function trashOutput( $content )
{
	$content	= NULL;
}
?>
