NAME
	svneligible - Tool to assist with Subversion merges.

AVAILABLE COMMANDS
	svneligible branches
	svneligible releases
	svneligible tags
	svneligible upstream [--remove] [--path=<path>] [<upstream>]
	svneligible show [<path>|--stable]
	svneligible diff [<path>]
	svneligible merge [<path>|--stable] [--dry-run] [--author=<name>] [--initial=<rev>] [--final=<rev>] [--range=<start>:<stop>]
	svneligible reintegrate [<path>] [--commit[=<message>] [--remove]]
	svneligible help [<command>]

	Note: all commands also accept the [-v|--verbose] option, which causes all Subversion commands
	to be output to the console before they are executed.

<?php if (! $verbose): ?>
FURTHER HELP
	To get detailed help for a single command, try 'svneligible help <command>',
	or if you'd prefer to see all the help in one go, use 'svneligible help --verbose'.
<?php endif; ?>