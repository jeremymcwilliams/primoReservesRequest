primoReservesRequest
====================
<h3>Primo Reserves Request</h3>

<p>Description: Instructor clicks a General Electronic Services link in Primo, and is taken to this script. Upon entering information about the course and specific reserves loan period, an email is sent to reserves staff with course and item information.</p>

<h4>System Requirements</h4>
<ul>
<li>PHP 5.2 or greater, with CURL extension enabled</li>
<li>PHP settings configured to send mail (<a href='http://php.net/manual/en/mail.configuration.php' target='_blank'>more information</a>)</li>

</ul>


<h4>Setup</h4>

<h5>The Code</h5>
<ul>
<li>Download files, and put in a web accessible directory</li>
<li>Edit the config.php file to reflect local settings.
	<ul>
		<li>$loanPeriods: update this array with the appropriate key=>value pairs to reflect the reserves loan periods available at your institution.</li>
		<li>$APIkey: enter your API key from the Ex Libris <a href='https://developers.exlibrisgroup.com' target='_blank'>Developer Network</a>. Set up your key so it can use the Bibs API, and the current plan is Prod Read-Only.</li>
		<li>$IZsuffix: the last four digits of your institution's standard MMS ID</li>
		<li>$circEmail: the email address the reserves requests should target</li>
		<li>$circPhone: the circulation phone number, as displayed to the user</li>
		<li>$circMessage</li>
	</ul>

</li>
</ul>

<h5>Alma/Primo Back Office</h5>

<li>Set up General Electronic Services link in Alma.</li>
<code>http://your.domain.com/pathToDirectory/index.php?state=start&mms_id={rfr_dat}&title={rft.title}&isbn={rft.isbn}&oclcnum={rft.oclcnum}&au={rft.au}</code>
<li>You may need to adjust Primo Back Office template settings to get the MMS IDs to populate the link.</li>
<li>As of Jan 2015, dedupe merged records will not populate the GES link with the MMS ID, hence the other metadata fields.</li>


