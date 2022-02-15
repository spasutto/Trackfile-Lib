<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads GPX file
 */
class GPXReader implements ITrackfileReader
{
	private $fhandle;
	public $duration;

	/**
	 * Class constructor creates the FLGReader object from a file path.
	 *
	 * @param				string	$file_path usually this will be the request vars
	 */
	public function __construct($file_path)
	{
		set_error_handler("exception_error_handler");
		if (is_file($file_path)) {
			$this->fhandle = @simplexml_load_file($file_path);
		} else {
			$this->fhandle = @simplexml_load_string($file_path);
		}
		set_error_handler(NULL);
	}

	/**
	 * Returns the point list
	 */
	public function getRecords($first = false)
	{
		$pt_records = array();
		$pts = null;
		$namespaces = array();
		$this->duration = 0;
		$startdate = null;
		if ($this->fhandle) {
			$namespaces = $this->fhandle->getNamespaces(true);
			if(isset($namespaces[""]))	// if you have a default namespace
			{
				// register a prefix for that default namespace:
				$this->fhandle->registerXPathNamespace("default", $namespaces[""]);
			}
			foreach ($namespaces as $ns => $nsurl) {
				$prefixns = "default:";
				if (strlen($ns)>0)
					$prefixns = $ns.":";
				$pts = $this->fhandle->xpath("//".$prefixns."trkpt");
				if (!$pts)
					$pts = $this->fhandle->xpath("//".$prefixns."wpt");
				if ($pts)
					break;
			}
		}
		if (is_array($pts)) {
			$assign_pt = function($pt) use (&$pt_records, $prefixns, $namespaces) {
				if(isset($namespaces[""]))
					$pt->registerXPathNamespace("default", $namespaces[""]);
				$curdate = $pt->xpath("".$prefixns."time");
				$curele = $pt->xpath("".$prefixns."ele");
				if (is_array($curdate) && count($curdate)>0)
					$curdate = $curdate[0];
				if (is_array($curele) && count($curele)>0)
					$curele = $curele[0];
				$pt_records[] = (object)[
				'date' => DateTime::createFromFormat('Y-m-d\TH:i:s+', (string) $curdate, new DateTimeZone('UTC')),
				'latitude' => floatval((string) $pt['lat']),
				'longitude' => floatval((string) $pt['lon']),
				'altitude' => floatval((string) $curele)
				];
			};
			foreach($pts as $pt) {
				$assign_pt($pt);
				if ($first)
					break;
			}
			$this->duration = ($pt_records[count($pt_records)-1]->date->getTimestamp()-$pt_records[0]->date->getTimestamp());
		}
		return $pt_records;
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
