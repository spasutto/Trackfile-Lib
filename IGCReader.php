<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');
/**
 * IGCReader
 * forked from project php-igc by Mike Milano <coder1@gmail.com>
 *
 * This class is instanciated with the file path of the IGC file. It
 * will create an array of IGC record objects for convenient use of the data.
 */
class IGCReader implements ITrackfileReader
{
  /**
   * The date and time of the flight
   * @access public
   * @var DateTime
   */
  public $datetime;

  /**
   * The Pilot's name
   * @access public
   * @var string
   */
  public $pilot;

  /**
   * The Glider type
   * @access public
   * @var string
   */
  public $glider_type;

  /**
   * The Glider ID
   * @access public
   * @var string
   */
  public $glider_id;

  /**
   * The max altitude of the flight
   * @access public
   * @var string
   */
  public $max_altitude;

  /**
   * The minimum altitude of the flight
   * @access public
   * @var string
   */
  public $min_altitude;

  /**
   * The total distance of the flight
   * @access public
   * @var string
   */
  public $distance;

  /**
   * The total duration of the flight (in seconds)
   * @access public
   * @var integer
   */
  public $duration;

  /**
   * Class constructor creates the IGCReader object from a file path.
   *
   * @param        string  $file_path usually this will be the request vars
   */
  public function __construct($file_path)
  {
    spl_autoload_register('self::ClassAutoloader');
    $handle = @fopen($file_path, "r");
    if ($handle) {
      while (($buffer = fgets($handle)) !== FALSE) {
        $this->records[] = $this->getRecord($buffer);
      }
    }
    spl_autoload_unregister('self::ClassAutoloader');
  }

  /**
   * Returns an IGC record object
   *
   * @param        string          $string is the raw record line from an IGC file
   * @return       IGC_Record      Returns the specific IGC_Record object or false if the record isn't supported.
   */
  public function getRecord($string) {

    $classname = 'IGC_'.strtoupper(substr($string,0,1)).'_Record';
    return new $classname($string);
  }

  /**
   * Sets the details of the IGC files from the record objects within
   */
  public function setDetails()
  {
    $this->max_altitude = 0;
    $this->min_altitude = 80000;

    // set lowest and highest altitude
    if (is_array($this->records)) {
      $this->datetime = new DateTime("1970-01-01");
      $start_found = false;

      foreach ($this->records as $each) {
        if ($each->type == 'H') {
          if ($each->tlc == 'DTE') {
            if ($this->startsWith($each->value, 'DATE:')) {
              $this->datetime->setDate(intval('20'.substr($each->value, 9, 2)),
                                       intval(substr($each->value, 7, 2)),
                                       intval(substr($each->value, 5, 2)));
            }
            else {
              $this->datetime->setDate(intval('20'.substr($each->value, 4, 2)),
                                       intval(substr($each->value, 2, 2)),
                                       intval(substr($each->value, 0, 2)));
            }
          }
          elseif ($each->tlc == 'PLT') {
            $this->pilot = ucwords(strtolower($each->value));
          }
        }
        elseif ($each->type == 'B') {
          $record_time = clone $this->datetime;
          $record_time->setTime($each->time_array['h'],
                                $each->time_array['m'],
                                $each->time_array['s']);
          if (!$start_found) {
            $start_found = true;
            $this->datetime = $record_time;
          }
          $this->duration = $record_time->getTimestamp() - $this->datetime->getTimestamp();
          if ($each->pressure_altitude > $this->max_altitude) {
            $this->max_altitude = $each->pressure_altitude;
          }
          elseif ($each->pressure_altitude < $this->min_altitude) {
            $this->min_altitude = $each->pressure_altitude;
          }
        }
      }
    }

    // reset to 0 if a minimum altitude was never recorded
    if ($this->min_altitude == 80000) {
      $this->min_altitude = 0;
    }
  }

  /**
   * Returns the point list
   */
  public function getRecords()
  {
    $pt_records = array();
    $this->setDetails();
    //$date = str_replace('+00:00', 'Z', date('c', $this->datetime->getTimestamp()));// $this->datetime;
    $date = $this->datetime;// $this->toGMT($this->datetime);
    //$dateinit = $date;
    foreach ($this->records as $each) {
      if ($each->type == "B")
      {
        $date->setTime(intval($each->time_array['h']), intval($each->time_array['m']), intval($each->time_array['s']));
        //$date->add(new DateInterval("PT".intval($each->time_array['h'])."H".$each->time_array['m']."M".$each->time_array['s']."S"));
        $pt_records[] = (object)[
        'date' => clone $date,
        'latitude' => floatval($each->latitude['decimal_degrees']),
        'longitude' => floatval($each->longitude['decimal_degrees']),
        'altitude' => floatval($each->pressure_altitude)];
      }
    }
    return $pt_records;
  }

  /**
   * Returns the HTML and Javascript to draw the path over GoogleMaps
   *
   * @param        string  $key is the GoogleAPI developer key
   * @param        integer $width in pixels
   * @param        integer $height in pixels
   * @return       string  Returns HTML, CSS, and JavaScript
   */
  public function getMap($key, $width, $height)
  {
    if (count($this->records)<1) {
      $code = "invalid file";
      return $code;
    }

    $code = '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$key.'" type="text/javascript"></script>
<br /><br />
<div id="map" style="width: '.$width.'px; height: '.$height.'px; border: 2px solid #111111;"></div>

<script type="text/javascript">
function loadIGC() {

        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        ';

    $started = false;
    foreach ($this->records as $each) {

      if ($each->type == "B") {

        if (!$started) {
          $code .= "map.setCenter(new GLatLng(".$each->latitude['decimal_degrees'].", ".$each->longitude['decimal_degrees']."), 13, G_SATELLITE_MAP);\n";
          $code .= "var polyline = new GPolyline([\n";
          $started = true;
        }

        $code .= "new GLatLng(".$each->latitude['decimal_degrees'].", ".$each->longitude['decimal_degrees']."),\n";
      }
    }

    $code .= '
        ], "#FF0000", 2);
        map.addOverlay(polyline);

        }

    window.onload = loadIGC;
    </script>';

    return $code;

  }

  /**
   * Returns the full manufacturer string from the code defined in the A record
   *
   * @param        string  $code is the manufacturer's code from the A record
   * @return       string  Full manufacturer string
   */
  public static function GetManufacturerFromCode($code)
  {
    // manufacturer array
    $man = array();

    $man['B'] = "Borgelt";
    $man['C'] = "Cambridge";
    $man['E'] = "EW";
    $man['F'] = "Filser";
    $man['I'] = "Ilec";
    $man['M'] = "Metron";
    $man['P'] = "Peschges";
    $man['S'] = "Sky Force";
    $man['T'] = "PathTracker";
    $man['V'] = "Varcom";
    $man['W'] = "Westerboer";
    $man['Z'] = "Zander";
    $man['1'] = "Collins";
    $man['2'] = "Honeywell";
    $man['3'] = "King";
    $man['4'] = "Garmin";
    $man['5'] = "Trimble";
    $man['6'] = "Motorola";
    $man['7'] = "Magellan";
    $man['8'] = "Rockwell";

    if (!$man[(string)$code]) {
      return false;
    }

    return $man[(string)$code];
  }

  private static function ClassAutoloader($class_name)
  {
    require(dirname(__FILE__).DIRECTORY_SEPARATOR.
            "lib".DIRECTORY_SEPARATOR.$class_name.".php");
  }
  
  private static function startsWith($haystack, $needle)
  {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
  }
}
?>
