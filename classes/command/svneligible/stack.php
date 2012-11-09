<?php
/**
 * Branch stack management.
 */
class Command_Svneligible_Stack extends Command_Svneligible
{
	/**
	 * Stack object used for operations.
	 *
	 * @var object
	 */
	protected $_stack;

	/**
	 * Main entrypoint to the command.
	 *
	 * @return void
	 */
	public function run()
	{
		$validSubCommands = array('', 'list', 'remove', 'clear');

		$subCommand = $this->_args->getUnnamedArgument(1);
		if (! in_array($subCommand, $validSubCommands)) {
			throw new Exception('Invalid subcommand: ' . $subCommand);
		}

		if (! $subCommand) {
			$subCommand = 'list';
		}

		$args = array();
		if ($subCommand == 'remove') {
			$id = $this->_args->getUnnamedArgument(2);
			if ($id === NULL || ! is_numeric($id)) {
				throw new Exception('You must pass an id to the remove subcommand.');
			}
			$args = array($id);
		}

		// Create the stack object.
		$this->_stack = new Stack(Svn::getRoot('.'), 'branches.stack');

		// Call the method, and away we go.
		$method = '_' . $subCommand;
		call_user_func_array(array($this, $method), $args);
	}

	/**
	 * List the contents of the branch stack.
	 *
	 * @return void
	 */
	protected function _list()
	{
		foreach ($this->_stack->getAll() as $id => $value) {
			echo $id, ': ', $value, PHP_EOL;
		}
	}

	/**
	 * Remove an item from the stack.
	 *
	 * @param int $id The id to remove.
	 *
	 * @return void
	 */
	protected function _remove($id)
	{
		$value = $this->_stack->remove($id);
		echo 'Removed ', $value, ' from the branch stack.', PHP_EOL;
	}

	/**
	 * Remove the whole stack.
	 *
	 * @return void
	 */
	protected function _clear()
	{
		// Show the list 1st.
		$this->_list();

		// Clear the stack.
		$this->_stack->clear();

		echo 'Branch stack cleared.', PHP_EOL;
	}
}