NAME
	svneligible - Tool to assist with Subversion merges.

AVAILABLE COMMANDS
	svneligible branches
	svneligible releases
	svneligible tags
	svneligible upstream [set|remove] [<upstream>] [<alias|path>]
	svneligible branch [<existing_path>] <new_path> [--parents] [--no-switch] [--commit=<message>]
	svneligible switch [<path>|--stable]
	svneligible diff [<path>|--stable] [-r|--reverse]
	svneligible show [<path>|--stable] [--dry-run] [--author=<name>] [--initial=<rev>] [--final=<rev>] [--range=<start>:<stop>]
	svneligible merge [<path>|--stable] [--dry-run] [--author=<name>] [--initial=<rev>] [--final=<rev>] [--range=<start>:<stop>]
	svneligible reintegrate [<path>|--stable] [--no-commit|--commit[=<message>] [--no-remove]
	svneligible help [<command>]

	Note: all commands also accept the [-v|--verbose] option, which causes all Subversion commands
	to be output to the console before they are executed.

<?php if (! $verbose): ?>
FURTHER HELP
	To get detailed help for a single command, try 'svneligible help <command>',
	or if you'd prefer to see all the help in one go, use 'svneligible help --verbose'.
<?php endif; ?>