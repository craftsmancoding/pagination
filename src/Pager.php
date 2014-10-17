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
	   |       |   | | |    |      +----- last
	   |       |   | | |    +------------ next
	   |       |   | | +----------------- current
	   |       |   +-+------------------- page
	   |       +------------------------- prev
	   +--------------------------------- first

\_________________________________________________/
                    |
                    +-------------------- outer


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
	 * Active formatting templates (tpls) -- copied from the styles
	 */
	private static $tpls = array();

    /**
     * A library of styles
     *
     */	
	private static $styles = array(
        'default' => array(
            'first' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&laquo; First</a>  ',
            'last' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Last &raquo;</a>',
            'prev' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&lsaquo; Prev.</a> ',
            'next' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Next &rsaquo;</a>',
            'current' => ' <span>[+page_number+]</span> ',
            'page' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>[+page_number+]</a> ',
            'outer' => '<div id="pagination">[+content+]<br/>
    				Page [+current_page+] of [+page_count+]<br/>
    				Displaying records [+first_record+] thru [+last_record+] of [+record_count+]
    			</div>',
    	),
    	'raw' => array(
            'first' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>First</a>',
            'last' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Last</a>',
            'prev' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&laquo; Previous</a> ',
            'next' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Next &raquo;</a>',
            'current' => '<em class="current">[+page_number+]</em>',
            'page' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>[+page_number+]</a> ',
            'outer' => '<div class="raw_pagination">[+content+]<div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>',
    	),
    	'digg' => array(
            'first' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>First</a>',
            'last' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Last</a>',
            'prev' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&laquo; Previous</a> ',
            'next' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Next &raquo;</a>',
            'current' => '<em class="current">[+page_number+]</em>',
            'page' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>[+page_number+]</a> ',
            'outer' => '<style>.digg_pagination{background:#fff;cursor:default;margin:10px 0}.digg_pagination a,.digg_pagination em,.digg_pagination span{padding:.2em .5em;display:block;float:left;margin-right:1px}.digg_pagination .disabled{color:#999;border:1px solid #ddd}.digg_pagination .current{font-style:normal;font-weight:700;background:#2e6ab1;color:#fff;border:1px solid #2e6ab1}.digg_pagination a{text-decoration:none;color:#105cb6;border:1px solid #9aafe5}.digg_pagination a:focus,.digg_pagination a:hover{color:#003;border-color:#003}.digg_pagination .page_info{background:#2e6ab1;color:#fff;padding:.4em .6em;width:22em;margin-bottom:.3em;text-align:center}.digg_pagination .page_info b{color:#003;background:#6aa6ed;padding:.1em .25em}.digg_pagination:after{content:".";display:block;height:0;clear:both;visibility:hidden}* html .digg_pagination{height:1%}.digg_pagination .page-count{margin-top:5px}.digg_pagination .page-count,.digg_pagination .page-display{color:#2E6AB1;font-size:12px}.clear:after,.clear:before{content:"\0020";display:block;height:0;visibility:hidden}.clear:after{clear:both}.clear{zoom:1}</style><div class="digg_pagination">[+content+]<div class="clear">&nbsp;</div><div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>',
    	),
    	'apple' => array(
            'first' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>First</a>',
            'last' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Last</a>',
            'prev' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&laquo; Previous</a> ',
            'next' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Next &raquo;</a>',
            'current' => '<em class="current">[+page_number+]</em>',
            'page' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>[+page_number+]</a> ',
            'outer' => '<style>.apple_pagination{margin:10px 0;background:#f1f1f1;border:1px solid #e5e5e5;text-align:center;padding:1em;cursor:default}.apple_pagination a,.apple_pagination span{padding:.2em .3em}.apple_pagination .disabled{color:#aaa}.apple_pagination .current{font-style:normal;font-weight:700;background-color:#bebebe;display:inline-block;width:1.4em;height:1.4em;line-height:1.5;-moz-border-radius:1em;-webkit-border-radius:1em;border-radius:1em;text-shadow:rgba(255,255,255,.8) 1px 1px 1px}.apple_pagination a{text-decoration:none;color:#000}.apple_pagination a:focus,.apple_pagination a:hover{text-decoration:underline}.apple_pagination .page-count{margin-top:10px}.apple_pagination .page-count,.apple_pagination .page-display{font-size:11px;text-align:center}</style><div class="apple_pagination">[+content+]<div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>',
    	),
    	'flickr' => array(
            'first' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>First</a>',
            'last' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Last</a>',
            'prev' => '<a href="[+base_url+]&offset=[+offset+]" [+extra+]>&laquo; Previous</a> ',
            'next' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>Next &raquo;</a>',
            'current' => '<em class="current">[+page_number+]</em>',
            'page' => ' <a href="[+base_url+]&offset=[+offset+]" [+extra+]>[+page_number+]</a> ',
            'outer' => '<style>.flickr_pagination{margin:10px 0;text-align:center;padding:.3em;cursor:default}.flickr_pagination a,.flickr_pagination em,.flickr_pagination span{padding:.2em .5em}.flickr_pagination .disabled{color:#aaa}.flickr_pagination .current{font-style:normal;font-weight:700;color:#ff0084}.flickr_pagination a{border:1px solid #ddd;color:#0063dc;text-decoration:none}.flickr_pagination a:focus,.flickr_pagination a:hover{border-color:#036;background:#0063dc;color:#fff}.flickr_pagination .page_info{color:#aaa;padding-top:.8em}.flickr_pagination .next_page,.flickr_pagination .previous_page{border-width:2px}.flickr_pagination .previous_page{margin-right:1em}.flickr_pagination .next_page{margin-left:1em}.flickr_pagination .page-count{margin-top:10px}.flickr_pagination .page-count,.flickr_pagination .page-display{font-size:11px;text-align:center;color:#bbb}</style><div class="flickr_pagination">[+content+]<div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>',
    	),
	);
	
	/**
	 * Configuration for settings that don't change often.
	 */
	private static $config = array(
	   'link_cnt' => 10, // how many page links are shown total
	   'jump_size' => 1, // when you click on prev/next, how many pages are jumped forward or backward?
	);
	
	// Contains all placeholders passed to the outer Tpl
	public static $properties = array(
	   'offset' => 0,
	   'page_count' => '',
	   'per_page' => '',
	);

    /**
     * The instance passed around while method chaining
     */
    public static $instance;

    /**
     * Where the magic happens: Generates final output
     */
    public function __toString() {
		// No point in doing pagination if there aren't enough records
		if (self::$properties['record_count'] <= self::$properties['per_page']) {
			return '';
		}
        if (!isset(self::$properties['base_url'])) {
            self::$properties['base_url'] = '?';
        }   
        if (empty(self::$tpls)) {
            self::style('default');
        }
        // Final calculations based on the settings
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

		static::$properties['first_record'] = self::$properties['offset'] + 1;

		if ( self::$properties['offset'] + self::$properties['per_page'] >= self::$properties['record_count']) {
			self::$properties['last_record'] = self::$properties['record_count'];
		}
		else {
			self::$properties['last_record'] = self::$properties['offset'] + self::$properties['per_page'];
		}
		
        // The parsing     
		self::$properties['content'] = static::_parse_first();
		self::$properties['content'] .= static::_parse_prev();
		self::$properties['content'] .= static::_parse_pagination_links();
		self::$properties['content'] .= static::_parse_next();
		self::$properties['content'] .= static::_parse_last();
		$first_pass = static::_parse(static::$tpls['outer'], self::$properties);
		return static::_parse($first_pass, self::$properties,true);
    }
    
	/**
	 * Parses the first template (first)
	 * which always has offset 0
	 * @return string
	 */
	private static function _parse_first() {
		if (self::$properties['offset'] > 0) {
			return self::_parse(static::$tpls['first'], array('offset'=> 0, 'page_number'=> 1 ));
		} 
        return '';
	}


	/**
	 * Parse the last template (last)
	 *
	 * @return string
	 */
	private static function _parse_last() {
		$page_number = self::$properties['page_count'];
		$offset = self::page_to_offset($page_number, self::$properties['per_page']);
		if (self::$properties['current_page'] < self::$properties['page_count']) {
			return static::_parse(static::$tpls['last'], array(
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
				$output .= static::_parse( static::$tpls['current'], array('offset'=> $offset, 'page_number'=> $page));
			} else {
				$output .= static::_parse(static::$tpls['page'], array('offset'=> $offset, 'page_number'=> $page));
			}
		}
		return $output;
	}


	/**
	 * Parse the tpl used for the "Next >" link.
	 *
	 * @return string
	 */
	private static function _parse_next() {
		$page_number = self::_get_next_page( self::$properties['current_page'], self::$properties['page_count'] );
		$offset = self::page_to_offset( $page_number, self::$properties['per_page'] );
		if ( self::$properties['current_page'] < self::$properties['page_count'] ) {
			return static::_parse(static::$tpls['next'], array('offset'=> $offset, 'page_number'=> $page_number));
		} 
        return '';
	}

	/**
	 * Parse the tpl used for the "< Prev" link.
	 *
	 * @return string
	 */
	private static function _parse_prev() {
		$page_number = self::_get_prev_page( self::$properties['current_page'], self::$properties['page_count'] );
		$offset = self::page_to_offset( $page_number, self::$properties['per_page'] );
		if (self::$properties['offset'] > 0) {
			return static::_parse( static::$tpls['prev'], array('offset'=> $offset, 'page_number'=> $page_number) );
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
	private static function _get_highest_visible_page($current_pg, $total_pgs_shown, $total_pgs) {
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
	 * @param boolean $remove_unused set to true if you want to clean out unused placeholders
	 * @return string
	 */
	private static function _parse($tpl, $record,$remove_unused=false) {
		foreach ($record as $key => $value) {
			$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
		}
		if ($remove_unused) {
            $tpl = preg_replace('/'.preg_quote('[+').'(.*?)'.preg_quote('+]').'/', '', $tpl);
        }
        return trim($tpl);
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
	public static function links($record_count, $offset=0, $per_page=25,$base_url=null) {
        self::$instance = null; // <-- init
        self::$properties['record_count'] = (int) $record_count;
        self::$properties['per_page'] = (int) $per_page;
        self::$properties['offset'] = (int) $offset;
        if ($base_url) self::$properties['base_url'] = $base_url;  // <-- set only conditionally
		self::$properties['page_count'] = ceil(self::$properties['record_count'] / self::$properties['per_page']);
		self::$properties['current_page'] = self::offset_to_page( self::$properties['offset'], self::$properties['per_page'] );
        return self::returnInstance(); // for method chaining... see __toString() for the parsing.
	}

    /**
     * Used in method chaining: we return an instance of this object
     * so we can either print its result via __toString() or chain
     * additional methods onto it.  Any method that is available to method chaining
     * should return this function instead of returning normal output.
     */
    public static function returnInstance() {
        if (empty(static::$instance)) {
            static::$instance = new Pager();
        }    
        return static::$instance;
    }

	//------------------------------------------------------------------------------
	/**
	 * This is the base url used when creating all the links to all the pages.
	 * WARNING: use a clean URL!!! Filter out any Javascript or anything that might
	 * lead to an XSS attack before you set this value -- this function does not do
	 * any of its own filtering.
     * For GET links, this is would simply be "?"
	 *
	 * @param string $base_url
	 */
	public static function setBaseUrl($base_url) {
        //if (strpos($base, '?') === false) {
		//	$base_url = $base_url . '?';
		//}	
		self::$properties['base_url'] = $base_url;
        return self::returnInstance();		
	}
	
	//------------------------------------------------------------------------------
    /**
     * Set a config setting.
     * @param string $key name of the setting
     * @param integer $value the new value.
     * @throws Exception
     * @return object
     */
	public static function setConfig($key, $value) {
		$value = (int) $value;
		if (!array_key_exists($key, self::$config)) {
            throw new Exception('Invalid configuration key '.$key);
		}
		elseif ($value <= 0) {
            throw new Exception('Configuration settings require integer values greater than 0');
		}
        self::$config[$key] = $value;
        return self::returnInstance();
	}
	

	//------------------------------------------------------------------------------
    /**
     * Set a single formatting tpl.
     * @param string $tpl one of the named tpls
     * @param string $content
     * @throws Exception
     * @return object
     */
	public static function setTpl($tpl, $content) {
		if (!is_scalar($content)) {
			throw new Exception("Content for $tpl tpl must be a string.");
		}
		if (in_array($tpl, array('first','last','prev','next','current',
			'page','css','outer'))) {
			self::$tpls[$tpl] = $content;
		}
		else {
			throw new Exception ('Unknown tpl ' . strip_tags($tpl));
		}
        return self::returnInstance();
	}

	//------------------------------------------------------------------------------
	/**
	 * Set all the tpls in one go by supplying an array.  You must supply 
	 * a *complete* set of tpls to this function! A missing key is equivalent to 
	 * supplying an empty string.
	 * @throws Exception
	 * @param array $tpls, associative array with keys
     * @return object
	 */
	public static function setTpls($tpls) {
		if (is_array($tpls)) {
			$tpls = array_merge(array('first'=>'','last'=>'','prev'=>'',
			'next'=>'','current'=>'','page'=>'','css'=>'','outer'=>''), $tpls);
			foreach($tpls as $tpl => $v) {
				self::setTpl($tpl,$v);
			}
		}
		else {
            throw new Exception ('setTpls() requires array input.');
		}
        return self::returnInstance();		
	}
	
	/**
	 * Set the tpls to a particular style.
	 * @param string $style identifies a key in the $styles array.
     * @return object
	 */
	public static function style($style) {

        if (array_key_exists($style, self::$styles)) {
            self::$tpls = self::$styles[$style];
        }
        else {
            self::$tpls = self::$styles['default'];
        }
        return self::returnInstance();        
	}
}

/*EOF*/