<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads KML file
 */
class KMLReader implements ITrackfileReader
{
	private $fhandle;

	/**
	 * Class constructor creates the KMLReader object from a file path.
   *
   * @param        string  $file_path usually this will be the request vars
   */
  public function __construct($file_path)
  {
    $this->fhandle = @simplexml_load_file($file_path);
  }

  /**
   * Returns the point list
   */
  public function getRecords()
  {
    $pt_records = array();
    //TODO
    /*$xml = null;
    if ($this->fhandle) {
      $namespaces = $this->fhandle->getNamespaces(true);
      if(isset($namespaces[""]))  // if you have a default namespace
      {
        // register a prefix for that default namespace:
        $this->fhandle->registerXPathNamespace("default", $namespaces[""]);
        // and use that prefix in all of your xpath expressions:
        $xpath_to_document = "//default:gpx";
      }
      else
        $xpath_to_document = "//gpx";
      $xml = $this->fhandle->xpath($xpath_to_document);
      if (is_array($xml))
        $xml = $xml[0];
      if(isset($namespaces[""]))
        $xml->registerXPathNamespace("default", $namespaces[""]);
    }
    if ($xml) {
      $assign_pt = function($pt) use (&$pt_records) {
        $pt_records[] = (object)[
        'date' => DateTime::createFromFormat('Y-m-d\TH:i:s+', (string) $pt->time),
        'latitude' => (string) $pt['lat'],
        'longitude' => (string) $pt['lon'],
        'altitude' => (string) $pt->ele
				];
      };
      foreach($xml->xpath("//default:trkpt") as $pt)
        $assign_pt($pt);
      if (count($pt_records)<=0)
        foreach($xml->xpath("//default:wpt") as $pt)
          $assign_pt($pt);
    }*/
    return $pt_records;
  }
}
?>
