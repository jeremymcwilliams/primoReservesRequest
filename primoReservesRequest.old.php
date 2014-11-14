<?php
include ("almaAPI/almaAPI.class.php");

/*

Sample incoming URLS:
with MMSIDs:
https://library.lclark.edu/reserves/request/primorequest.php?state=start&mms_id=ie%253D99134815920001451%252Cie%253D99136724590101844%252Cie%253D9910742210001456%252Cie%253D9961553801851%252Cie%253D9936878301852%252Cie%253D99324090230101842&title=Shifu%252C+you%2527ll+do+anything+for+a+laugh+%252F&isbn=1611452228&oclcnum=46619610&au=Mo%252C+Yan%252C+1955-

w/o MMSIDs



need to add portion to parse mmsid field, plus display 


*/
class primoReservesRequest{

	function __construct(){
		
		$this->state="";
		if (isset($_REQUEST["state"])){$this->state=$_REQUEST["state"];}
		else{$this->state="start";}
		

	
	}
	
	function controller(){
	
		switch($this->state){
			
			case "start":
			$this->start();
			break;
			
			case "process":
			$this->process();
			break;
		
		}

	}
	
	
	function start(){
	
		?><h3>Course Reserves Request</h3><?php
	
	/* testing:
	NZ mmsid: 99164822050001451
	IZ mmsid: 99132189790101844
	*/
	
	
		if (isset($_REQUEST["mmsid"])){
			include ("config.php");
		
			$mmsid=$_REQUEST["mmsid"];
			$response=$this->restAPI($APIkey);
			$xml=simplexml_load_string($response);
			
			$alma = new AlmaAPI($apiUser,$apiPass,$instCode);
			$alma->soapConnect("HoldingsInformation");
			$info=$alma->queryAPI("retrieveHoldingsInformation", array("arg0"=>$mmsid));
			//var_dump($info);
			
   			if ($info->errorsExist=="true"){
   			
   				$error=$info->errorList->error->errorCode;
   				//echo "<p>the error is $error</p>";
   				return false;
   		
   			}
   			else{
   				
				$author=$alma->getAuthor($info);
				
				$title=rtrim($alma->getTitle($info), "/");
				
				
				$callNumber=$this->getCallNumber($xml);
				//$callNumber=$alma->getCallNumber($info);
				
				$location=$alma->getLocation($info);
				//$libraryCode=$alma->getLibraryCode($info);
				$libraryCode=$this->getLibrary($xml);
				$location=$this->getLocation($xml);
				$availability=$this->getAvailability($xml);
				
				 
				$pnx=$alma->getPNX($info);
            	//$availability=$alma->getAvailability($info);
				$data["author"]=$author;
				$data["title"]=$title;
				$data["callNumber"]=$callNumber;
				$data["location"]=$location;
				$data["libraryCode"]=$libraryCode;
            	$data["pnx"]=$pnx;
            	$data["availability"]=$availability;
            	$data["mmsid"]=$mmsid;
            	
            	$this->form($data, $loanPeriods);
		

   		}


			
			
			
			
		}
		else{
		
			echo "Sorry, no MMS ID";
		}
	
	
	
	

	
	
	
	}



    function processData($suffix){
    	
    	/*check if data already processed*/
    	if ($_SESSION["currentRecord"]==true){
    		return true;
    	}
    	else{
    		if ($mmsID=$this->get_local_mmsid($suffix)){
    			//echo $mmsID;
    			$_SESSION["mmsID"]=$mmsID;
    			$_SESSION["useAPI"]=true;

    		}
    		else{
    			/* use request data*/
    			$isbn=$this->formatISBN();
    			$_SESSION["title"]=urldecode($_REQUEST["title"]);
    			
    			//echo "requestdata!!!";
    			$_SESSION["author"]=urldecode($_REQUEST["au"]);
    			$_SESSION["oclc"]=$_REQUEST["oclcnum"];
    			$_SESSION["isbn"]=$isbn;
    			$_SESSION["useAPI"]=false;
    	
    		}
    		$_SESSION["currentRecord"]=true;
    		//var_dump($_SESSION); 
    		return true;   	
    	}
    	
	
    }

	function get_local_mmsid($suffix){

		$mmsids=urldecode($_REQUEST["mms_id"]);
		$mms_array=explode(",",$mmsids);
		foreach($mms_array as $mms){
			$a=explode("=",$mms);
			$id=$a[1];
			if (substr($id, -4)==$suffix){
				$mmsID=$id;
				break;
			}
		}
		if (empty($mmsID)){return false;}
		else{return $mmsID;}

	}









	function restAPI($APIkey){
	
		echo $APIkey;
		$ch = curl_init();
		$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/{mms_id}';
		$templateParamNames = array('{mms_id}');
		$templateParamValues = array(urlencode('99132189790101844'));
		$url = str_replace($templateParamNames, $templateParamValues, $url);
		$queryParams = '?' . urlencode('expand') . '=' . urlencode('p_avail') . '&' . urlencode('apikey') . '=' . urlencode($APIkey);
		curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$response = curl_exec($ch);
		curl_close($ch);

		return $response;	
		//var_dump($response);
		
		
		$xml=simplexml_load_string($response);
		var_dump($xml);
		
		$test=$xml->xpath("//datafield[@tag='010']/subfield");
		var_dump($test);
		echo $test[0];
		

	
	}




	
	function form($data, $loanPeriods){
	
	?>
	
		<form class="pure-form pure-form-aligned" method="POST" action="">
    <fieldset>
        <div class="pure-control-group">
            <label for="title">Title</label> <?php echo $data["title"];?>
        </div>
        <div class="pure-control-group">
            <label for="author">Author</label> <?php echo $data["author"];?>
        </div>
        <div class="pure-control-group">
            <label for="callNumber">Call Number</label> <?php echo $data["callNumber"];?>
        </div>
        <div class="pure-control-group">
            <label for="location">Location</label> <?php echo $data["location"];?>
        </div>
        <div class="pure-control-group">
            <label for="library">Library</label> <?php echo $data["libraryCode"];?>
        </div>

        <div class="pure-control-group">
            <label for="library">Loan Period</label> <select name="loanPeriod" id="location">
			<?php
				$c=1;
				foreach ($loanPeriods as $key=>$value){
					if ($c==1){$s="selected";}
					else{$s="";}
					echo "<option value='$key' $s>$value</option>\n";
					$c++;
				
				}
			
			?>
			</select>
		</div>
        <div class="pure-control-group">
            <label for="name">Course</label>
            <input id="name" type="text" placeholder="e.g. Art 101" name="coursecode">
        </div>

        <div class="pure-control-group">
            <label for="courseTitle">Course Title</label>
            <input id="courseTitle" type="text" placeholder="e.g. Introduction to Art" name="coursetitle">
        </div>

        <div class="pure-control-group">
            <label for="profLname">Professor Last Name</label>
            <input id="profLname" name="profLname" type="text" placeholder="e.g. Smith" required>
        </div>

        <div class="pure-control-group">
            <label for="profEmail">Professor Email</label>
            <input id="profLname" type="text" placeholder="jane@lclark.edu" name="profEmail" required>
        </div>

        <div class="pure-control-group">
            <label for="comment">Comment</label>
            <textarea id="comment" type="text" placeholder="Optionally add additional information" name="comment"></textarea>
        </div>

        <div class="pure-controls">
            <button type="submit" class="pure-button pure-button-primary">Submit</button>
        </div>
 
 

 
 
    </fieldset>
    
    
    		<input type="hidden" name="state" value="process">
			<input type='hidden' name='mmsid' value='<?php echo $data["mmsid"];?>'>
			<input type='hidden' name='location' value='<?php echo $data["location"];?>'>
			<input type='hidden' name='title' value='<?php echo urlencode($data["title"]);?>'>
			<input type='hidden' name='author' value='<?php echo urlencode($data["author"]);?>'>
			<input type='hidden' name='callnumber' value='<?php echo urlencode($data["callNumber"]);?>'>
			<input type='hidden' name='lib' value='<?php echo $data["libraryCode"];?>'>
            <input type='hidden' name='pnx' value='<?php echo $data["pnx"];?>'> 
            <input type='hidden' name='availability' value='<?php echo $data["availability"];?>'>





</form>
	<?php	
	
	
	
	
	
	
	
	}
	
	
	function process(){
	
		echo "process";
		var_dump($_POST);
		
		$args=array(
			"loanperiod"=>FILTER_SANITIZE_ENCODED,
			"coursecode"=>FILTER_SANITIZE_ENCODED,
			"coursetitle"=>FILTER_SANITIZE_ENCODED,
			"mmsid"=>FILTER_SANITIZE_ENCODED,
			"location"=>FILTER_SANITIZE_ENCODED,
			"title"=>FILTER_SANITIZE_ENCODED,
			"author"=>FILTER_SANITIZE_ENCODED,
			"callnumber"=>FILTER_SANITIZE_ENCODED,
			"lib"=>FILTER_SANITIZE_ENCODED,
			"pnx"=>FILTER_SANITIZE_ENCODED,
			"availability"=>FILTER_SANITIZE_ENCODED
			);
		
		
		$input=filter_var_array($_POST, $args);
		
		var_dump($input);	
		

    	if ($this->send_notice()){

            echo "<p>Request sent!</p>";
            echo "<p>Circulation staff will process your request promptly. Available materials will typically be processed within two business days.  If an item is checked out, it will be recalled and placed on reserve when returned. </p>";
            echo "<p>If you have any questions, please <a href='mailto:reserves@lclark.edu'>email</a> Circulation staff or call the Circulation desk at (503)768-7270.</p>";
            echo "<p><a href='javascript:window.close();'>Close This Window</a>";

    	}
    	else {

    		echo "Message did not send. Sorry.";

    	
    	}

	
	}
	

	function clean($var){	
		$clean=filter_var($var, FILTER_SANITIZE_SPECIAL_CHARS);
		return $clean;

	}







    function send_notice(){
 
        #date_default_timezone_set('America/Los_Angeles');

       $pnx=$_REQUEST["pnx"]; 
       $url="http://alliance-primo.hosted.exlibrisgroup.com/primo_library/libweb/action/display.do?tabs=detailsTab&ct=display&fn=search&doc=LCC".$pnx."&indx=10&recIds=LCC".$pnx;
       
       $url="http://alliance-primo.hosted.exlibrisgroup.com/primo_library/libweb/action/display.do?"
       ."tabs=detailsTab&ct=display&fn=search&doc=LCC".$pnx."&indx=1&recIds=LCC".$pnx."&recIdxs=0"
       ."&elementId=0&renderMode=poppedOut&displayMode=full&frbrVersion=&dscnt=1&scp.scps=scope%3A%28LCC%29"
       ."&frbg=&tab=default_tab&srt=rank&mode=Basic&dum=true&tb=t&vid=LCC&gathStatIcon=true";
       

        
        $to_email= "jeremym@lclark.edu";
        $getname=urldecode($reserves[0]);
        
    	$prof=explode(" | ",$getname);
        $p=$prof[0];

        $prof_username=$_SESSION["patroninfo"]["username"];
        $to  = "$to_email"; 
        //$to  .= "rjp@lclark.edu" . ", "; 
        //$to .= ", jeremym@lclark.edu";        
        
        //    $to ="$prof_email";
    	$subject ="Watzek Library Course Reserve Request: ".$this->title;
    

    	$message=$_REQUEST["fullname"]." has submitted the following course reserves request to Watzek Library:\n\n"
             ."Title: ".$this->title."\n"
             ."Author: ".$this->author."\n"
             ."Call Number: ".$this->callnumber."\n"
             ."Location: ".$_POST["location"]."\n"
             ."Library: ".$_POST["lib"]." \n"
             ."Availability: ".$_POST["availability"]." \n"
             ."URL: $url\n\n"; 


        $message.="Course: ".$_POST["coursecode"]."\n";
        $message.="Course Title: ".$_POST["coursetitle"]."\n";
        $message.="Instructor: ".$_POST["profLname"]."\n";
        $message.="Instructor Email: ".$_POST["profEmail"]."\n";
        $message.="Reserve Loan Period: ".$_POST["loanPeriod"]."\n";
        $message.="Comment: ".$_POST["comment"]."\n\n";    
        $message.="Please fill this request within 24 hours, and inform the instructor.";
    	$headers = 'From: Watzek Library <reserves@lclark.edu>' . "\r\n";

    	if (mail($to, $subject, $message, $headers)){return true;}
    	else {return false;}    
    
    
    }
    
    function getCallNumber($xml){
    
		$data=$xml->xpath("//datafield[@tag='AVA']/subfield[@code='d']");
		return $data[0];        
    
    }
    
    function getLibrary($xml){
    
		$data=$xml->xpath("//datafield[@tag='AVA']/subfield[@code='b']");
		return $data[0];        
    
    }

    function getLocation($xml){
    
		$data=$xml->xpath("//datafield[@tag='AVA']/subfield[@code='c']");
		return $data[0];        
    
    }

    function getAvailability($xml){
    
		$data=$xml->xpath("//datafield[@tag='AVA']/subfield[@code='e']");
		return $data[0];        
    
    }


}

?>