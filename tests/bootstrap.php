<?php
require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

/**
 *
 *
 */
function normalize_html($str) {
    $str = trim($str);
    return preg_replace('/\s+/', ' ', $str);
}
/*EOF*/