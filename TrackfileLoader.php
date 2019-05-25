<?php
require(dirname(__FILE__).'/IGCReader.php');
require(dirname(__FILE__).'/FLGReader.php');

/**
 * Class utility for loading a trackfile
 */
class TrackfileLoader
{
  /**
   * Load a trackfile and return the associated reader
   */
	public static function load($file)
	{
		$path_parts = pathinfo($file);
		if (!isset($path_parts['extension']))
				return false;
		switch (strtolower($path_parts['extension']))
		{
			case 'igc':
				return new IGCReader($file);
			case 'flg':
				return new FLGReader($file);
			default:
				return false;
		}
	}

  /**
   * Return a GPX string representation of the trackfile
   */
	public static function toGPX($file)
	{
		$gpxdata = '';
		$tfreader = TrackfileLoader::load($file);
		if (!$tfreader)
		{
			return "<?xml version=\"1.0\"?><err>bad or unknown trackfile</err>";
		}
		$pts = $tfreader->getRecords();
		$GPX_HEADER = "<?xml version=\"1.0\"?>\n<gpx\nxmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\nxmlns=\"http://www.topografix.com/GPX/1/1\"\nxsi:schemaLocation=\"http://www.topografix.com/GPX/1/1/gpx.xsd\"\nversion=\"1.1\">\n\t<trk>\n\t\t<src>FlightLog</src>\n\t\t<trkseg>\n";
		$GPX_FOOTER = "\t\t</trkseg>\n\t</trk>\n</gpx>";

		$gpxdata = $GPX_HEADER;
		foreach ($pts as $pt)
		{
			$gpxdata .= sprintf('			<trkpt lat="'.$pt->latitude.'" lon="'.$pt->longitude.'">'."\n");
			$gpxdata .= sprintf('				<ele>'.$pt->altitude.'</ele>'."\n");
			$gpxdata .= sprintf('				<time>'.TrackfileLoader::toGMT($pt->date).'</time>'."\n");
			$gpxdata .= sprintf('			</trkpt>'."\n");
		}
		$gpxdata .= $GPX_FOOTER;
		return $gpxdata;
	}

  /**
   * Convert a date to GPX ISO8601 like format
	 * e.g. 2018-11-04T10:33:20Z
   */
  public static function toGMT($date)
  {
    return substr(date('c', $date->getTimestamp()), 0, 19).'Z';
  }

}
?>