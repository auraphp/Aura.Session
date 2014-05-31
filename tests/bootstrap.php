<?php
// soothe the command line
ini_set('session.use_cookies', 0);
ini_set('session.cache_limiter', '');

// preload source files
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src.php';

// autoload test files
spl_autoload_register(function($class) {
    $file = dirname(__DIR__). DIRECTORY_SEPARATOR
          . 'tests' . DIRECTORY_SEPARATOR
          . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// have to buffer; otherwise, output from PHPUnit will cause "cannot send
// headers" errors.
ob_start();
