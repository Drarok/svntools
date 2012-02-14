<?php
/**
 * Simple caching tests.
 */

require_once 'bootstrap.php';

// This data should never get saved.
$cache = new Cache('nosave', -1);
$cache->set('foo', 'bar');
$cache->save();
if (Cache::exists('nosave')) {
	echo 'ERROR: Cache \'nosave\' exists on disk.', PHP_EOL;
} else {
	echo 'SUCCESS: Expired data was not saved.', PHP_EOL;
}
Cache::delete('nosave');

// This data should get saved, but expire after 5s.
$cache = new Cache('5s', 5);
$cache->set('foo', 'bar');
$cache->save();
if (! Cache::exists('5s')) {
	echo 'ERROR: Cache \'5s\' doesn\'t exist on disk.', PHP_EOL;
} else {
	echo 'SUCCESS: Valid data was saved.', PHP_EOL;
}
sleep(6);
$cache->save();
if (Cache::exists('5s')) {
	echo 'ERROR: Cache \'5s\' still exists after its expiry time.', PHP_EOL;
} else {
	echo 'SUCCESS: Expired data was deleted.', PHP_EOL;
}

echo 'Finished', PHP_EOL;