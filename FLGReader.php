<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads FLG file (flightlog)
 * Filghtlog is a pure CSV like file format : each line is described as
 * 158904;2018-11-04T10:33:20.05Z;45.0008617;5.7358733;1525.566;0.031;-0.625;0.05
 *	col 0 : milliseconds from start
 *	col 1 : date
 *	col 2 : latitude
 *	col 3 : longitude
 *	col 4 : altitude AMSL
 *	col 5 : horizontal speed
 *	col 6 : vertical speed
 *	col 7 : glide ratio
 */
class FLGReader implements ITrackfileReader
{
	private $fhandle;
	private $buffer;
	public $duration;

	/**
	 * Class constructor creates the FLGReader object from a file path.
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
		$cols = explode(';', $buffer);
		return (object)[
		'date' => DateTime::createFromFormat('Y-m-d\TH:i:s+', $cols[1], new DateTimeZone('UTC')),
		'latitude' => floatval($cols[2]),
		'longitude' => floatval($cols[3]),
		'altitude' => floatval($cols[4])
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
