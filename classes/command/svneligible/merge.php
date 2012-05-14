<?php

/**
 * Merge the revisions that are eligible.
 * 
 * Merges revisions from a given branch into the working copy.
 */
class Command_Svneligible_Merge extends Command_Svneligible_Filter
{
	/**
	 * Instance of the Svn class to use.
	 * 
	 * @var object
	 */
	protected $_svn;

	/**
	 * Command runner - does the actual work.
	 * 
	 * @return void
	 */
	public function run()
	{
		// Note that we *always* operate on the root of the working copy.
		$this->_svn = new Svn(Svn::getRoot('.'));

		$options = $this->_parseOptions();

		echo $options->path, PHP_EOL;

		$eligible = $this->_svn->eligible($options->path);

		if (! (bool) $eligible) {
			echo '    No eligible revisions.', PHP_EOL;
			return;
		}

		// There are revs, filter on author if required.
		if ((bool) $author = $this->_args->getNamedArgument('author')) {
			$eligible = $this->_filterAuthor($eligible, $author);

			if (! count($eligible)) {
				echo '    There are no eligible revs by author \'', $author, '\'.', PHP_EOL;
				exit(1);
			}
		}

		// Apply range filter.
		$eligible = $this->_filterRange($eligible, $options->initial, $options->final);
		if (! count($eligible)) {
			echo '    There are no eligible revisions within the range specified.', PHP_EOL;
			exit(1);
		}

		if ($this->_args->getNamedArgument('dry-run')) {
			$noun = count($eligible) == 1
				? 'revision'
				: 'revisions';

			echo sprintf('Would merge %s %s from %s',
				$noun, implode(', ', $eligible), $options->path), PHP_EOL;

			exit(0);
		}

		$this->_svn->merge($options->path, $eligible);
	}

	/**
	 * Parse the command-line arguments, returning an object.
	 * 
	 * The object returned is guaranteed to have the following properties:
	 *     - path
	 *     - initial
	 *     - final
	 * 
	 * @return object
	 */
	protected function _parseOptions()
	{
		// Initialise the defaults.
		$result = (object) array(
			'path'    => false,
			'initial' => false,
			'final'   => false,
		);

		if ($this->_args->getNamedArgument('stable')) {
			// The --stable flag means to check against the 'newest' release branch.
			$releases = Command_Svneligible::factory('releases')->run(false);
			$result->path = array_pop($releases);
		} else {
			// Don't forget that argument 0 is the command.
			$result->path = $this->_args->getUnnamedArgument(1);
		}

		if (! $result->path) {
			// There's still no path. Look for an upstream.
			$upstream = new Upstream('.');
			$result->path = $upstream->getUpstream($this->_svn->relativePath());
		}

		if (! $result->path) {
			echo 'You must specify a path to use the \'merge\' command.', PHP_EOL;
			exit(1);
		}

		// Now parse out the range / limiting options.
		if ((bool) $initial = (int) $this->_args->getNamedArgument('initial')) {
			$result->initial = $initial;
		}

		if ((bool) $final = (int) $this->_args->getNamedArgument('final')) {
			$result->final = $final;
		}

		if ((bool) $range = $this->_args->getNamedArgument('range')) {
			if ($result->initial || $result->final) {
				echo 'You cannot specify both a range and an initial or final revision.', PHP_EOL;
				exit(1);
			}

			$range = explode(':', $range);
			if (count($range) != 2) {
				echo 'You must specify a range in the correct format: --range=<initial>:<final>.', PHP_EOL;
				exit(1);
			}

			$result->initial = (int) $range[0];
			$result->final = (int) $range[1];
		}

		return $result;
	}
}