<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads WPT file
 */
class WPTReader implements ITrackfileReader
{
	private $fhandle;
	private $buffer;
	public $duration;

	/**
	 * Class constructor creates the WPTReader object from a file path.
	 *
	 * @param				string	$file_path usually this will be the request vars
	 */
	public function __construct($file_path)
	{
		if (is_file($file_path)) {
			$this->fhandle = @fopen($file_path, "r");
		} else {
			$this->buffer = $file_path;
		}
	}

	/**
	 * Returns the point list
	 */
	public function getRecords($first = false)
	{
		$pt_records = array();
		if ($this->fhandle) {
			while (($buffer = fgets($this->fhandle)) !== FALSE) {
				$tmprec = $this->getRecord($buffer);
				if ($tmprec) {
					$pt_records[] = $tmprec;
					if ($first)
						break;
				}
			}
		} else {
			foreach (explode("\n", $this->buffer) as $buffer) {
				if (strlen(trim($buffer))<=0) {
					continue;
				}
				$tmprec = $this->getRecord($buffer);
				if ($tmprec) {
					$pt_records[] = $tmprec;
					if ($first)
						break;
				}
			}
		}
		$this->duration = ($pt_records[count($pt_records)-1]->date->getTimestamp()-$pt_records[0]->date->getTimestamp());
		return $pt_records;
	}
	
	/**
	 * Returns the point
	 */
	protected function getRecord($buffer)
	{
		$buffer = strtolower(trim($buffer));
		if ($buffer[0] != "w")
			return;
		$cols = array_values(array_filter(explode(' ', $buffer), function($val) {return strlen($val);}));
		return (object)[
		'date' => DateTime::createFromFormat('j-M-y H:i:s', $cols[5]." ".$cols[6], new DateTimeZone('UTC')),
		'latitude' => floatval(preg_replace("/[^0-9\.]/", "", $cols[3])),
		'longitude' => floatval(preg_replace("/[^0-9\.]/", "", $cols[4])),
		'altitude' => floatval($cols[7])
		];
	}

	/**
	 * Returns the first point
	 */
	public function getFirstRecord()
	{
		$pt_records = $this->getRecords(true);
		if (is_array($pt_records) && count($pt_records)>0)
			return $pt_records[0];
		return null;
	}
}
?>
