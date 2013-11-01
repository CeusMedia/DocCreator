<?php
foreach( new DirectoryIterator( "." ) as $entry )
	if( $entry->isDir() )
		if( preg_match( '/^[^\.]/', $entry ) )
			echo '<a href="'.$entry.'">'.$entry.'</a><br/>';
foreach( new DirectoryIterator( "." ) as $entry )
	if( $entry->isFile() )
		if( preg_match( '/\.(png|jp(e|eg|g)|gif|bmg|ico)$/i', $entry ) )
			echo '<img src="'.$entry.'" title="'.$entry.'"/>';