<?php
// mongo configs
$config = array(
    'servers'  => array(
        '172.16.245.126:27017',
    ),
    'database' => 'test',
);
if (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
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
