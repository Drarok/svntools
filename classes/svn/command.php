<?php
/**
 * Subversion Command.
 *
 * @category Subversion
 * @package  Subversion
 */
class Svn_Command
{
	/**
	 * Command to run.
	 *
	 * @var string
	 */
	protected $_command = '';

	/**
	 * Arguments to pass to the command when run.
	 *
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Factory method.
	 *
	 * Returns a specific subclass if available, or an instance of Svn_Command.
	 *
	 * @param string $command Name of the command to run.
	 * @param array  $params  Parameters to pass to the command when run.
	 *
	 * @return Svn_Command
	 */
	static public function factory($command, array $params = array())
	{
		$className = 'Svn_Command_' . ucfirst($command);
		if (! class_exists($className)) {
			// Specific class doesn't exist, so use generic one.
			$className = 'Svn_Command';
		}

		$instance = new $className($command, $params);
		if (! ($instance instanceof Svn_Command)) {
			throw new Exception($className . ' has invalid ancestry.');
		}
		return $instance;
	}

	/**
	 * Object constructor.
	 *
	 * @param array $params Parameters to pass to the command when run.
	 */
	protected function __construct($command, array $params = array())
	{
		$this->_command = $command;
		$this->_params = $params;
	}

	/**
	 * This is the method that subclasses should override.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->_run();
	}

	/**
	 * Run the command, optionally returning its output.
	 *
	 * @param boolean $return Pass true to receive the output, false to pass through.
	 *
	 * @return mixed
	 */
	protected function _run($return = false)
	{
		// Make each parameter 'safe'.
		$params = array();
		foreach ($this->_params as $param) {
			$params[] = escapeshellarg($param);
		}

		// Build up the shell command.
		$shellCommand = escapeshellcmd('svn');
		if ($this->_command) {
			$shellCommand .= ' ' . $this->_command;
		};
		if ($params) {
			$shellCommand .= ' ' . implode(' ', $params);
		}

		if (! $return) {
			passthru($shellCommand, $exitCode);
			if ($exitCode) {
				exit($exitCode);
			}
			return;
		}

		// Run the command and return its output.
		echo $shellCommand, PHP_EOL;
		return shell_exec($shellCommand);
	}
}