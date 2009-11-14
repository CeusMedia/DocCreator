<?php
/**
 *	Reads serialized List of Classes and Terms and matches with one or more Search Query Terms.
 */
class TermSearch
{
	protected $data		= array();

	/**
	 *	Constructor, reads serialized List of Search Terms of Classes.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		$this->data	= unserialize( file_get_contents( "terms.serial" ) );
	}

	/**
	 *	Matches a Search Query with Class Search Terms and returns List.
	 *	@access		public
	 *	@param		string		$query		Query containing one or more Search Terms
	 *	@return		array
	 */
	public function query( $query )
	{
		$list			= array();
		$queries		= self::getQueryParts( $query );
		foreach( $this->data as $item )
		{
			$foundQueries	= array();
			$foundCount 	= 0;
			foreach( $queries as $query )
			{
				$result	= $this->queryTerms( $item['terms'], $query );
				if( !$result )
				{
					$foundCount 	= 0;
					break;
				}
				$foundQueries[$query] = $result;
				$foundCount += $result;
			}
			if( !$foundCount )
				continue;
			$item['foundQueries']	= $foundQueries;
			$item['foundCount']		= $foundCount;	
			$list[$foundCount][]	= $item;
		}
		krsort( $list );
		$data	= array();
		foreach( $list as $sublist )
			foreach( $sublist as $item )
				$data[]	= $item;
		return $data;
	}
	
	/**
	 *	Returns number of Classes at all.
	 *	@access		public
	 *	@return		int
	 */
	public function getItemCount()
	{
		return count( $this->data );
	}

	/**
	 *	Splits given Search Query String into Query Parts.
	 *	@access		protected
	 *	@return		array		List of Query Parts
	 */
	protected static function getQueryParts( $query )
	{
		$parts	= explode( " ", $query );
		foreach( $parts as $nr => $part )
			if( !trim( $part ) )
				unset( $parts[$nr] );
		return $parts;
	}

	/**
	 *	Returns number of found Query Parts.
	 *	@access		protected
	 *	@return		int			Number of found Query Parts
	 */
	protected function queryTerms( $terms, $query )
	{
		$count		= 0;
		$matches	= array();
		foreach( $terms as $term => $number )
		{
			preg_match_all( "/".$query."/i", $term, $matches );
			$count		+= count( $matches[0] );
		}
		return $count;
	}
}

/**
 *	Renders HTML Result List.
 */
class TermSearchResults
{
	/**
	 *	Renders HTML Result List.
	 *	@access		public
	 *	@param		string		$query		Query containing one or more Search Terms
	 *	@param		int			$limit		Number of Result Items
	 *	@return		string
	 */
	public static function render( $query, $limit = 10 )
	{
		$search		= new TermSearch;
		$countAll	= $search->getItemCount();
		$data		= $search->query( $query );
		$countFound	= count( $data );
		$list		= array();

		if( is_int( $limit ) && $limit )
			$data	= array_slice( $data, 0, $limit );

		foreach( $data as $entry )
		{
			$facts		= $entry['facts'];
			$queries	= array_keys( $entry['foundQueries'] );
			$className	= self::hilight( $facts['className'], $queries );
			$classUrl	= 'class.'.$facts['classId'].'.html';
			$classLink	= '<a href="'.$classUrl.'?query='.urlencode($query).'">'.$className.'</a>&nbsp;<small>('.$entry['foundCount'].')</small>';
			$classDesc	= "";
			if( self::find( $facts['classDesc'], $queries ) )
				$classDesc	=  '<div class="description">'.self::hilight( $facts['classDesc'], $queries ).'</div>';

			//  --  CLASS MEMBERS  --  //
			$members	= array();
			if( !empty( $facts['members'] ) )
			{
				foreach( $facts['members'] as $memberName => $memberDesc )
				{
					if( !self::find( $memberName." ".$memberDesc, $queries ) )
						continue;
					$desc	= "";
					if( self::find( $memberDesc, $queries ) )
						$desc	=  '<div class="description">'.nl2br( self::hilight( $memberDesc, $queries ) ).'</div>';
					$name		= self::hilight( $memberName, $queries );
					$link		= '<a href="'.$classUrl.'#class_member_'.$memberName.'">$'.$name.'</a>';
					$members[]	= '<li class="member">'.$link.$desc.'</li>';
				}
			}
			$members	= count( $members ) ? '<ul class="members">'.implode( "\n", $members ).'</ul>' : "";

			//  --  CLASS METHODS  --  //
			$methods	= array();
			if( !empty( $facts['methods'] ) )
			{
				foreach( $facts['methods'] as $methodName => $methodDesc )
				{
					if( !self::find( $methodName." ".$methodDesc, $queries ) )
						continue;
					$desc	= "";
					if( self::find( $methodDesc, $queries ) )
						$desc	=  '<div class="description">'.nl2br( self::hilight( $methodDesc, $queries ) ).'</div>';
					$name		= self::hilight( $methodName, $queries );
					$link		= '<a href="'.$classUrl.'#class_method_'.$methodName.'">'.$name.'()</a>';
					$methods[]	= '<li class="method">'.$link.$desc.'</li>';
				}
			}
			$methods	= count( $methods ) ? '<ul class="methods">'.implode( "\n", $methods ).'</ul>' : "";

			//  --  TODOS  --  //
			$todos	= array();
			if( !empty( $facts['todos'] ) )
				foreach( $facts['todos'] as $todo )
					if( self::find( $todo, $queries ) )
						$todos[]	= '<div class="todo"><b>Todo: </b>'.self::hilight( $todo, $queries ).'</div>';
			$todos	= implode( "\n", $todos );

			//  --  DEPRECATIONS  --  //
			$deprecations	= array();
			if( !empty( $facts['deprecations'] ) )
				foreach( $facts['deprecations'] as $deprecation )
					if( self::find( $deprecation, $queries ) )
						$deprecations[]	= '<div class="todo"><b>Deprecation: </b>'.self::hilight( $deprecation, $queries ).'</div>';
			$deprecations	= implode( "\n", $deprecations );

			$count	= '<span class="count">'.$entry['count'].'</span>';
			$list[]	= '<li class="class">'.$classLink/*.$count*/.$classDesc.$members.$methods.$todos.$deprecations.'</li>';
		}
		$list	= $list ? '<ul id="classes">'.join( $list ).'</ul>' : "";

		$span1	= '<span id="result-counter-found">'.$countFound.'</span>';
		$span2	= '<span id="result-counter-all">'.$countAll.'</span>';
		$stats	= '<div class="result-counter">'.$span1.' / '.$span2.'</div>';
		return $stats.$list;
	}
	
	/**
	 *	@access		protected
	 *	@param		string		$string		String to check for Query Parts
	 *	@return		bool		Flag: found atleast one Query Part
	 */
	protected static function find( $string, $queries )
	{
		foreach( $queries as $queries => $query )
			if( !preg_match( "/".$query."/i", $string ) )
				return FALSE;
		return TRUE;
	}

	/**
	 *	@access		protected
	 *	@param		string		$string		String to hilight Query Parts in
	 *	@param		array		$queries	List of Query Parts to hilight
	 *	@return		string
	 */
	protected static function hilight( $string, $queries )
	{
		foreach( $queries as $nr => $query )
			$string	= preg_replace( '/('.$query.')/i', '__##'.$nr.'[[\\1]]##__', $string );
		$string	= preg_replace( '/__##([0-9]+)\[\[/', '<span class="found term-\\1">', $string );
		$string	= preg_replace( '/\]\]##__/', '</span>', $string );
		return $string;
	}
}

$query		= $_REQUEST['query'];
echo TermSearchResults::render( $query );
?>