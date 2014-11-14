<?php

$loanPeriods=array("wrs3h"=>"3 hours", "wrs1d"=>"1 day","wrs3d"=>"3 days");

$APIkey="l7xx9e56b6c65ff54991b232234541fd9721";

$IZsuffix="1844";  //the last 4 digits if your institution's standard MMSID

$circEmail="jeremym@lclark.edu";
$circPhone="(503)768-7270";

$circMessage="
	<p>Request sent!</p>
	<p>Circulation staff will process your request promptly. Available materials will typically be processed within two business days.  If an item is checked out, it will be recalled and placed on reserve when returned. </p>
	<p>If you have any questions, please <a href='mailto:$circEmail'>email</a> Circulation staff or call the Circulation desk at $circPhone.</p>
	<p><a href='javascript:window.close();'>Close This Window</a>

";

$emailSubjectPrefix="Watzek Library Course Reserve Request: ";
$libraryName="Watzek Library";


define ("CIRCEMAIL", $circEmail);
define ("APIKEY", $APIkey);
define ("LOANPERIODS", serialize($loanPeriods));
define ("SUFFIX", $IZsuffix);
define ("CIRCMESSAGE", $circMessage);
define ("EMAILSUBJECTPREFIX", $emailSubjectPrefix);
define ("LIBRARYNAME", $libraryName);







?>