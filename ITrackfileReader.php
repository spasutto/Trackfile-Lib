<?php

/**
 * Interface for trackfile readers
 */
interface ITrackfileReader
{
	public function __construct($file_path);
	public function getRecords();
}
?>
