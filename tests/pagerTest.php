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
}