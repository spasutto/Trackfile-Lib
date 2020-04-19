<?php
if (isset($_GET['file']))
	$trackfile = $_GET['file'];
else
{
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/trackfile.igc")."'>Example IGC</a><BR>";
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/trackfile.flg")."'>Example FLG</a><BR>";
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/trackfile.gpx")."'>Example GPX</a><BR>";
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/waypoints.gpx")."'>Example GPX (waypoints)</a><BR>";
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/waypoints.wpt")."'>Example WPT</a><BR>";
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/trackfile.kml")."'>Example KML</a><BR>";
	exit(0);
}

require('../TrackfileLoader.php');

$tfreader = TrackfileLoader::load($trackfile);
if (!$tfreader)
{
	echo "bad or unknown trackfile";
	return;
}
$pts = $tfreader->getRecords();
//echo json_encode($pts, JSON_PRETTY_PRINT);
?>
<table border="1">
<tr>
	<th>date/time</th>
	<th>latitude</th>
	<th>longitude</th>
	<th>altitude</th>
</tr>
<?php
foreach ($pts as $pt)
{
	echo "<tr>\n";
	echo "\t<td>".TrackfileLoader::toGMT($pt->date/*->format('Y-m-d H:i:s')*/).'</td><td>'.$pt->latitude.'</td><td>'.$pt->longitude.'</td><td>'.$pt->altitude.'</td>';
	echo "</tr>\n";
}
?>
</table>
