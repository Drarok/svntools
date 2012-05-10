#!/usr/bin/env php
<?php
/**
 * This command ensures that commits are run from the working copy root.
 */

require_once __DIR__ . '/bootstrap.php';

Command::factory('svnbase')->run();
