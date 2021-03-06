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

class autoloadTest extends PHPUnit_Framework_TestCase {

        
    public function testLoad() {
        $P = new Pagination\Pager();
        $this->assertTrue(is_object($P), 'Pager.'); 
    }
}