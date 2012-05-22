NAME
	svnstash - Stash the changes in a working copy away for later.

SYNOPSIS
	svnstash save <name> [-u|--include-untracked]
	svnstash list
	svnstash show [<name|id>]
	svnstash apply [<name|id>]
	svnstash pop [<name|id>]
	svnstash drop [<name|id>]
	svnstash clear [-f|--force]
	svnstash trash [show]
	svnstash trash empty [-f|--force]
	svnstash trash restore <name|id> [...]
	svnstash help
	
OPTIONS
	save <name> [-u|--include-untracked] [--revert]
		Save the current working copy modifications to a new stash named <name>, and revert the working copy back to a pristine state.
		
		If the --include-untracked option is used, then untracked files are also stashed, and removed from the working copy.

		Passing in --revert will automatically issue a recursive revert once the stash is saved.
	
	list
		List all stashes in the current working directory, showing their name and id.
	
	show [<name|id>]
		Show the diff from the stash. When no <name> is specified, this command shows the latest stash.
	
	apply [<name|id>]
		Apply the diff from the stash to the working copy. When no <name> is specified, applies the latest stash.
	
	pop [<name|id>]
		Apply the diff to the working copy, and remove it afterwards. When no <name> is specified, applies the latest stash.
	
	drop [<name|id>]
		Delete a stash without applying it to the working copy. When no <name> is specified, removes the latest stash.
	
	clear [-f|--force]
		Remove all stashes from the working directory.
		
		Passing the --force option will not ask for confirmation of this step.
	
	trash [show]
		Shows what's in the trash.
	
	trash empty [-f|--force]
		Permanently delete all the stashes from the trash.
		
		Passing the --force option will not ask for confirmation of this step.
	
	trash restore <name|id> [...]
		Restore one or more items from the trash.
	
	help
		This help text.

NOTES
	Anywhere that svnstash accepts <name|id> style parameters, you can simply use the id shown in the corresponding command.
