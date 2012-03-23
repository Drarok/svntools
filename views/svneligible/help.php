NAME
	svneligible - Tool to assist with discovering merge-eligible revisions.

SYNOPSIS
	svneligible branches [-v|--verbose]
	svneligible releases [-v|--verbose]
	svneligible tags [-v|--verbose]
	svneligible show (<path>|--stable) [--show-log] [-v|--verbose]
	svneligible merge (<path>|--stable) [--dry-run] [--author=<name>] [--initial=<rev>] [--final=<rev>] [--range=<start>:<stop>] [-v|--verbose]
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

	merge
		Tell Subversion to merge the eligible revs from <path>. You must pass either a path, or --stable.

		The --stable option will look up the "latest" release (the last item in the releases list).

		If you pass --dry-run, then no merge will actually take place, the revisions will just be output.
		This is useful if you want to try out some filters and ensure they match correctly.

		Passing --author=<name> will only merge revisions committed by the specified author.
		This is useful for merging only commits that you have made.

		If you pass --initial=<rev>, then no revisions prior to <rev> will be included in the merge.

		The --final=<rev> option will cause the merge to stop at <rev>.

		Lastly, there is a --range=<start>:<stop> option, which allows an inclusive range to be specified in a
		short-hand notation, similar to Subversion's normal merging.

		You cannot pass --range at the same time as --initial or --final, doing so will result in an error.

		Note: Subversion's standard command expect you to make up the first revision, whereas svneligible accepts
		non-braindead arguments. The options passed to --initial, --final, and --range are *inclusive*.

	help
		This help text.
