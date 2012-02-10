<?php

namespace CSV;

class Validater {

	protected $_csv_errors = array();
	protected $_escaping_errors = array();
	protected $_file_name = '';
	protected $_field_correct_count = 0;
	protected $_csv_field_names = array();
	protected $_row_count = 0;

	public function __destruct()
	{
		$this->_log_csv_errors($this->_csv_errors);
		$this->_log_escaping_errors($this->_escaping_errors);
	}

	// Check csv file for validity.
	// Returns true or false.
	public function check_file($file_name)
	{
		echo "Validating {$file_name}...\n";

		$this->_file_name = $file_name;

		Csv::read($file_name, 'read_callback', $this);

		$this->_check_line_count();
		$this->_check_unicode_characters();

		$row_count = $this->_row_count;
		echo "{$file_name} is finished being checked with {$row_count} rows read...\n";

		return count($this->_csv_errors) === 0 && count($this->_escaping_errors) === 0
				? true : false;
	}

	public function read_callback($csv_row, $row_count)
	{

		if ($row_count === 1)
		{
        	$this->_field_correct_count = count($csv_row);
        	$this->_csv_field_names = $csv_row;
        }

        $this->_row_count = $row_count;

        // check that row values matches correct column count
        $this->_check_csv_column_count($csv_row);
        $this->_check_escaping($csv_row);
	}

	protected function _check_escaping($csv_row)
	{
		foreach($csv_row as $key => $field)
		{
			$regex = "/([^\\\]\")$/";

			if (preg_match($regex,$field))
			{
				$message = "Escaping error for "
						 . implode(',', $csv_row)
						 . "\tField: $key";
				$this->_push_escaping_error($message);
			}
		}
	}

	protected function _check_line_count()
	{
		$file = $this->_file_name;
		$correct_count = count(file($file));
		$error_prone_count = trim(preg_replace("/ .*$/","",`wc -l {$file}`));

		if ($correct_count != $error_prone_count)
		{
			$message = "Line count mismatch, file {$file} has {$correct_count} but is incorrectly reported by bash and family as having {$error_prone_count}.\n";
			Logger::error($message);
			echo $message;
		}
	}

	protected function _check_unicode_characters()
	{
		Csv_unicode_check::check($this->_file_name);
	}

	protected function _check_csv_column_count($csv_row)
	{
		if (count($csv_row) !== count($this->_csv_field_names))
        {
			$this->_push_csv_error(implode(',', $csv_row));
        }
	}

	protected function _push_csv_error($csv_error = '')
	{
		$this->_csv_errors[] = $csv_error;
	}

	protected function _push_escaping_error($error = '')
	{
		$this->_escaping_errors[] = $error;
	}

	protected function _log_csv_errors($errors = array())
	{
		if (count($errors) > 0)
		{
			$file_name = $this->_file_name;
			$log_filename = Logger::get_filename();
			echo "\nCSV errors in {$file_name}, check log at {$log_filename}.\n";
			Logger::error("{$file_name} contains csv errors...");
			foreach($errors as $csv_error)
			{
				Logger::error("\t{$csv_error}\n");
			}
		}
	}

	protected function _log_escaping_errors($errors = array())
	{
		if (count($errors) > 0)
		{
			$file_name = $this->_file_name;
			$log_filename = Logger::get_filename();
			echo "\nEscaping errors in {$file_name}, check log at {$log_filename}.\n";
			Logger::error("{$file_name} contains escaping errors...");
			foreach($errors as $error)
			{
				Logger::error("\t{$error}\n");
			}
		}
	}
}