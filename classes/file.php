<?php

namespace CSV;

class File {

	static public function read($file)
	{
		static::validate_file_exists($file);

		$data = array();
		$handle = fopen($file, "r");
		while (($csv_row = fgetcsv($handle, 1000, ",", '"', '\\')) !== FALSE)
		{
			$data[] = $csv_row;
	    }
		fclose($handle);

		return $data;
	}

	static public function get_field_names($file)
	{
		static::validate_file_exists($file);
		$handle = fopen($file, "r");
		while (($csv_row = fgetcsv($handle, 1000, ",", '"', '\\')) !== FALSE)
		{
			fclose($handle);
			return $csv_row;
	    }

	}

	static public function validate_file_exists($file)
	{
		if ( ! is_file($file))
		{
			throw new \Exception("{$file} does not exist");
		}
		if (($handle = fopen($file, "r")) === FALSE)
		{
			throw new \Exception("Unknown error when trying to read {$file}");
		}
		else
		{
			fclose($handle);
		}
	}

	static public function validate($file)
	{
		$validator = new Validater($file);
		$validator->check_file();
	}
}