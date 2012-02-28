<?php

namespace CSV;

class Validater {

	protected $_file_name = '';

	public function __construct($file_name)
	{
		$this->_file_name = $file_name;
	}

	// Check csv file for validity.
	// Returns true or false.
	public function check_file()
	{
		$file_name = $this->_file_name;
		$has_errors = false;

		// Check for file errors.
		try {
			static::validate_file_exists($file_name);
		}
		catch (\Exception $e)
		{
			// \Cli::error($e->getMessage());
			\Log::error($e->getMessage());
			$has_errors = true;
		}
		try {
			static::validate_file_line_count($file_name);
		}
		catch (\Exception $e)
		{
			// \Cli::error($e->getMessage());
			\Log::error($e->getMessage());
			$has_errors = true;
		}
		try {
			static::validate_file_unicode_characters($file_name);
		}
		catch (\Exception $e)
		{
			// \Cli::error($e->getMessage());
			\Log::error($e->getMessage());
			$has_errors = true;
		}

		// Check for row errors.
		$file_content = \CSV\File::read($file_name);
		$is_first = true;
		$field_names = array();
		foreach($file_content as $row)
		{
			if ($is_first)
			{
				$field_names = $row;
				$is_first = false;
				continue;
			}

			try {
				static::validate_row_column_count($row, $field_names);
			}
			catch (\Exception $e)
			{
				// \Cli::error($e->getMessage());
				\Log::error($e->getMessage());
				$has_errors = true;
			}
			try {
				static::validate_row_escaped($row, $field_names);
			}
			catch (\Exception $e)
			{
				// \Cli::error($e->getMessage());
				\Log::error($e->getMessage());
				$has_errors = true;
			}
		}

		if ($has_errors)
		{
			throw new \Exception("Validating CSV for $file_name failed.");
		}
	}

	/**
	 * _unihexord
	 * Unicode version of ord() to return the hex equivalent value
     * Adapted and improved from php.net docs
	*/
	static protected function _unihexord($u)
	{
		// Make sure the string is properly UTF-8
		if (!mb_detect_encoding($u,"UTF-8",true))
		{
			$u = utf8_encode($u);
		}
		// Convert from UTF-8
		$k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
		// Get the ord() for each bit
		$k1 = ord(substr($k, 0, 1));
		$k2 = ord(substr($k, 1, 1));
		// Return the upper case paddex hex to 4 characters of the unicode value
		return str_pad(strtoupper(dechex($k2 * 256 + $k1)),4,0,STR_PAD_LEFT);
	}

	static public function validate_file_exists($file)
	{
		if ( ! is_file($file))
		{
			throw new \Exception("$file does not exist.");
		}
	}

	/**
	 * Validates file line count
	 * @param  string $file [description]
	 */
	static public function validate_file_line_count($file)
	{
		$correct_count = count(file($file));
		$file = escapeshellarg($file);
		$error_prone_count = trim(`grep -c . {$file}`);

		if ($correct_count != $error_prone_count)
		{
			$message = "Line count mismatch, file {$file} has {$correct_count} "
					 . "but is incorrectly reported by bash and family as "
					 . "having {$error_prone_count}.";
			throw new \Exception($message);
		}
	}

	/**
	 * Validate that the file does not have unicode characters
	 * @param string $file
	 */
	static public function validate_file_unicode_characters($file)
	{
		// Define Filters and other stuff
		$show_line_errors = false;
		$url = "http://www.fileformat.info/info/unicode/char/{hexcode}/ http://www.decodeunicode.org/u+{hexcode}";
		$regex_noprint = '/[^[:print:]]/';
		$regex_cleaner = "/[\n\r\t]+/";
		$i = 0;
		$na = array();
		$errors = array();

		// Walk the file CSV Style
		if ($lines = file($file))
		{
			foreach ($lines as $line)
			{
				$i++;
				$n = 0;
				$clean_line = preg_replace($regex_cleaner,"",$line);
				if (preg_match_all($regex_noprint,$clean_line,$matches))
				{
					foreach ($matches[0] as $match)
					{
						$n++;
						$match = static::_unihexord($match);
						if (!isset($na[$match])) {
							$na[$match] = 0;
						}
						$na[$match]++;
					}
				}
			}
		}
		else
		{
			throw new \Exception("The chosen file does not exist.");
		}

		if (count($na) > 0)
		{
			$message = "The chosen file contained the following non-printing character(s):\n";

			foreach ($na as $unihex=>$count)
			{
				$turl = str_replace("{hexcode}",$unihex,$url);
				$message .= "- {$count} count(s) of U+{$unihex}, {$turl}\n";
			}

			throw new \Exception($message);
		}
	}

	/**
	 * Validate CSV row column count.
	 * @param  array $csv_row
	 * @param  array $csv_field_names
	 */
	static public function validate_row_column_count($csv_row, $csv_field_names)
	{
		if (count($csv_row) !== count($csv_field_names))
        {
			$row_imploded = implode(', ', $csv_row);
			$message = "Column mismatch for row:\t$row_imploded.";
			throw new \Exception($message);
        }
	}

	/**
	 * Validates csv row is properly escaped
	 * @param  array $csv_row
	 */
	static public function validate_row_escaped($csv_row)
	{
		$errors = array();
		foreach($csv_row as $key => $field)
		{
			$regex = "/([^\\\]\")$/";
			if (preg_match($regex,$field))
			{
				$message = "Escaping error for "
						 . implode(',', $csv_row)
						 . "\tField: $key";
				$errors[] = $message;
			}
		}

		if (count($errors))
		{
			throw new \Exception(implode("\n", $errors));
		}
	}
}