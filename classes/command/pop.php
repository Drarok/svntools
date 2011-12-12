<?php

class Command_Pop extends Command
{
	public function run()
	{
		Command::factory('apply')->run();
		Command::factory('drop')->run();
	}
}