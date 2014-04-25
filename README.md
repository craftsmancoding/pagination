# Pagination

A simple pagination library for the common folks: this generates links for flipping through pages of records. A hot new feature: DOCUMENTATION!  Although other libraries may have vastly superior and amazing features, this library features actual documentation with real examples and explanations.  If you are a super genius who grok's source-code with one glance, then you have many other packages to choose from, but this is for the rest of you.

We folks here believe in the simple practice of demonstrating how to use what we've written.  

## Overview

You've seen pagination links all over the place: you deal with databases and record-sets for a short while
and you see the need to flip through pages of the records using links that might look something like:

    << First < Prev.  1 2 3 4 5  Next >  Last >>

That's what we're creating here.  It may surprise you, but to create pagination links, the only things you
truly need are:

* the count of the total records available 
* the base URL that controls the offset.

Other stuff is optional for fine-tuning, but that's it.  Let's take a closer look at how to make this work.

-----------------------------

## Paginating Records

Cut to the chase.  How to paginate records?

## Customize Templates

So you don't like our HTML?  That's Ok.

--------------
## Config

### link_cnt ###

#### jump_size ####

Controls how many pages are flipped forwards/backwards when the prev/next links are clicked. With a value of 1, this operates like a book -- flip forward or back exactly one page at a time. Giant leaps are possible e.g. if you display 10 links at a time and you flip 10 pages forward, e.g. from displaying pages 11 - 20 to 21 - 30, etc.


--------------

## Templates Used

If the current page is 3, the templates at work are the following:
    	
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

* **firstTpl** : contains the link back to the very first page.
* **prevTpl** : contains the link back to a previous page (usually the page right before, but this is controlled by the "jump_size" config).
* **pageTpl** : formats a link to a specific page (a page that is not currently active).
* **currentPageTpl** : formats the number representing the currently active page.
* **nextTpl** : contains the link to an upcoming page (usually the page right after the current one, but this is controlled by the "jump_size" config).
* **lastTpl** : contains the link to the final page of results.
* **outerTpl** : wraps the final output. This template is a good place for summary info.
