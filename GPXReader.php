<?php
require_once(dirname(__FILE__).'/ITrackfileReader.php');


/**
 * Reads GPX file
 */
class GPXReader implements ITrackfileReader
{
	private $doc;

	/**
	 * Class constructor creates the PHP_IGC object from a file path.
	 *
	 * @param				string	$file_path usually this will be the request vars
	 */
	public function __construct($file_path)
	{
		$this->doc = new DOMDocument();
		$this->doc->load($file_path);
	}

	/**
	 * Returns the point list
	 */
	public function getRecords()
	{
		$pt_records = array();

		if ($this->doc) {
			$gpxprefix = '';
			$xpath = new DOMXpath($this->doc);
			$trkpts = $xpath->query("//*[local-name() = 'trkpt'][1]");
			if (count($trkpts)>1)
				$gpxprefix = $trkpts[0]->prefix;
			else if (is_a($trkpts, 'DOMNodeList'))
				$gpxprefix = $trkpts->item(0)->prefix;
			else
				$gpxprefix = $trkpts->prefix;
			$gpxprefix = trim($gpxprefix);
			if (strlen($gpxprefix)>1)
				$gpxprefix .= ':';
			else
				$gpxprefix .= 'x:';
			//echo $gpxprefix;
			
			$context = $this->doc->documentElement;
			/*var_dump($context->getAttribute("xmlns"));
			var_dump($context->lookupNamespaceURI(NULL));
			var_dump($context->namespaceURI);*/
			foreach( $xpath->query('namespace::*', $context) as $node ) {
				$prefix = $node->localName;
				if (strlen(trim($node->prefix)) == 0)
					$prefix = "x";
				$xpath->registerNamespace($prefix, $node->nodeValue);
			}

			//$xpath->registerNamespace("x", "http://www.topografix.com/GPX/1/1");
			//$xpath->registerNamespace("x", "http://www.topografix.com/GPX/1/0");
			//$gpxprefix = "x:";
			$trkpts = $xpath->query("//".$gpxprefix."trkpt");
			if (!is_null($trkpts)) {
				foreach ($trkpts as $trkpt) {
					//print_r($trkpt);
					$pt_records[] = (object)[
					'date' => DateTime::createFromFormat('Y-m-d\TH:i:s+', $trkpt->getElementsByTagName('time')->item(0)->nodeValue),
					'latitude' => $trkpt->getAttribute('lat'),
					'longitude' => $trkpt->getAttribute('lon'),
					'altitude' => $trkpt->getElementsByTagName('ele')->item(0)->nodeValue
					];
				}
			}
		}
		return $pt_records;
	}
}
?>
