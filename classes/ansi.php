<?php
/**
 * ANSI Code Helper.
 */
abstract class ANSI
{
	/**
	 * Colors to pass to the color() method.
	 *
	 * @const string
	 */
	const BOLD = '1';
	const BLACK = '30';
	const RED = '31';
	const GREEN = '32';
	const YELLOW = '33';
	const BLUE = '34';
	const MAGENTA = '35';
	const CYAN = '36';
	const WHITE = '37';

	/**
	 * Internal use only.
	 *
	 * @const string
	 */
	const CSI = "\x1b[";
	const RESET = '0';
	const END = 'm';


	/**
	 * Output an ANSI color code, but not on Windows.
	 *
	 * @param string $color Color code.
	 * @param string ...    Additional color codes.
	 *
	 * @return string
	 */
	static public function color($color)
	{
		if (DIRECTORY_SEPARATOR == '\\') {
			return '';
		}

		$colors = func_get_args();

		return static::CSI . implode(';', $colors) . static::END;
	}

	/**
	 * Reset the color.
	 *
	 * @return string
	 */
	static public function reset()
	{
		return static::color(static::RESET);
	}
}