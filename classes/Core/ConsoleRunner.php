<?php
/**
 *	Main Class of DocCreator Application.
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
 *	@version		$Id: ConsoleRunner.php5 79 2011-09-09 14:24:09Z christian.wuerker $
 */
/**
 *	Main Class of DocCreator Application.
 *	@category		cmTools
 *	@package		DocCreator_Core
 *	@extends		Console_Application
 *	@uses			DocCreator_Core_Runner
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2013 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@version		$Id: ConsoleRunner.php5 79 2011-09-09 14:24:09Z christian.wuerker $
 */
class DocCreator_Core_ConsoleRunner extends Console_Application{

	protected $configFile	= NULL;

	protected $verbose		= NULL;

	protected $shortCuts		= array(
		'-c'	=> '--config-file',
		'-s'	=> '--source-folder',
		'-t'	=> '--target-folder',
		'-h'	=> '--help',
		'-l'	=> '--log-errors',
		'-m'	=> '--mail-errors',
		'-q'	=> '--quite',
	);

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string			$pathProject	Path of Project to document
	 *	@param		bool			$verbose		Flag: show Information during Creation
	 *	@return		void
	 */
	public function __construct( $configFile = NULL, $verbose = NULL, $trace = NULL ){
		$this->configFile	= $configFile;
		$this->verbose		= $verbose;
		$this->trace		= $trace;
		parent::__construct( $this->shortCuts );
	}

	/**
	 *	Main Method handling Application Call and Console Parameters.
	 *	@access		protected
	 *	@return		void
	 */
	protected function main(){
		if( $this->arguments->has( '--help' ) )
			die( $this->showUsage() );

#		set_error_handler( array( $this, 'handleError' ) );
		$creator	= new DocCreator_Core_Runner( $this->configFile, $this->verbose, $this->trace );

		$mapSkip	= array(
			'--config-file'		=> 'setConfigFile',
			'--source-folder'	=> 'setProjectBasePath',
			'--target-folder'	=> 'setBuilderTargetPath',
			'--log-error'		=> 'setErrorLog',
			'--mail-error'		=> 'setErrorMail',
			'--skip-parser'		=> 'enableParser',
			'--skip-creator'	=> 'enableCreator',
			'--skip-info'		=> 'enableInfo',
			'--skip-resources'	=> 'enableResources',
			'--quite'			=> 'setQuite',
			'--trace'			=> 'setTrace'
		);
		foreach( $this->arguments->getAll() as $key => $value )
			if( array_key_exists( $key, $mapSkip ) )
				$creator->$mapSkip[$key]( $value );
		if( $this->arguments->has( '--show-config' ) )
			$creator->setOption( 'showConfig', TRUE );
		if( $this->arguments->has( '--show-config-only' ) )
			$creator->setOption( 'showConfigOnly', TRUE );

		$creator->main();
	}

	/**
	 *	Prints Usage Screen.
	 *	@access		protected
	 *	@param		string		$message		Message to show below usage lines
	 *	@return		void
	 */
	protected function showUsage( $message = NULL ){
		remark( "Usage: php create.php5 [OPTION]..." );
		remark( "Options:" );
		remark( "  -c, --config-file       URI of config file of project" );
		remark( "  -s, --source-folder     Override base source folder" );
		remark( "  -t, --target-folder     Override base target folder" );
		remark( "  -sc, --skip-creator     Skip file creation process" );
		remark( "  -sp, --skip-parser      Skip file parsing process" );
		remark( "  -sr, --skip-resources   Skip coping of resources files" );
		remark( "  -q, --quite             No output to console" );
		remark( "  --trace                 Show trace of exception" );
		remark( "  --show-config           Show project config" );
		remark( "  --show-config-only      Show project config and abort" );
		remark( "" );
		if( $message )
			$this->showError( $message );
	}
}
?>
