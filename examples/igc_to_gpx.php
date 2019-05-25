<?php
if (isset($_GET['file']))
	$trackfile = $_GET['file'];
else
{
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/trackfile.igc")."'>Example IGC</a><BR>";
	echo "<a href='".$_SERVER['REQUEST_URI']."?file=".urlencode("trackfiles/trackfile.flg")."'>Example FLG</a><BR>";
	exit(0);
}

require('../TrackfileLoader.php');

header("Content-type: text/xml");
echo TrackfileLoader::toGPX($trackfile);
?>
