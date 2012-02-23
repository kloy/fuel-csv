<?php

namespace CSV;

class File {

	static public function read($file, $as_associative = false)
	{
		// static::validate_file_exists($file);

		$data = array();
		$handle = fopen($file, "r");
		$counter = 0;
		$field_headings = array();
		while (($csv_row = fgetcsv($handle, 0, ",", '"', '\\')) !== FALSE)
		{
			if ($counter === 0 and $as_associative === true)
			{
				$field_headings = $csv_row;
				++$counter;
				continue;
			}
			if ($as_associative === true)
			{
				$data[] = array_combine($field_headings, $csv_row);
			}
			else
			{
				$data[] = $csv_row;
			}
			// unset($csv_row);
			// \Cli::write("CSV READ FILE LINE");
			// $mem = memory_get_usage(true);
			// $megabytes = round($mem/1048576,2);
			// $message = "Current memory usage is: $megabytes megabytes";
			// \Log::info($message);
			// \Cli::write($message);
	    }
		fclose($handle);

		// \Cli::write("CSV READ FILE\t$file");
		// $mem = memory_get_usage(true);
		// $megabytes = round($mem/1048576,2);
		// $message = "Current memory usage is: $megabytes megabytes";
		// \Log::info($message);
		// \Cli::write($message);

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

	    \Cli::write("Get field names");
		$mem = memory_get_usage(true);
		$megabytes = round($mem/1048576,2);
		$message = "Current memory usage is: $megabytes megabytes";
		\Log::info($message);
		\Cli::write($message);
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

		\Cli::write("Validate file exists\t$file");
		$mem = memory_get_usage(true);
		$megabytes = round($mem/1048576,2);
		$message = "Current memory usage is: $megabytes megabytes";
		\Log::info($message);
		\Cli::write($message);
	}

	static public function validate($file)
	{
		$validator = new Validater($file);
		$validator->check_file();
	}
}