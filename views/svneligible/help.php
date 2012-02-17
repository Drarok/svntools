NAME
	svneligible - Tool to assist with discovering merge-eligible revisions.

SYNOPSIS
	svneligible branches
	svneligible releases
	svneligible tags
	svneligible show (<path>|--stable) [--show-log]
	svneligible help

OPTIONS
	branches
		Display a two-level deep list from ^/branches.
	
	releases
		List the contents of ^/releases.
	
	tags
		List the contents of ^/tags.
	
	show (<path>|--stable) [--show-log]
		Show revisions that are eligible to merge from <path>. You must pass either a path, or --stable.
		
		The --stable option will look up the "latest" release (the last item in the releases list) for you.
		The --show-log option will fetch the commit messages and display those, too.
	
	help
		This help text.
