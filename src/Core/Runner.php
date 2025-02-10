<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	General Runner of DocCreator Application.
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

use CeusMedia\Common\Alg\Time\Clock as Clock;
use CeusMedia\Common\CLI\Output as CliOutput;
use RuntimeException;

/**
 *	General Runner of DocCreator Application.
 *	@category		Tool
 *	@package		CeusMedia_DocCreator_Core
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2008-2023 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Runner
{
	protected Environment $env;
	protected ?string $configFile	= NULL;
	protected array $configTool		= [];
	protected Configuration $configProject;
	protected $options				= NULL;
	protected $pathProject;

	protected CliOutput $out;
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		string|NULL	$configFile		Name of Configuration File, absolute or relative
	 *	@param		bool		$verbose		Flag: show Information
	 *	@param		bool		$trace			Flag: show Exception Trace
	 *	@return		void
	 */
	public function __construct( ?string $configFile = NULL, bool $verbose = NULL, bool $trace = NULL )
	{
		$this->loadToolConfig();
		$this->out		= new CliOutput();
		if( !is_null( $configFile ) && strlen( $configFile ) > 0 ){
			$this->setConfigFile( $configFile );
			if( !is_null( $verbose ) )
				$this->setVerbose( $verbose );
			if( !is_null( $trace ) )
				$this->setTrace( $trace );
		}

		@date_default_timezone_set( @date_default_timezone_get() );					//  set default time zone
	}

	/**
	 *	Enable or disable creation of Doc Files (Classes, Interfaces, Packages, Categories).
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Doc Files
	 *	@return		self
	 */
	public function enableCreator( bool $bool = TRUE ): self
	{
		$this->configProject->setSkip( 'creator', !$bool );
		return $this;
	}

	/**
	 *	Enable or disable creation of Info Sites.
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Info Sites
	 *	@return		self
	 */
	public function enableInfo( bool $bool = TRUE ): self
	{
		$this->configProject->setSkip( 'info', !$bool );
		return $this;
	}

	/**
	 *	Enable or disable parsing of Source Files.
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch parsing of Source Files
	 *	@return		self
	 */
	public function enableParser( bool $bool = TRUE ): self
	{
		$this->configProject->setSkip( 'parser', !$bool );
		return $this;
	}

	/**
	 *	Enable or disable creation of Resources (Images, StyleSheets, JavaScripts).
	 *	@access		public
	 *	@param		bool		$bool		Flag: switch creation of Resources
	 *	@return		self
	 */
	public function enableResources( bool $bool = TRUE ): self
	{
		$this->configProject->setSkip( 'resources', !$bool );
		return $this;
	}

	public function main()
	{
		if( !$this->configFile )
			throw new RuntimeException( "No config file set." );

//		$this->pathTool	= dirname( __FILE__, 3 );
//		try{
			$clock		= new Clock;
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
				$doc	= new Reader( $this->env, $this->configProject->getVerbose() ?? FALSE );
				$data	= $doc->readFiles();
				$this->env->saveContainer( $data );												//  save Data to Serial File
			}

			$this->out->newLine();
			$this->runCreator();
			$usage	= getrusage();
			$this->out->newLine( vsprintf( "Done in %s seconds (%s)", [
				$clock->stop( 0, 1 ),
				round( $usage["ru_utime.tv_usec"] / 1_000_000, 1 ),
			] ) );
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

	public function setBuilderTargetPath( string $path = NULL ): self
	{
		$this->configProject->setBuilderTargetPath( $path );
		return $this;
	}

	/**
	 *	Set a XML Configuration File and load it.
	 *	@access		public
	 *	@param		string		$configFile		URI of XML Configuration File
	 *	@return		self
	 */
	public function setConfigFile( string $configFile ): self
	{
		$this->configFile	= $configFile;
		$this->loadProjectConfig();
		return $this;
	}

	public function setErrorMail( string $mail ): self
	{
		$this->configProject->setMailReceiver( $mail );
		return $this;
	}

	public function setProjectBasePath( string $path ): self
	{
		$this->configProject->setProjectBasePath( $path );
		return $this;
	}

#	public function setErrorLog( $fileName ){
#		$this->setOption( 'file.log.error', $fileName );
#	}

#	public function setOption( $key, $value ){
#		$this->configProject['creator.'.$key]	= $value;
#	}

	public function setQuiet(): self
	{
		$this->setVerbose( FALSE );
		return $this;
	}

	public function setTrace( bool $bool = TRUE ): self
	{
		$this->configProject->setTrace( $bool );
		return $this;
	}

	public function setVerbose( bool $bool = TRUE ): self
	{
		$this->configProject->setVerbose( 'general', $bool );
		return $this;
	}

	/**
	 *	Loads Configuration of Projects from set absolute or relative XML Configuration File.
	 *	@access		protected
	 *	@return		void
	 */
	protected function loadProjectConfig(): void
	{
		//  --  LOAD CUSTOM PROJECT CONFIG  --  //
		if( !$this->configFile )
			throw new RuntimeException( 'No config file set' );
		if( !file_exists( $this->configFile ) )
			throw new RuntimeException( 'Config file "'.$this->configFile.'" not found' );

		$this->configProject	= new Configuration( $this->configFile );
		$this->env				= new Environment( $this->configProject, $this->configTool, $this->out );
		if( $this->configProject->getTimeLimit() >= 0 )
			set_time_limit( $this->configProject->getTimeLimit() );
		$this->setVerbose( $this->configProject->getVerbose() ?? FALSE );
	}

	/**
	 *	Loads Configuration of DocCreator itself.
	 *	@access		protected
	 *	@return		void
	 */
	protected function loadToolConfig(): void
	{
		$uri	= dirname( __FILE__, 3 ) ."/config/config.ini";
		if( !file_exists( $uri ) )
			throw new RuntimeException( 'No tool config file given' );
		$this->configTool	= parse_ini_file( $uri, TRUE ) ?: [];
	}

	/**
	 *	Start creation of Doc Files applying one or more Builders to Data Container.
	 *	@access		protected
	 *	@return		void
	 *	@throws		RuntimeException if a Builder Class is not existing
	 */
	protected function runCreator(): void
	{
		foreach( $this->configProject->getBuilders() as $builder ){
			$format		= $builder->getAttribute( 'format' );
			$className	= '\\CeusMedia\\DocCreator\\Builder\\'.$format.'\\Creator';
			if( !class_exists( $className ) )
				throw new RuntimeException( 'Builder class "'.$className.'" is not existing' );
			new $className( $this->env, $builder, $this->configProject->getVerbose() );
		}
	}
}
