<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Main Class of DocCreator Application.
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

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\CLI\Application as CliApplication;
use Exception;

/**
 *	Main Class of DocCreator Application.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class ConsoleRunner extends CliApplication
{
	protected ?string $configFile;

	protected bool $verbose;

	protected bool $trace;

	protected array $shortCuts		= [
		'-c'	=> '--config-file',
		'-s'	=> '--source-folder',
		'-t'	=> '--target-folder',
		'-h'	=> '--help',
		'-l'	=> '--log-errors',
		'-m'	=> '--mail-errors',
		'-q'	=> '--quite',
	];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string|NULL		$configFile		...
	 *	@param		bool			$verbose		Flag: show Information during Creation
	 *	@param		bool			$trace			Flag: ...
	 *	@return		void
	 */
	public function __construct( string $configFile = NULL, bool $verbose = FALSE, bool $trace = FALSE )
	{
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
	protected function main()
	{
		/** @var array $parameters */
		$parameters	= $this->arguments->get( 'parameters', [] );
		$parameters	= new Dictionary( $parameters );
		if( $parameters->has( '--help' ) ){
			$this->showUsage();
			exit;
		}

#		set_error_handler( array( $this, 'handleError' ) );
		try{
			$mapSkip	= [
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
			];
			$creator	= new Runner( $this->configFile, $this->verbose, $this->trace );
			/**
			 * @var string $key
			 * @var mixed $value
			 */
			foreach( $parameters->getAll() as $key => $value )
				if( array_key_exists( $key, $mapSkip ) )
					$creator->{$mapSkip[$key]}( $value );
			if( $parameters->has( '--show-config' ) )
				$creator->setOption( 'showConfig', TRUE );
			if( $parameters->has( '--show-config-only' ) )
				$creator->setOption( 'showConfigOnly', TRUE );

			$creator->main();
		}
		catch( Exception $e ){
			print $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			$this->showUsage();
			exit;
		}
	}

	/**
	 *	Prints Usage Screen.
	 *	@access		protected
	 *	@param		string		$message		Message to show below usage lines
	 *	@return		void
	 */
	protected function showUsage( $message = NULL )
	{
		print "Usage: php create.php5 [OPTION]..." . PHP_EOL;
		print "Options:" . PHP_EOL;
		print "  -c, --config-file       URI of config file of project" . PHP_EOL;
		print "  -s, --source-folder     Override base source folder" . PHP_EOL;
		print "  -t, --target-folder     Override base target folder" . PHP_EOL;
		print "  -sc, --skip-creator     Skip file creation process" . PHP_EOL;
		print "  -sp, --skip-parser      Skip file parsing process" . PHP_EOL;
		print "  -sr, --skip-resources   Skip coping of resources files" . PHP_EOL;
		print "  -q, --quite             No output to console" . PHP_EOL;
		print "  --trace                 Show trace of exception" . PHP_EOL;
		print "  --show-config           Show project config" . PHP_EOL;
		print "  --show-config-only      Show project config and abort" . PHP_EOL;
		print PHP_EOL;
		if( $message !== NULL )
			$this->showError( $message );
	}
}
