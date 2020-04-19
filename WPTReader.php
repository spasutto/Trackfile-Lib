<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads WPT file
 */
class WPTReader implements ITrackfileReader
{
	private $fhandle;

	/**
	 * Class constructor creates the WPTReader object from a file path.
   *
   * @param        string  $file_path usually this will be the request vars
   */
  public function __construct($file_path)
  {
    $this->fhandle = @fopen($file_path, "r");
  }

  /**
   * Returns the point list
   */
  public function getRecords()
  {
    $pt_records = array();
    if ($this->fhandle) {
      while (($buffer = fgets($this->fhandle)) !== FALSE) {
        $buffer = strtolower(trim($buffer));
        if ($buffer[0] != "w")
          continue;
        $cols = array_values(array_filter(explode(' ', $buffer), function($val) {return strlen($val);}));
        $pt_records[] = (object)[
        'date' => DateTime::createFromFormat('j-M-y H:i:s', $cols[5]." ".$cols[6]),
        'latitude' => preg_replace("/[^0-9\.]/", "", $cols[3]),
        'longitude' => preg_replace("/[^0-9\.]/", "", $cols[4]),
        'altitude' => $cols[7]
        ];
        //print_r($pt_records[count($pt_records)-1]);echo "<BR>";
      }
    }
    return $pt_records;
  }
}
?>
