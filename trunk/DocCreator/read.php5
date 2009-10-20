<?php
require_once( "cmClasses/0.6.6/useClasses.php5" );
import( 'de.ceus-media.ui.DevOutput' );
import( 'classes.Reader' );

$config	= parse_ini_file( "config/config.ini" );
$doc	= new Reader( $config );
$doc->listClassFiles();

#remark( "Skipped Folders: " );
#print_m( $lister->logSkippedFolders );

?>