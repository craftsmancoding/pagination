<?php
/**
 *
 * To run these tests, pass the test directory as the 1st argument to phpunit:
 *
 *   phpunit path/to/tests
 *
 * or if you're having any trouble running phpunit, download its .phar file, and 
 * then run the tests like this:
 *
 *  php phpunit.phar path/to/tests
 *
 * To run just the tests in this file, specify the file:
 *
 *  phpunit tests/autoloadTest.php
 *
 */

class pagerTest extends PHPUnit_Framework_TestCase {

    public function testPager() {
        $actual = Pagination\Pager::links(100, 0, 10);  
//        print normalize_html($actual); exit;
        $expected = '<div id="pagination"><span>1</span><a href="?&offset=10" >2</a><a href="?&offset=20" >3</a><a href="?&offset=30" >4</a><a href="?&offset=40" >5</a><a href="?&offset=50" >6</a><a href="?&offset=60" >7</a><a href="?&offset=70" >8</a><a href="?&offset=80" >9</a><a href="?&offset=90" >10</a><a href="?&offset=10" >Next &rsaquo;</a><a href="?&offset=90" >Last &raquo;</a><br/> Page 1 of 10<br/> Displaying records 1 thru 10 of 100 </div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));

        $actual = Pagination\Pager::links(100, 0, 10)->setConfig('link_cnt', 3);  
//        print normalize_html($actual); exit;
        $expected = '<div id="pagination"><span>1</span><a href="?&offset=10" >2</a><a href="?&offset=20" >3</a><a href="?&offset=10" >Next &rsaquo;</a><a href="?&offset=90" >Last &raquo;</a><br/> Page 1 of 10<br/> Displaying records 1 thru 10 of 100 </div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));

    }

    public function testOffset() {
    
        $actual = Pagination\Pager::links(100, 50);
        $expected = '<div id="pagination"><a href="?&offset=0" >&laquo; First</a><a href="?&offset=25" >&lsaquo; Prev.</a><a href="?&offset=25" >2</a><span>3</span><a href="?&offset=75" >4</a><a href="?&offset=75" >Next &rsaquo;</a><a href="?&offset=75" >Last &raquo;</a><br/>
    				Page 3 of 4<br/>
    				Displaying records 51 thru 75 of 100 </div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));
    }


    public function testCustomTpls() {
/*
        print Pagination\Pager::links(100, 10)
            ->style('default');
*/        
        $actual = Pagination\Pager::links(100)
            ->setTpl('outer','<div id="pagination">[+content+]</div>');
        $expected = '<div id="pagination"><span>1</span><a href="?&offset=25" >2</a><a href="?&offset=50" >3</a><a href="?&offset=25" >Next &rsaquo;</a><a href="?&offset=75" >Last &raquo;</a></div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));
    }
    
    public function testBaseUrl() {
    
        $actual = Pagination\Pager::links(100)
            ->setBaseUrl('http://mysite.com/index.php?page=something');

        $expected = '<div id="pagination"><span>1</span><a href="http://mysite.com/index.php?page=something&offset=25" >2</a><a href="http://mysite.com/index.php?page=something&offset=50" >3</a><a href="http://mysite.com/index.php?page=something&offset=25" >Next &rsaquo;</a><a href="http://mysite.com/index.php?page=something&offset=75" >Last &raquo;</a></div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));    
    }

//    public function testRawStyles() {
//        Pagination\Pager::style('raw');
//        $actual = Pagination\Pager::links(100);
//        $expected = '<div class="raw_pagination"><em class="current">1</em><a href="http://mysite.com/index.php?page=something&offset=25" >2</a><a href="http://mysite.com/index.php?page=something&offset=50" >3</a><a href="http://mysite.com/index.php?page=something&offset=25" >Next &raquo;</a><a href="http://mysite.com/index.php?page=something&offset=75" >Last</a><div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>';
//        $this->assertEquals(normalize_html($actual), normalize_html($expected));
//    }

    public function testDiggStyles() {
        Pagination\Pager::style('digg');
        $actual = Pagination\Pager::links(100);
        $expected = '<style>.digg_pagination{background:#fff;cursor:default;margin:10px 0}.digg_pagination a,.digg_pagination em,.digg_pagination span{padding:.2em .5em;display:block;float:left;margin-right:1px}.digg_pagination .disabled{color:#999;border:1px solid #ddd}.digg_pagination .current{font-style:normal;font-weight:700;background:#2e6ab1;color:#fff;border:1px solid #2e6ab1}.digg_pagination a{text-decoration:none;color:#105cb6;border:1px solid #9aafe5}.digg_pagination a:focus,.digg_pagination a:hover{color:#003;border-color:#003}.digg_pagination .page_info{background:#2e6ab1;color:#fff;padding:.4em .6em;width:22em;margin-bottom:.3em;text-align:center}.digg_pagination .page_info b{color:#003;background:#6aa6ed;padding:.1em .25em}.digg_pagination:after{content:".";display:block;height:0;clear:both;visibility:hidden}* html .digg_pagination{height:1%}.digg_pagination .page-count{margin-top:5px}.digg_pagination .page-count,.digg_pagination .page-display{color:#2E6AB1;font-size:12px}.clear:after,.clear:before{content:"\0020";display:block;height:0;visibility:hidden}.clear:after{clear:both}.clear{zoom:1}</style><div class="digg_pagination"><em class="current">1</em><a href="http://mysite.com/index.php?page=something&offset=25" >2</a><a href="http://mysite.com/index.php?page=something&offset=50" >3</a><a href="http://mysite.com/index.php?page=something&offset=25" >Next &raquo;</a><a href="http://mysite.com/index.php?page=something&offset=75" >Last</a><div class="clear">&nbsp;</div><div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));
    }
    public function testAppleStyles() {
        Pagination\Pager::style('apple');
        $actual = Pagination\Pager::links(100);
        $expected = '<style>.apple_pagination{margin:10px 0;background:#f1f1f1;border:1px solid #e5e5e5;text-align:center;padding:1em;cursor:default}.apple_pagination a,.apple_pagination span{padding:.2em .3em}.apple_pagination .disabled{color:#aaa}.apple_pagination .current{font-style:normal;font-weight:700;background-color:#bebebe;display:inline-block;width:1.4em;height:1.4em;line-height:1.5;-moz-border-radius:1em;-webkit-border-radius:1em;border-radius:1em;text-shadow:rgba(255,255,255,.8) 1px 1px 1px}.apple_pagination a{text-decoration:none;color:#000}.apple_pagination a:focus,.apple_pagination a:hover{text-decoration:underline}.apple_pagination .page-count{margin-top:10px}.apple_pagination .page-count,.apple_pagination .page-display{font-size:11px;text-align:center}</style><div class="apple_pagination"><em class="current">1</em><a href="http://mysite.com/index.php?page=something&offset=25" >2</a><a href="http://mysite.com/index.php?page=something&offset=50" >3</a><a href="http://mysite.com/index.php?page=something&offset=25" >Next &raquo;</a><a href="http://mysite.com/index.php?page=something&offset=75" >Last</a><div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));
    }
    public function testFlickrStyles() {
        Pagination\Pager::style('flickr');
        $actual = Pagination\Pager::links(100);
        $expected = '<style>.flickr_pagination{margin:10px 0;text-align:center;padding:.3em;cursor:default}.flickr_pagination a,.flickr_pagination em,.flickr_pagination span{padding:.2em .5em}.flickr_pagination .disabled{color:#aaa}.flickr_pagination .current{font-style:normal;font-weight:700;color:#ff0084}.flickr_pagination a{border:1px solid #ddd;color:#0063dc;text-decoration:none}.flickr_pagination a:focus,.flickr_pagination a:hover{border-color:#036;background:#0063dc;color:#fff}.flickr_pagination .page_info{color:#aaa;padding-top:.8em}.flickr_pagination .next_page,.flickr_pagination .previous_page{border-width:2px}.flickr_pagination .previous_page{margin-right:1em}.flickr_pagination .next_page{margin-left:1em}.flickr_pagination .page-count{margin-top:10px}.flickr_pagination .page-count,.flickr_pagination .page-display{font-size:11px;text-align:center;color:#bbb}</style><div class="flickr_pagination"><em class="current">1</em><a href="http://mysite.com/index.php?page=something&offset=25" >2</a><a href="http://mysite.com/index.php?page=something&offset=50" >3</a><a href="http://mysite.com/index.php?page=something&offset=25" >Next &raquo;</a><a href="http://mysite.com/index.php?page=something&offset=75" >Last</a><div class="page-count">Page 1 of 4</div><div class="page-display">Displaying records 1 thru 25 of 100</div></div>';
        $this->assertEquals(normalize_html($actual), normalize_html($expected));
    }
}