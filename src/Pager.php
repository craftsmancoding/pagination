<?php
/**
 * Create pagination links for any URL
 * 
Pagination: a library for generating links to pages of results, allowing you
to retrieve a limited number of records from the database with each query.
Note that accurate pagination requires that you count the number of available 
records.

Example of Generated Links:

			<< First < Prev.  1 2 3 4 5  Next >  Last >>

Keys define which parameter to look for in the URL.
number_of_pagination_links_displayed controls whether you have something like
<<prev 1 2 3 next>> or 1 2 3 4 5 6 ... 

	Templates used for formatting are assembled in the following manner:
	
	E.g. if the current page is 3:
	
	<<First <<Prev 1 2 3 Next>> Last>>
	\_____/ \____/ ^ ^ ^ \____/ \____/
	   |       |   | | |    |      +----- lastTpl
	   |       |   | | |    +------------ nextTpl
	   |       |   | | +----------------- currentPageTpl
	   |       |   +-+------------------- pageTpl
	   |       +------------------------- prevTpl
	   +--------------------------------- firstTpl

\_________________________________________________/
                    |
                    +-------------------- outerTpl


Make sure you've filtered any GET values before using this library!
 
 * USAGE:
 * 
 * There are 2 ways to identify page numbers during pagination. The most obvious one
 * is that we number each page: 1,2,3.  This corresponds to pagination links
 * like mypage.php?page=3 for example.
 * 
 * 		require_once 'Pagination.php';
 * 		$p = new Pager();
 * 		$offset = $p->page_to_offset($_GET['page'], $_GET['rpp']);
 * 		$p->set_offset($offset); //
 * 		$p->set_per_page($_GET['rpp']);  // You can optionally expose this to the user.
 * 		$p->extra = 'target="_self"'; // optional
 * 		print $p->paginate(100); // 100 is the count of records
 * 
 * The other way to identify page numbers is via an offset of the records. This is
 * a bit less intuitive, but it is more flexible if you ever want to let the user
 * change the # of results shown per page. Imagine if someone bookmarked a URL
 * with ?page=3 on it, and then adjusted the # of records per page from 10 to 100.
 * The page would contain an entirely different set of records, whereas with the offset
 * method, e.g. ?offset=30, the page would at least start with the same records no matter
 * if the # of records per page changed.
 * 
 * 
 * 
 * AUTHOR: everett@craftsmancoding.com (revised 2014)
 *
 */
namespace Pagination; 
class Pager {

	/**
	 * Formatting templates (tpls)
	 */
	private static $tpls = array(
        'firstTpl' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&laquo; First</a> &nbsp;',
        'lastTpl' => '&nbsp;<a href="[+base_url+]&offset=[+offset+]" [+extra+]>Last &raquo;</a>',
        'prevTpl' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&lsaquo; Prev.</a>&nbsp;',
        'nextTpl' => '&nbsp;<a href="[+base_url+]&offset=[+offset+]" [+extra+]>Next &rsaquo;</a>',
        'currentPageTpl' => '&nbsp;<span>[+page_number+]</span>&nbsp;',
        'pageTpl' => '&nbsp;<a href="[+base_url+]&offset=[+offset+]" [+extra+]>[+page_number+]</a>&nbsp;',
        'outerTpl' => '<div id="pagination">[+content+]<br/>
				Page [+current_page+] of [+page_count+]<br/>
				Displaying records [+first_record+] thru [+last_record+] of [+record_count+]
			</div>',
	);
	
	/**
	 * Configuration for settings that don't change often.
	 */
	private static $config = array(
	   'link_cnt' => 10, // how many links are shown total
	   'jump_size' => 1, // when you click on prev/next, how many pages are jumped forward or backward?
	   'per_page' => 25,
	);
	
	// Contains all placeholders passed to the outerTpl
	public static $properties = array(
	   'offset' => 0,
	   'page_count' => '',
	   'per_page' => '',
	);


	/**
	 * Parses the first template (firstTpl)
	 *
	 * @return string
	 */
	private static function _parse_firstTpl() {
		if (self::$properties['offset'] > 0) {
			return self::_parse(static::$tpls['firstTpl'], array('offset'=> 0, 'page_number'=> 1 ));
		} 
        return '';
	}


	/**
	 * Parse the last template (lastTpl)
	 *
	 * @return string
	 */
	private static function _parse_lastTpl() {
		$page_number = self::$properties['page_count'];
		$offset = self::page_to_offset($page_number, self::$properties['per_page']);
		if (self::$properties['current_page'] < self::$properties['page_count']) {
			return static::_parse(static::$tpls['lastTpl'], array(
				'offset'=> $offset, 
				'page_number'=> $page_number
				)
			);
		} 
        return '';
	}

	/**
	 * @return string
	 */
	private static function _parse_pagination_links() {
		$output = '';
		for ( $page = self::$properties['lowest_visible_page']; $page <= self::$properties['highest_visible_page']; $page++ ) {
			$offset = self::page_to_offset( $page, self::$properties['per_page']);

			if ( $page == self::$properties['current_page'] ) {
				$output .= static::_parse( static::$tpls['currentPageTpl'], array('offset'=> $offset, 'page_number'=> $page));
			} else {
				$output .= static::_parse(static::$tpls['pageTpl'], array('offset'=> $offset, 'page_number'=> $page));
			}
		}
		return $output;
	}


	/**
	 * Parse the tpl used for the "Next >" link.
	 *
	 * @return string
	 */
	private static function _parse_nextTpl() {
		$page_number = self::_get_next_page( self::$properties['current_page'], self::$properties['page_count'] );
		$offset = self::page_to_offset( $page_number, self::$properties['per_page'] );
		if ( self::$properties['current_page'] < self::$properties['page_count'] ) {
			return static::_parse(static::$tpls['nextTpl'], array('offset'=> $offset, 'page_number'=> $page_number));
		} 
        return '';
	}

	/**
	 * Parse the tpl used for the "< Prev" link.
	 *
	 * @return string
	 */
	private static function _parse_prevTpl() {
		$page_number = self::_get_prev_page( self::$properties['current_page'], self::$properties['page_count'] );
		$offset = self::page_to_offset( $page_number, self::$properties['per_page'] );
		if (self::$properties['offset'] > 0) {
			return static::_parse( static::$tpls['prevTpl'], array('offset'=> $offset, 'page_number'=> $page_number) );
		}
	}

	/**
	 * A calcuation to get the highest visble page when displaying a cluster of 
	 * links, e.g. 4 5 6 7 8  -- this function is what calculates that "8" is the 
	 * highest visible page.
	 *
	 * @param integer $current_pg
	 * @param integer $total_pgs_shown
	 * @param integer $total_pgs
	 * @return integer
	 */
	private function _get_highest_visible_page($current_pg, $total_pgs_shown, $total_pgs) {
		//if ($total_pgs_shown is even)
		$half = floor($total_pgs_shown / 2);

		$high_page = $current_pg + $half;
		$output = '';
		if ($high_page < $total_pgs_shown) {
			$output = $total_pgs_shown;
		} else {
			$output = $high_page;
		}
		if ($output > $total_pgs) {
			$output = $total_pgs;
		}
		return $output;
	}

	/**
	 * Calculates the smallest of the visible pages, keeping the current page floating
	 * in the center.
	 *
	 * @param integer $current_pg
	 * @param integer $pgs_visible
	 * @param integer $total_pgs
	 * @return integer
	 */
	private static function _get_lowest_visible_page($current_pg, $pgs_visible, $total_pgs) {
		//if ($pgs_visible is even, subtract the 1)
		$half = floor($pgs_visible / 2);
		$output = 1;
		$low_page = $current_pg - $half;
		if ($low_page < 1) {
			$output = 1;
		} else {
			$output = $low_page;
		}
		if ( $output > ($total_pgs - $pgs_visible) ) {
			$output = $total_pgs - $pgs_visible + 1;
		}
		if ($output < 1) {
			$output = 1;
		}
		return $output;
	}

	//------------------------------------------------------------------------------
	/**
	 * The page targeted by the Next link. 
	 *
	 * @param integer $current_pg
	 * @param integer $total_pgs
	 * @return integer
	 */
	private static function _get_next_page($current_pg, $total_pgs) {
		$next_page = $current_pg + self::$config['jump_size'];
		if ($next_page > $total_pgs) {
			return $total_pgs;
		} 
        return $next_page;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * The page targeted by the Prev link.
	 *
	 * @param integer $current_pg
	 * @param integer $total_pgs
	 * @return integer
	 */
	private static function _get_prev_page($current_pg, $total_pgs) {
		$prev_page = $current_pg - self::$config['jump_size'];
		if ($prev_page < 1) {
			return 1;
		} 
		else {
			return $prev_page;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Simple parsing function to replace [+placeholders+] with value
	 *
	 * @param string $tpl
	 * @param array $record
	 * @return string
	 */
	private static function _parse($tpl, $record) {
		foreach ($record as $key => $value) {
			$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
		}
		return $tpl;
	}
		
	//------------------------------------------------------------------------------
	//! PUBLIC FUNCTIONS
	//------------------------------------------------------------------------------
	/**
	 * convert an offset number to a page number
	 *
	 * @param integer $offset
	 * @param integer $per_page (optional) defaults to the set self::$properties['per_page']
	 * @return integer
	 */
	public static function offset_to_page($offset, $per_page=null) {
		$offset = (int) $offset;
		if ($per_page) {
			$per_page = (int) $per_page;
		}
		else {
			$per_page = self::$properties['per_page'];
		}
		if (is_numeric($per_page) && $per_page > 0) {
			return (floor($offset / $per_page)) + 1;
		} else {
			return 1;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Convert page number to an offset
	 *
	 * @param integer $page
	 * @param integer $per_page
	 * @return integer
	 */
	public static function page_to_offset($page, $per_page=null) {
		$page = (int) $page;
		if ($per_page) {
			$per_page = (int) $per_page;
		}
		else {
			$per_page = self::$properties['per_page'];
		}
		if (is_numeric($page) && $page > 1) {
			return ($page - 1) * $per_page;
		} else {
			return 0;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * This is THE primary interface for the whole library = Get the goods!
	 * INPUT: (int) the # of records you're paginating.
	 * OUTPUT: formatted links
	 *
	 * @param integer $record_count
	 * @param integer $per_page -- how many records to show on each page
	 * @param string $base_url
	 * @return string	html used for pagination (formatted links)
	 */
	public static function links(int $per_page, int $record_count=1000, int $offset=0,$base_url='?') {

		// No point in doing pagination if there aren't enough records
		if ($record_count <= $per_page) {
			return '';
		}
        self::$properties['per_page'] = $per_page;
		self::$properties['page_count'] = ceil($record_count / self::$properties['per_page']);
		self::$properties['current_page'] = self::offset_to_page( self::$properties['offset'], self::$properties['per_page'] );

		self::$properties['lowest_visible_page'] = self::_get_lowest_visible_page(
			self::$properties['current_page']
			, self::$config['link_cnt']
			, self::$properties['page_count']
		);

		self::$properties['highest_visible_page'] = self::_get_highest_visible_page (
			self::$properties['current_page']
			, self::$config['link_cnt']
			, self::$properties['page_count']
		);

		static::$parameters['first_record'] = self::$properties['offset'] + 1;

		if ( self::$properties['offset'] + self::$properties['per_page'] >= $record_count) {
			self::$properties['last_record'] = $record_count;
		}
		else {
			self::$properties['last_record'] = self::$properties['offset'] + self::$properties['per_page'];
		}

		// We need keys from config
		self::$properties['record_count'] = $record_count;

		self::$properties['content'] = static::_parse_firstTpl();
		self::$properties['content'] .= static::_parse_prevTpl();
		self::$properties['content'] .= static::_parse_pagination_links();
		self::$properties['content'] .= static::_parse_nextTpl();
		self::$properties['content'] .= static::_parse_lastTpl();
		$first_pass = static::_parse(static::$tpls['outerTpl'], self::$properties);
		
		return static::_parse($first_pass, self::$properties);
	}

	//------------------------------------------------------------------------------
	/**
	 * This is the base url used when creating all the links to all the pages.
	 * WARNING: use a clean URL!!! Filter out any Javascript or anything that might
	 * lead to an XSS attack before you set this value -- this function does not do
	 * any of its own filtering.
	 * The base_url is intented to be manually set, not open to user input.
	 *
	 * @param string $base_url
	 */
	public static function set_base_url($base_url) {
		if (strpos($str, '?') === false) {
			$base_url = $base_url . '?';
		}

		self::$properties['base_url'] = $base_url;
	}

	//------------------------------------------------------------------------------
	/**
	 * Controls how many pages are flipped forwards/backwards when the prev/next
	 * links are clicked. With a value of 1, this operates like a book -- flip
	 * forward or back one page at a time.
	 * Giant leaps are possible e.g. if you display 10 links at a time and you flip 
	 * 10 pages forward, e.g. from displaying pages 11 - 20 to 21 - 30, etc.
	 *
	 * @param integer $pgs 1 or greater
	 * @return void
	 */
	public static function set_jump_size($pgs) {
		$pgs = (int) $pgs;
		if ($pgs > 0) {
			self::$config['jump_size'] = $pgs;	
		}
		else {
			throw new Exception ('set_jump_size() requires an integer greater than 0');
		}	
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Set the number of pagination links to display, e.g. 3 might generate a set 
	 * of links like this:
	 *
	 *		<< First < Prev 4 5 6 Next > Last >>
	 *
	 * Whereas setting a value of 6 might generate a set of links like this:
	 *
	 *		<< First < Prev 4 5 6 7 8 9 Next > Last >>
	 *
	 * @param integer $cnt the total number of links to show
	 * @return void
	 */
	public static function set_link_cnt($cnt) {
		$cnt = (int) $cnt;
		if ($cnt > 0) {
			self::$config['link_cnt'] = $cnt;
		}
		else {
			throw new Exception('set_link_cnt() requires an integer greater than 0');
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Goes thru integer filter; this one IS expected to get its input from users
	 * or from the $_GET array, so using (int) type-casting is a heavy-handed filter.
	 *
	 * @param integer $offset
	 */
	public static function set_offset($offset) {
		$offset = (int) $offset;
		if ($offset >= 0 ) {
			self::$properties['offset'] = $offset;
		}
		else {
			self::$properties['offset'] = 0;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Set the number of results to show per page.
	 *
	 * @param integer $per_page 
	 */
	public function set_per_page($per_page) {
		$per_page = (int) $per_page;
		if ($per_page > 0 ) {
			self::$properties['per_page'] = $per_page;
		}
		else {
            throw new Exception("set_per_page() requires an integer greater than zero.");
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Set a single formatting tpl.
	 * @param string $tpl one of the named tpls
	 * @param string $content
	 */
	public static function setTpl($tpl, $content) {
		if (!is_scalar($content)) {
			throw new Exeption("Content for $tpl tpl must be a string.");
		}
		if (in_array($tpl, array('firstTpl','lastTpl','prevTpl','nextTpl','currentPageTpl',
			'pageTpl','outerTpl'))) {
			self::$tpls[$tpl] = $content;
		}
		else {
			throw new Exception ('Unknown tpl ' . strip_tags($tpl));
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Set all the tpls in one go by supplying an array.  You must supply 
	 * a *complete* set of tpls to this function! A missing key is equivalent to 
	 * supplying an empty string.
	 *
	 * @param array $tpls, associative array with keys 
	 */
	public static function setTpls($tpls) {
		if (is_array($tpls)) {
			$tpls = array_merge(array('firstTpl'=>'','lastTpl'=>'','prevTpl'=>'',
			'nextTpl'=>'','currentPageTpl'=>'','pageTpl'=>'','outerTpl'=>''), $tpls);
			foreach($tpls as $tpl => $v) {
				self::setTpl($tpl,$v);
			}
		}
		else {
            throw new Exception ('setTpls() requires array input.');
		}
	}
}

/*EOF*/