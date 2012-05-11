#!/usr/bin/env php
<?php
/**
 * This command ensures that commits are run from the working copy root.
 */

require_once __DIR__ . '/bootstrap.php';

$args = new Arguments(array_slice($_SERVER['argv'], 1));

Command::factory('svnbase', $args)->run();
