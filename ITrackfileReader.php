<?php

function exception_error_handler($severity, $message, $file, $line) {
	if (!(error_reporting() & $severity)) {
		// This error code is not included in error_reporting
		return;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
}

/**
 * Interface for trackfile readers
 */
interface ITrackfileReader
{
	public function __construct($file_path);
	public function getRecords();
	public function getFirstRecord();
}
?>
