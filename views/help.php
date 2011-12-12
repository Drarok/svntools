NAME
	svnstash - Stash the changes in a working copy away for later.

SYNOPSIS
	svnstash save <name> [-u|--include-untracked]
	svnstash list
	svnstash show [<name>]
	svnstash apply [<name>]
	svnstash pop [<name>]
	svnstash drop [<name>]
	svnstash clear [-f|--force]
	svnstash help
	
OPTIONS
	save <name> [-u|--include-untracked]
		Save the current working copy modifications to a new stash named <name>, and revert the working copy back to a pristine state.
		
		If the --include-untracked option is used, then untracked files are also stashed, and removed from the working copy.
	
	list
		List all stashes in the current working directory.
	
	show [<name>]
		Show the diff from the stash. When no <name> is specified, this command shows the latest stash.
	
	apply [<name>]
		Apply the diff from the stash to the working copy. When no <name> is specified, applies the latest stash.
	
	pop [<name>]
		Apply the diff to the working copy, and remove it afterwards. When no <name> is specified, applies the latest stash.
	
	drop [<name>]
		Delete a stash without applying it to the working copy. When no <name> is specified, removes the latest stash.
	
	clear [-f|--force]
		Remove all stashes from the working directory.
		
		Passing the --force option will not ask for confirmation of this step.
	
	help
		This help text.
