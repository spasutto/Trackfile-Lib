<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads GPX file
 */
class GPXReader implements ITrackfileReader
{
	private $fhandle;

	/**
	 * Class constructor creates the FLGReader object from a file path.
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
    $xml = null;
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
        foreach($xml->xpath("//default:wpt") as $pt)
          $assign_pt($pt);
    }
    return $pt_records;
  }
  /**
   * Returns the point list
   *//*
  public function getRecords()
  {
    $pt_records = array();
    if ($this->fhandle) {
      echo print_r($this->fhandle->wpt);
      //foreach ($this->fhandle->wpt as $pt) {
        foreach ($this->fhandle->trk as $trk) {
          foreach($trk->trkseg as $seg){
              foreach($seg->trkpt as $pt){
        $pt_records[] = (object)[
        'date' => DateTime::createFromFormat('Y-m-d\TH:i:s+', (string) $pt->time),
        'latitude' => (string) $pt['lat'],
        'longitude' => (string) $pt['lon'],
        'altitude' => (string) $pt->ele
				];
      }
    }}}
    return $pt_records;
  }*/
}
?>
