<?php
/**
 *	Script to execute DocCreator Runner on console.
 *	You can run this script from anywhere on the filesystem.
 *
 *	Usage: php /path/to/DocCreator/doc.php --config-file=/path/to/your/doc.xml
 */

$pathCwd			= getCwd().'/';
$pathSelf			= dirname( __FILE__ ).'/';
$autoload			= 'vendor/autoload.php';

if( file_exists( $pathCwd.$autoload ) )
	require_once $pathCwd.$autoload;
else if( file_exists( $pathSelf.$autoload ) )
	require_once $pathSelf.$autoload;
else
	throw new \RuntimeException( 'No vendor folder found. Please run composer!' );	//  quit with exception

new \CeusMedia\Common\UI\DevOutput;
new \CeusMedia\DocCreator\Core\ConsoleRunner();										//  create a new runner
