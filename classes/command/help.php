<?php

class Command_Help extends Command
{
	public function run()
	{
		View::factory('help')->render();
	}
}