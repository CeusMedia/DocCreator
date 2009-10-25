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
require_once( dirname( __FILE__ ).'/Environment.php5' );
require_once( dirname( __FILE__ ).'/Reader.php5' );
/**
 *	General Runner of DocCreator Application.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@uses			File_INI_Reader
 *	@uses			Alg_Time_Clock
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

	public function __construct( $configFile = NULL, $verbose = NULL )
	{
		$this->loadToolConfig();

		if( $configFile )
		{
			$this->setConfigFile( $configFile );
			if( !is_null( $verbose ) )
				$this->setVerbose( $verbose );
		}
	}
	
	public function enableParser( $bool = TRUE )
	{
		$this->setOption( 'skipParser', !$bool );
	}
	
	public function enableCreator( $bool = TRUE )
	{
		$this->setOption( 'skipCreator', !$bool );
	}
	
	public function enableInfo( $bool = TRUE )
	{
		$this->setOption( 'skipInfo', !$bool );
	}
	
	public function enableResources( $bool = TRUE )
	{
		$this->setOption( 'skipResources', !$bool );
	}
	
	public function setOption( $key, $value )
	{
		$this->configProject['creator.'.$key]	= $value;
	}
	
	public function getOption( $key, $default = NULL )
	{
		if( isset( $this->configProject['creator.'.$key] ) )
			return $this->configProject['creator.'.$key];
		return $default;
	}

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
		if( $this->getOption( 'mail.receiver' ) )
		{
			$receiver	= $this->getOption( 'mail.receiver' );
			$subject	= array_shift( explode( "\n", $message ) );
			mail( "kriss@reiz-strom.net", $subject, $message );
		}
		if( $this->getOption( 'file.log.error' ) )
		{
			$logFile	= $this->getOption( 'file.log.error' );
			error_log( time().":". base64_encode( $content )."\n", 3, $logFile );
		}
		if( $this->getOption( 'verbose' ) )
		{
			die( $content );
		}
	}
	
	protected function loadProjectConfig( $fileName )
	{
		//  --  LOAD DEFAULT PROJECT CONFIG  --  //
		$uri	= dirname( dirname( __FILE__ ) )."/config/default.ini";
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'No default config file found' );
		$configDefault	= parse_ini_file( $uri, FALSE );

		//  --  LOAD CUSTOM PROJECT CONFIG  --  //
		if( !$fileName )
			throw new RuntimeException( 'No config file set' );
		if( !file_exists( $fileName ) )
			throw new RuntimeException( 'No config file found' );
		$configCustom			= parse_ini_file( $fileName, FALSE );

		$this->configProject	= array_merge( $configDefault, $configCustom );						//  merge default and custom config to project config
		$this->pathProject		= $this->configProject['project.path.source'];						//  set shortcut to project 

		$this->setVerbose( $this->getOption( 'verbose' ) );
	}

	protected function loadToolConfig()
	{
		$uri	= dirname( dirname( __FILE__ ) )."/config/doc.ini";
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'No tool config file given' );
		$this->configTool	= parse_ini_file( $uri, FALSE );
	}

	public function main()
	{
		$pathOld		= getCwd();
		$this->pathTool	= dirname( dirname( __FILE__ ) );
		chdir( $this->pathTool );
		try
		{
			$clock		= new Alg_Time_Clock;

			if( $this->getOption( 'showConfigOnly' ) )
			{
				$this->showConfig();
				return;
			}
#			else if( !$this->getOption( 'verbose' ) )
#				ob_start( 'trashOutput' );

			if( $this->getOption( 'verbose' ) )
			{
				remark( "run ".$this->configTool['project.name']." v".$this->configTool['project.version'] );
				remark( "for ".$this->configProject['project.name']." v".$this->configProject['project.version'] );
				remark( "" );
				remark( "Project Config: ".$this->configFile );
				if( $this->getOption( 'showConfig' ) )
					$this->showConfig();
			}

			if( $this->getOption( 'skipParser' ) )
			{
				if( $this->getOption( 'verboseSkip' ) )
					remark( 'Skip: Parser + Reader + Reader Plugins' );
			}
			else
			{
				$doc	= new DocCreator_Core_Reader( $this->configProject, $this->getOption( 'verbose' ) );
				$doc->readFiles();
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
		
	protected function runCreator()
	{

	
		$format		= $this->configProject['project.builder.format'];
		$theme		= $this->configProject['project.builder.theme'];
		$classKey	= 'builder.'.$format.'.'.$theme.'.classes.Creator';
		$className	= 'Builder_'.strtoupper( $format ).'_'.strtoupper( $theme ).'_Creator';
		import( $classKey );
		new $className( $this->configProject, $this->getOption( 'verbose' ) );
	}

	public function setConfigFile( $fileName )
	{
		$this->configFile	= $fileName;
		$this->loadProjectConfig( $fileName );		
		$this->configProject	= new ArrayObject( $this->configProject );
	}

	public function setErrorLog( $fileName )
	{
		$this->setOption( 'file.log.error', $fileName );
	}

	public function setErrorMail( $mail )
	{
		$this->setOption( 'mail.receiver', $mail );
	}

	public function setQuite()
	{
		$this->setVerbose( FALSE );
	}
	
	public function setVerbose( $bool = TRUE )
	{
		$this->setOption( 'verbose', $bool );
	}

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