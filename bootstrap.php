<?php

// Define some project-wide constants.
define('DS', DIRECTORY_SEPARATOR);
define('SVNSTASH_ROOT', __DIR__ . DS);

// Initialise our autoloader.
require_once SVNSTASH_ROOT . 'classes' . DS . 'autoloader.php';

// Set up the exception handler.
set_exception_handler('Handler::exceptionHandler');