# Pagination

A simple pagination library for the common folks: this generates links for flipping through pages of records, now including documentation!  Although other libraries may have vastly superior features, this humble library attempts to make due with examples and explanations.  If you are a super genius who grok's source-code with one glance then those other libraries work just fine for you.

## Overview

You've seen pagination links all over the place: you deal with databases and record-sets for a short while
and you see the need to flip through pages of the records using links that might look something like:

    << First < Prev.  1 2 3 4 5  Next >  Last >>

That's what we're creating here: pagination links with GET parameters to control the current page.  It may surprise you, but to create pagination links, the only things you truly need are:

* the count of the total records available 
* how many records you want to show per page
* an offset from the start (i.e. which page are you starting on)
* the URL of the current page.

Other stuff is optional for fine-tuning, but that's it.  Some pagination classes are bound with the controller and model (e.g. Laravel's) so you don't need to supply them as much information, but because this is a stand-alone class, you have to do things a bit more manually.

Let's take a closer look at how to make this work.

-----------------------------

## Paginating Records

Cut to the chase.  How to paginate records?  


**Syntax** `links( integer $record_count, integer $offset=0, integer $per_page=25, string $base_url=null)`

Example:

    <?php
    $cnt = count_my_results();
    $offset = (int) (isset($_GET['offset'])) ? $_GET['offset'] : 0;
    print Pagination\Pager::links($cnt,$offset);
    ?>
    
Or, more namespacey in your class somewhere:

    <?php
    use Pagination;
    class Classy {
        public function functional() {
            $cnt = $this->count_my_results();
            $offset = (int) (isset($_GET['offset'])) ? $_GET['offset'] : 0;
            return Pager::links($cnt,$offset);
        }
    }
    ?>

Example with a Base URL:

    <?php
        print Pagination\Pager::links(100)
            ->setBaseUrl('http://yoursite.com/index.php?page=something');
    ?>

This is critical if your page's URL relies on GET parameters -- because the pagination links also rely on URL parameters, you need to set the base URL so the links know which parameters stay fixed and constant between page flips.


### What you gotta Do

Just a reminder/recap...

**Count the results**: how you count the available results depends entirely on your particular framework or functions.  Most database drivers will include options for returning a count of the number of rows that matched the given query.  Because this library does not try to be omniscient and tie into your underlying framework or drivers, you must throw it a bone and tell the Pager::links() function how many records you're dealing with.

**Read the Offset**: in order to have the links dynamically adjust as you flip through the result set, you need to pass the offset value to the Pager::links() function.  This should work in conjunction with whatever parameter your code is using to offset the raw database query. 

**Get the URL of the current page**: how this is done depends on your particular framework or application.  Use the **setBaseUrl()** if you need to customize this -- this is important if the page needing pagination has a parameter in its URL.



--------------
## Config

There are a couple configuration items that control the output behavior.  To set either option, use the setConfig() method and reference the key name with a new value, e.g.

    Pagination\Pager::links(100)->setConfig('link_cnt', 3)

### link_cnt ###

How many page links are shown?  For example 3 might look something like this:

    << First < Prev 1 2 3 Next > Last >>

Whereas 12 might generate a set of links like this:

    << First < Prev 1 2 3 4 5 6 7 8 9 10 11 12 Next > Last >>

The default is 10.

### jump_size ###

Controls how many pages are flipped forwards/backwards when the prev/next links are clicked. With a value of 1, this operates like a book -- flip forward or back exactly one page at a time. Giant leaps are possible e.g. if you display 10 links at a time and you flip 10 pages forward, e.g. from displaying pages 11 - 20 to 21 - 30, etc.



--------------

## Customize Templates

So you don't like our HTML?  That's Ok, you can set custom templates for each part of the final result.

For fine tuning, you can use the **setTpl()** to change templates one at a time.

    <?php
        print Pagination\Pager::links(100)
            ->setTpl('outer','<div id="my_pagination" class="custom_footer_block">[+content+]</div>');
    ?>

See the "Templates Used" section for a list of all available templates.

If you want to go completely custom, you can use the **setTpls()** method to set *all* templates:

    <?php
        print Pagination\Pager::links(100)
            ->setTpls(
                array(
                    'first' => '',
                    'last' => '',
                    'prev' => '',
                    'next' => '',
                    'current' => '',
                    'page' => '',
                    'outer' => '',
                )
            );
    ?>

Take a look at the "Templates Used" section to familiarize yourself where each component gets used in the final output.

## Templates Used

If the current page is 3, the templates at work are the following:
    	
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

* **first** : contains the link back to the very first page.
* **prev** : contains the link back to a previous page (usually the page right before, but this is controlled by the "jump_size" config).
* **page** : formats a link to a specific page (a page that is not currently active).
* **current** : formats the number representing the currently active page.
* **next** : contains the link to an upcoming page (usually the page right after the current one, but this is controlled by the "jump_size" config).
* **last** : contains the link to the final page of results.
* **outer** : wraps the final output. This template is a good place for summary info.
