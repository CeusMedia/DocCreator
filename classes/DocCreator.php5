<?php
/**
 *	Main Class of DocCreator Application.
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
 *	@version		$Id: DocCreator.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
import( 'de.ceus-media.file.ini.Reader' );
import( 'de.ceus-media.console.Application' );
import( 'de.ceus-media.alg.time.Clock' );
import( 'de.ceus-media.ui.DevOutput' );
import( 'classes.Environment' );
import( 'classes.Reader' );
/**
 *	Main Class of DocCreator Application.
 *	@category		cmTools
 *	@package		DocCreator
 *	@extends		Console_Application
 *	@uses			File_INI_Reader
 *	@uses			Alg_Time_Clock
 *	@uses			Environment
 *	@uses			Reader
 *	@author			Christian Würker <christian.wuerker@ceus-media.de>
 *	@copyright		2008-2009 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: DocCreator.php5 718 2009-10-19 01:34:14Z christian.wuerker $
 */
class DocCreator extends Console_Application
{
	protected $configFile		= array();
	protected $configTool		= array();
	protected $configProject	= array();
	protected $shortCuts		= array(
		'-c'	=> '--config-file',
		'-h'	=> '--help',
		'-l'	=> '--log-errors',
		'-m'	=> '--mail-errors',
		'-q'	=> '--quite',
		'-sc'	=> '--skip-creator',
		'-si'	=> '--skip-info',
		'-sp'	=> '--skip-parser',
		'-sr'	=> '--skip-resources',
	);
	
	protected $pathProject;
	protected $pathTool;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string			$pathProject	Path of Project to document
	 *	@param		bool			$verbose		Flag: show Information during Creation
	 *	@return		void
	 */
	public function __construct( $configFile = NULL, $verbose = NULL )
	{
		if( $configFile )
			$this->configFile	= $configFile;

 		$this->pathTool		= str_replace( "\\", "/", dirname( dirname( realpath( __FILE__ ) ) ) )."/";
		$this->loadToolConfig();
		$this->loadProjectConfig();
		if( !is_null( $verbose) )
			$this->configProject['creator.verbose'] = $verbose;
		$this->verbose		= $this->configProject['creator.verbose'];

		$this->configProject	= new ArrayObject( $this->configProject );

#		set_error_handler( array( $this, 'handleError' ) );
		parent::__construct( $this->shortCuts );
	}
	
	protected function loadProjectConfig()
	{
		//  --  LOAD DEFAULT PROJECT CONFIG  --  //
		$uri	= dirname( dirname( __FILE__ ) )."/config/default.ini";
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'No default config file given' );
		$configDefault	= parse_ini_file( $uri, FALSE );

		//  --  LOAD CUSTOM PROJECT CONFIG  --  //
		if( !$this->configFile )
			throw new RuntimeException( 'No config file given' );
		$configCustom			= parse_ini_file( $this->configFile, FALSE );

		$this->configProject	= array_merge( $configDefault, $configCustom );						//  merge default and custom config to project config
		$this->pathProject		= $this->configProject['project.path.source'];						//  set shortcut to project 
	}

	protected function loadToolConfig()
	{
		$uri	= dirname( dirname( __FILE__ ) )."/config/doc.ini";
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'No tool config file given' );
		$this->configTool	= parse_ini_file( $uri, FALSE );
	}

	/**
	 *	Main Method handling Application Call and Console Parameters.
	 *	@access		protected
	 *	@return		void
	 */
	protected function main()
	{
		$config		=& $this->configProject;
		$mapSkip	= array(
			'skip-parser'		=> 'skipParser',
			'skip-creator'		=> 'skipCreator',
			'skip-info'			=> 'skipInfo',
			'skip-resources'	=> 'skipResources',
		);
		foreach( $mapSkip as $keyArgument => $keyConfig )
			if( $this->arguments->has( '--'.$keyArgument ) )
				$config['creator.'.$keyConfig]		= TRUE;
		if( $this->arguments->has( '--quite' ) )
			$config['creator.verbose']		= FALSE;

		try
		{
			$clock		= new Alg_Time_Clock;

			if( $this->arguments->has( '--help' ) )
			{
				$this->showUsage();
				return;
			}
			else if( $this->arguments->has( '--show-config-only' ) )
			{
				$this->showConfig( $config );
				return;
			}
#			else if( !$config['creator.verbose'] )
#				ob_start( 'trashOutput' );

			if( $config['creator.verbose'] )
			{
				remark( "run ".$this->configTool['project.name']." v".$this->configTool['project.version'] );
				remark( "for ".$this->configProject['project.name']." v".$this->configProject['project.version'] );
				remark( "" );
				remark( "Project Config: ".$this->configFile );
			}

			if( !$config['creator.skipParser'] )
			{
				$doc	= new Reader( $config, $this->verbose );
				$doc->readFiles();
			}
			
			$this->runCreator( $config );
			remark( "Time: ".$clock->stop( 0, 1 )." seconds" );

#			if( !empty( $config['quite'] ) )
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
			die;
			trigger_error( "Error: ".$e->getMessage(), E_USER_ERROR );
		}
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
		if( $this->arguments->has( '--mail-errors' ) )
		{
			$receiver	= $this->arguments->get( '--mail-errors' ) ? $this->arguments->get( '--mail-errors' ) : self::$defaultMailReceiver;
			$subject	= array_shift( explode( "\n", $message ) );
			mail( "kriss@reiz-strom.net", $subject, $message );
		}
		if( $this->arguments->has( '--log-errors' ) )
		{
			$logFile	= $this->arguments->get( '--log-errors' ) ? $this->arguments->get( '--log-errors' ) : self::$defaultLogFile;
			error_log( time().":". base64_encode( $content )."\n", 3, $logFile );
		}
		if( !$this->arguments->has( '--quite' ) )
		{
			die( $content );
		}
	}

	protected function test( $config )
	{
		$parser	= new File_PHP_Parser();
		$file	= $parser->parseFile( $config['pathClasses']."adt/tree/AvlNode.php5", $config['pathClasses'] );
		die;
	}
		
	protected function runCreator( $config )
	{
		$format		= $config['project.builder.format'];
		$theme		= $config['project.builder.theme'];
		$classKey	= 'builder.'.$format.'.'.$theme.'.classes.Creator';
		$className	= 'Builder_'.strtoupper( $format ).'_'.strtoupper( $theme ).'_Creator';
		import( $classKey );
		new $className( $config, $this->verbose );
	}

	protected function showConfig( $config, $indent = 20 )
	{
		remark( "Settings:" );
		foreach( $config as $key => $value )
		{
			$key	.= str_repeat( " ", 30 - strlen( $key ) );
			if( utf8_encode( utf8_decode( $value ) ) == $value )
				$value	= utf8_decode( $value );
			remark( $key.$value );
		}
	}

	protected function showUsage()
	{
		remark( "Usage: php create.php5 [OPTION]..." );
		remark( "Options:" );
		remark( "  -c, --config-file       URI of config file of project" );
		remark( "  -q, --quite             No output to console" );
		remark( "  -sc, --skip-creator     Skip file creation process" );
		remark( "  -sp, --skip-parser      Skip file parsing process" );
		remark( "  -sr, --skip-resources   Skip coping of resources files" );
		remark( "  --show-config           Show project config" );
		remark( "  --show-config-only      Show project config and abort" );
		remark( "" );
	}
}
function trashOutput( $content )
{
	$content	= NULL;
}
?>