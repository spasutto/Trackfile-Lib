<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads KML file
 */
class KMLReader implements ITrackfileReader
{
	private $fhandle;
	public $duration;

	/**
	 * Class constructor creates the KMLReader object from a file path.
	 *
	 * @param				string	$file_path usually this will be the request vars
	 */
	public function __construct($file_path)
	{
		if (is_file($file_path)) {
			$this->fhandle = @simplexml_load_file($file_path);
		} else {
			$this->fhandle = @simplexml_load_string($file_path);
		}
	}

	/**
	 * Returns the point list
	 */
	public function getRecords($first = false)
	{
		$pt_records = array();
		$xml = null;
		$prefixns = "";
		if ($this->fhandle) {
			$namespaces = $this->fhandle->getNamespaces(true);
			if(isset($namespaces[""]))	// if you have a default namespace
			{
				// register a prefix for that default namespace:
				$this->fhandle->registerXPathNamespace("default", $namespaces[""]);
				$prefixns = "default:";
				// and use that prefix in all of your xpath expressions:
				$xpath_to_document = "//".$prefixns."Document";
			}
			else
				$xpath_to_document = "//Document";
			$xml = $this->fhandle->xpath($xpath_to_document);
			if (is_array($xml))
				$xml = $xml[0];
			if(strlen($prefixns)>0)
				$xml->registerXPathNamespace("default", $namespaces[""]);
		}
		if ($xml) {
			foreach ($namespaces as $ns => $nsurl)
			{
				$nstmpprefix1 = $prefixns;
				if (strlen($ns)>0)
					$nstmpprefix1 = $ns.":";
				foreach($xml->xpath("//".$nstmpprefix1."Track") as $trk)
				{
					if(strlen($prefixns)>0)
						$trk->registerXPathNamespace("default", $namespaces[""]);
					foreach ($namespaces as $ns2 => $nsurl2)
					{
						$nstmpprefix2 = $prefixns;
						if (strlen($ns2)>0)
							$nstmpprefix2 = $ns2.":";
						foreach($trk->xpath($nstmpprefix2."when") as $pt)
							$pt_records[] = (object)['date' => DateTime::createFromFormat('Y-m-d\TH:i:s+', (string) $pt, new DateTimeZone('UTC')), 'latitude' => 0.0, 'longitude' => 0.0, 'altitude' => 0.0];
						$i = 0;
						foreach($trk->xpath($nstmpprefix2."coord") as $pt) {
							//-93.3806146339391 44.8823651507134 2743
							$line = explode(' ', $pt);
							if (count($line)<3)
								continue;
							$pt_records[$i]->latitude = floatval($line[0]);
							$pt_records[$i]->longitude = floatval($line[1]);
							$pt_records[$i]->altitude = floatval($line[2]);
							$i++;
							if ($first)
								break;
						}
					}
				}
			}
		}
		/*echo "<pre>";
		print_r($pt);
		echo "</pre>";*/
		$this->duration = ($pt_records[count($pt_records)-1]->date->getTimestamp()-$pt_records[0]->date->getTimestamp());
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
