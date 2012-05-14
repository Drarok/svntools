NAME
	svneligible - Tool to assist with discovering merge-eligible revisions.

SYNOPSIS
	svneligible branches [-v|--verbose]
	svneligible releases [-v|--verbose]
	svneligible tags [-v|--verbose]
	svneligible upstream [--remove] [--path=<path>] [<upstream>]
	svneligible branch <existing_path> <new_path> [--no-switch] [--commit=<message>]
	svneligible show [<path>|--stable] [-v|--verbose]
	svneligible diff [<path>] [-v|--verbose]
	svneligible merge [<path>|--stable] [--dry-run] [--author=<name>] [--initial=<rev>] [--final=<rev>] [--range=<start>:<stop>] [-v|--verbose]
	svneligible reintegrate [<path>] [--commit[=<message>] [--remove]]
	svneligible help

OPTIONS
	branches
		Display a two-level deep list from ^/branches.

	releases
		List the contents of ^/releases.

	tags
		List the contents of ^/tags.

	upstream
		Shows or changes the "upstream" configuration. "Upstream" branches are those used automatically by the
		'show' and 'merge' commands when no <path> option is specified.

		Running 'upstream' with no parameters will output all currently-configured upstreams.

		Passing --path=<path> will perform operations on <path>, else the currently checked-out path is used.

		The --remove option will remove any upstream setting for <path> (or current branch).

		Specifiying <upstream> will record that path as the upstream branch. If you call 'show' or 'merge' with no <path>
		option, the <upstream> will be checked for the currently-checked out path.

	branch
		Create a new branch in the repository, and configure the upstream automatically.

		This command will automatically switch your working copy unless you pass --no-switch.

		You may also specify a commit message on the command line instead of using your editor, using '--commit=<message>'.

	show
		Show revisions (and log messages) that are eligible to merge from <path>. If no <path> or --stable option is passed,
		the upstreams will be checked. If there is still no path, an error occurs.

		The --stable option will look up the "latest" release (the last item in the releases list) for you.

	diff
		Perform a diff between the currently checked-out branch and the specified path. If no path is specified,
		upstreams are checked.

	merge
		Tell Subversion to merge the eligible revs from <path>. If no <path> or --stable option is passed, the upstreams
		will be checked. If there is still no path, an error occurs.

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

	reintegrate
		Perform a reintegration merge by switching the working copy, merging, committing (you may supply a message
		on the command line, or allow your editor to show), and finally removing your reintegrated branch. Some of these
		steps are optional.

		You must either supply a path on the command line as <path>, or have previously set up an "upstream" branch.

		Passing '--commit' will show your editor and allow you to enter a commit message, or you may specify it like so:
		'--commit=Reintegrating my development branch'.

		If you pass '--remove' in addition to '--commit[=<message>]', then the reintegrated branch will automatically
		be removed.

	help
		This help text.
