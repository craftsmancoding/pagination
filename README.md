# Pagination

A simple pagination library for the common folks: this generates links for flipping through pages of records.
A hot new feature: DOCUMENTATION!  Although other libraries may have vastly superior and amazing features, 
this library features actual documentation with real examples and explanations.  If you are a super genius 
who grok's source-code at one glance, then you have many other packages to choose from.  
We folks here believe in the simple practice of demonstrating how to use what we've written.  

## Overview

You've seen pagination links all over the place: you deal with databases and record-sets for a short while
and you see the need to flip through pages of the records using links that might look something like:

    << First < Prev.  1 2 3 4 5  Next >  Last >>

That's what we're creating here.  It may surprise you, but to create pagination links, the only things you
truly need are 1) the count of the total records available and 2) the base URL that controls the offset.

-----------------------------

## Paginating Records

Cut to the chase.  How to paginate records?

## Customize Templates


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

