<?php

namespace CSV;

class File {

	static public function read($file , $callback = null, $object = null)
	{
		if ( ! is_file($file))
		{
			throw new \FileAccessException("{$file} does not exist");
		}
		if (($handle = fopen($file, "r")) === FALSE)
		{
			throw new \FileAccessException("Unknown error when trying to read {$file}");
		}

		$row_count = 0;
		$data = array();
		while (($csv_row = fgetcsv($handle, 1000, ",", '"', '\\')) !== FALSE)
		{
			++$row_count;
			$data[] = $csv_row;
			if ($object !== null && $callback !== null)
			{
				$object->{$callback}($csv_row, $row_count);
			}
			else if ($callback !== null)
			{
				if (strpos('::', $callback) === FALSE)
				{
					$callback($csv_row, $row_count);
				}
				else
				{
					list($class, $function) = explode('::', $callback);
					call_user_func(array($class, $function), $csv_row, $row_count);
				}
			}
	    }
		fclose($handle);

		return $data;
	}

	static public function get_field_names($file)
	{
		if ( ! is_file($file))
		{
			throw new FileException("{$file} does not exist");
		}
		if (($handle = fopen($file, "r")) === FALSE)
		{
			throw new FileException("Unknown error when trying to read {$file}");
		}


		while (($csv_row = fgetcsv($handle, 1000, ",", '"', '\\')) !== FALSE)
		{
			fclose($handle);
			return $csv_row;
	    }

	}

	static public function validate($file)
	{
		$validator = new Csv_validator();

		return $validator->check_file($file);
	}
}