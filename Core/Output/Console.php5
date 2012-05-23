<?php
class DocCreator_Core_Output_Console{
	
	protected $lastLine	= "";
	
	public function newLine( $string = "" ){
		$string		= Alg_Text_Trimmer::trimCentric( $string, 78 );										//  trim string to <80 columns
		$this->lastLine	= $string;
		print( "\n" . $string );
	}
	public function sameLine( $string = "" ){
		$string		= Alg_Text_Trimmer::trimCentric( $string, 78 );										//  trim string to <80 columns
		$fill		= str_repeat( " ", max( 0, strlen( $this->lastLine ) - strlen( $string ) ) );
		$this->lastLine	= $string;
		print( "\r" . $string . $fill );
#		usleep( 10000 );
	}
}
?>