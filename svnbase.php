#!/usr/bin/env php
<?php
/**
 * This command ensures that commits are run from the working copy root.
 */

require_once __DIR__ . '/bootstrap.php';

// Initialise the command-line helper.
CLI::init();

Command::factory('svnbase')->run();
