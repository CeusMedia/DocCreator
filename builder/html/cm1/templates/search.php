<?php
/**
 *	Reads serialized List of Classes and Terms and matches with one or more Search Query Terms.
 */
class TermSearch
{
	/**
	 *	Constructor, reads serialized List of Classes and Terms.
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
	 *	@param		int			$limit		Number of Result Items
	 *	@return		array
	 */
	public function query( $query, $limit = NULL )
	{
		$list		= array();
		$queries	= $this->getQueryParts( $query );
		foreach( $this->data as $item )
		{
			$count = 0;
			foreach( $queries as $query )
			{
				$result	= $this->queryTerms( $item['terms'], $query );
				if( !$result )
				{
					$count = 0;
					break;
				}
				$count += $result;
			}
			if( !$count )
				continue;
			$item['count']	= $count;
			$list[$count][]	= $item;
		}
		krsort( $list );
		$data	= array();
		foreach( $list as $sublist )
			foreach( $sublist as $item )
				$data[]	= $item;
		if( is_int( $limit ) && $limit )
			$data	= array_slice( $data, 0, $limit );
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
		
	protected function getQueryParts( $query )
	{
		$parts	= explode( " ", $query );
		foreach( $parts as $nr => $part )
			if( !trim( $part ) )
				unset( $parts[$nr] );
		return $parts;
	}

	protected function queryTerms( $terms, $query )
	{
		$count	= 0;
		foreach( $terms as $term => $number )
			if( preg_match( "/".$query."/i", $term ) )
				$count += $number;
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
	 *	@return		string
	 */
	public static function render( $query )
	{
		$search		= new TermSearch;
		$countAll	= $search->getItemCount();
		$data		= $search->query( $query );
		$countFound	= count( $data );
		$list	= array();
		foreach( $data as $entry )
		{
			$link	= '<a href="files/'.$entry['fileId'].'.html?query='.urlencode($query).'">'.$entry['className'].'</a>&nbsp;<small>('.$entry['count'].')</small>';
			$count	= '<span class="count">'.$entry['count'].'</span>';
			$list[]	= '<li>'.$link./*$count.*/'</li>';
		}
		$list	= $list ? '<ul id="result-list">'.join( $list ).'</ul>' : "";

		$span1	= '<span id="result-counter-found">'.$countFound.'</span>';
		$span2	= '<span id="result-counter-all">'.$countAll.'</span>';
		$stats	= '<div class="result-counter">'.$span1.' / '.$span2.'</div>';
		return $stats.$list;
	}
}

$query		= $_REQUEST['query'];
echo TermSearchResults::render( $query );
?>