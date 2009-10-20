<?php
import( 'builder.html.cm1.classes.site.info.Abstract' );
class Builder_HTML_CM1_Site_Info_Triggers extends Builder_HTML_CM1_Site_Info_Abstract
{
	/**
	 *	Creates Trigger Info Site File.
	 *	@access		public
	 *	@return		bool		Flag: file has been created
	 */
	public function createSite()
	{
		if( !isset( $this->env->data->triggers ) )
			return FALSE;
		if( !$this->env->data->triggers )
			return FALSE;
		
		$list		= array();
		foreach( $this->env->data->triggers as $name => $description )
		{
			$item	= "<dt>".$name."</dt><dd>".$description."</dd>";
			$list[]	= $item;
		}
		if( !$list )
			return FALSE;

		$this->verboseCreation( 'triggers' );

		$content	= '<dl>'.implode( "\n", $list ).'</dl>';

		$words	= isset( $this->env->words['triggers'] ) ? $this->env->words['triggers'] : array();
		$uiData	= array(
			'key'		=> 'triggers',
			'id'		=> 'info-triggers',
			'title'		=> isset( $words['heading'] ) ? $words['heading'] : 'triggers',
			'content'	=> $content,
			'words'		=> $words,
		);
		$template	= 'site/info/triggers';
		$template	= $this->hasTemplate( $template ) ? $template : 'site/info/abstract';
		$content	= $this->loadTemplate( $template, $uiData );

		$this->saveFile( "triggers.html", $content );
		$this->appendLink( 'triggers.html', 'triggers', count( $list ) );
		return TRUE;
	}
}
?>