<?php
// define the parameter which points out whether it's in development env.
$isDev = false;
if (isset($_SERVER['REMOTE_ADDR'])) {
    if (false !== strpos($_SERVER['REMOTE_ADDR'], '127.0.')) {
        $isDev = true;
    }
    //echo 'remote: ' . $_SERVER['REMOTE_ADDR'];
}
define('IS_DEV', $isDev);
unset($isDev);

/**
 * open error reporting for this project
 * can suggest to integrate to original framework
 */
if (IS_DEV or $_SERVER['REMOTE_ADDR'] == '119.145.139.232') {
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    ini_set ("display_errors", 0);
}

// mongo configs
$config = array(
    'servers'  => array(
        '172.16.245.126:27017',
    ),
    'database' => 'test',
);

if (isset($_SERVER['REMOTE_ADDR']) and false !== strpos($_SERVER['REMOTE_ADDR'], '127.0.')) {
    // comment object
    $config = array(
        'servers'  => array(
                'localhost:27018',
                'localhost:27019',
                'localhost:27020',
        ),
        'database' => 'test',
        'username' => 'kim',
        'password' => 'kim',
    );
}

// define where to find the ip data
define('PATH_QQWRY', ROOT_DIR . 'data/qqwry.dat');
