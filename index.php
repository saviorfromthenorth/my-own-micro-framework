<?php
/**
 * Project Zap! Framework
 * @author Andie Valderama <andie.valderama@gmail.com>
 * @version 0.0.7 Alpha
 */

//Allow IE to accept SESSIONS, comment if it conflicts with your header
if (!headers_sent() && strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
    header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
}

//Build global variables for both server and client side paths
$prefix = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

if (file_exists('.htaccess')) {
    $base = $prefix . $_SERVER['HTTP_HOST'] . '/';
    $url  = $base;
} else {
    $uri  = explode('index.php', $_SERVER['REQUEST_URI']);
    $uri  = strpos($uri[0], '?') ? explode('?', $uri[0]) : $uri;
    $url  = $uri . 'index.php/';
    $base = $prefix . $_SERVER['HTTP_HOST'] . $uri[0];
}

define('BASE', $base);
define('URL', $url);
define('ROOT', dirname(__FILE__) . '/');

require_once ROOT . 'core/zapConfiguration.class.php';

//Start the project
$app = new zapConfiguration();
