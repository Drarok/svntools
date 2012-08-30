NAME
	svneligible - Tool to assist with Subversion merges.

AVAILABLE COMMANDS
<?php

$pathPrefix = SVNTOOLS_ROOT . implode(DS, array('views', 'svneligible', 'help')) . DS;
foreach ($commands as $command) {
	if ($command == 'help') {
		// Output special text for the help command.
		echo "\t", 'svneligible help [<command>]', PHP_EOL;
		continue;
	}

	if ($command == 'upstream') {
		// Output special text for the upstream command.
		echo "\t", 'svneligible upstream [set|remove] [<upstream>] [<alias|path>]', PHP_EOL;
		continue;
	}

	$file = fopen($pathPrefix . $command . '.txt', 'r');
	echo "\t", trim(fgets($file)), PHP_EOL;
	fclose($file);
}

?>

	Note: all commands also accept the [-v|--verbose] option, which causes all Subversion commands
	to be output to the console before they are executed.

<?php if (! $verbose): ?>
FURTHER HELP
	To get detailed help for a single command, try 'svneligible help <command>', or
	'svneligible <command> --help'.

	If you'd prefer to see all the help in one go, use 'svneligible help --verbose'.
<?php endif; ?>