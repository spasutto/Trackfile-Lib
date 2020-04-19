<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads FLG file (flightlog)
 * Filghtlog is a pure CSV like file format : each line is described as
 * 158904;2018-11-04T10:33:20.05Z;45.0008617;5.7358733;1525.566;0.031;-0.625;0.05
 *  col 0 : milliseconds from start
 *  col 1 : date
 *  col 2 : latitude
 *  col 3 : longitude
 *  col 4 : altitude AMSL
 *  col 5 : horizontal speed
 *  col 6 : vertical speed
 *  col 7 : glide ratio
 */
class FLGReader implements ITrackfileReader
{
	private $fhandle;

	/**
	 * Class constructor creates the FLGReader object from a file path.
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
				$cols = explode(';', $buffer);
        $pt_records[] = (object)[
        'date' => DateTime::createFromFormat('Y-m-d\TH:i:s+', $cols[1]),
        'latitude' => floatval($cols[2]),
        'longitude' => floatval($cols[3]),
        'altitude' => floatval($cols[4])
				];
      }
    }
    return $pt_records;
  }
}
?>
