<?php

/*

Sample incoming URLS:
with MMSIDs:
https://library.lclark.edu/reserves/request/primorequest.php?state=start&mms_id=ie%253D99134815920001451%252Cie%253D99136724590101844%252Cie%253D9910742210001456%252Cie%253D9961553801851%252Cie%253D9936878301852%252Cie%253D99324090230101842&title=Shifu%252C+you%2527ll+do+anything+for+a+laugh+%252F&isbn=1611452228&oclcnum=46619610&au=Mo%252C+Yan%252C+1955-


http://localhost/~jeremym/primoReservesRequest/index.php?state=start&mms_id=ie%253D99134815920001451%252Cie%253D99136724590101844%252Cie%253D9910742210001456%252Cie%253D9961553801851%252Cie%253D9936878301852%252Cie%253D99324090230101842&title=Shifu%252C+you%2527ll+do+anything+for+a+laugh+%252F&isbn=1611452228&oclcnum=46619610&au=Mo%252C+Yan%252C+1955-

w/o MMSIDs (dedup merge, 99103017320101844 )

http://localhost/~jeremym/primoReservesRequest/index.php?state=start&mms_id=ie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D%252Cie%253D&title=Charlotte%2527s+web+%252F&isbn=0812417992+%2528PFNL%2529&oclcnum=00225924&au=White%252C+E.+B.+1899-1985.+%2528Elwyn+Brooks%2529%252C

need to add portion to parse mmsid field, plus display 


*/
require_once("config.php");
require_once("almaRestAPI.php");



class primoReservesRequest{

	function __construct(){
		
		$this->state="";
		if (isset($_REQUEST["state"])){$this->state=$this->clean($_REQUEST["state"]);}
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
		
		if ($data=$this->processGet()){
			$this->getForm($data);
		}
		else{
		
			echo "The request is not valid.";
		}

	}	
	

	/* cleans get variables, determines whether there's a valid MMSID or not*/
	function processGet(){

	
		if (isset($_GET["mms_id"])){
			
		
			if ($mmsid=$this->get_local_mmsid()){
				///echo $mmsid;
			
				if ($data=$this->getAPIData($mmsid)){
				
					//great!
				}
				else{$data=$this->useGetData();}
			}
			else{$data=$this->useGetData();}
			
			var_dump($data);
			
			
			return $data;
		}
		else{return false;}	
	
	
	
	}
	
	function getAPIData($mmsid){
	
		$data=array();
		$almaAPI=new almaAPI();
		$response=$almaAPI->retrieveBibRecord(APIKEY, $mmsid);
		$xml=simplexml_load_string($response);
		//var_dump($xml);
		
		if ( $almaAPI->errorCheck($xml)){
			return false;
		}
		else{

			$callNumber=$this->clean($almaAPI->getCallNumber($xml));
			$libraryCode=$this->clean($almaAPI->getLibrary($xml));
			$title=rtrim($this->clean($almaAPI->getTitle($xml)), '/');
			$author=$this->clean($almaAPI->getAuthor($xml));
			$availability=$this->clean($almaAPI->getAvailability($xml));
			$location=$this->clean($almaAPI->getLocation($xml));
			$data["title"]=urldecode($title);
			$data["callNumber"]=$callNumber;
			$data["author"]=$author;
			$data["libraryCode"]=$libraryCode;
			$data["availability"]=$availability;
			$data["location"]=$location;
			$data["mmsid"]=$mmsid;
			$data["useAPI"]=true;
			return $data;
		}

	}
	
	function useGetData(){
	
		$data=array();

  		$title=urldecode($this->clean($_GET["title"]));
  		$isbn=urldecode($this->clean($_GET["isbn"]));
  		$oclcnum=urldecode($this->clean($_GET["oclcnum"]));
  		$author=urldecode($this->clean($_GET["au"]));
  		//echo $title; 
  		$data["useAPI"]=false;
  		$data["title"]=$title;
  		$data["isbn"]=$isbn;
  		$data["oclc"]=$oclcnum;
  		$data["author"]=$author;
  		
  		return $data;
	}
	
	function get_local_mmsid(){
		var_dump($_GET);
		$mmsids=$this->clean(urldecode($_GET["mms_id"]));
		$mms_array=explode(",",$mmsids);
		foreach($mms_array as $mms){
			$a=explode("=",$mms);
			$id=$a[1];

			if (substr($id, -4)==SUFFIX){
				$mmsID=$id;
				break;
			}
		}
		if (empty($mmsID)){return false;}
		else{return $mmsID;}

	}
	
	function getForm($data){
		
		$loanPeriods=unserialize(LOANPERIODS);
		?>
		
				<form class="pure-form pure-form-aligned" method="POST" action="">
    <fieldset>
        <div class="pure-control-group">
            <label for="title">Title</label> <?php echo $data["title"];?>
        </div>
        <div class="pure-control-group">
            <label for="author">Author</label> <?php echo $data["author"];?>
        </div>

		<?php
		if ($data["useAPI"]==true){$this->getAPIFormData($data);}
		if ($data["useAPI"]==false){$this->getGetFormData($data);}

		?>
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
            <input id="name" type="text" placeholder="e.g. Art 101" name="courseCode">
        </div>

        <div class="pure-control-group">
            <label for="courseTitle">Course Title</label>
            <input id="courseTitle" type="text" placeholder="e.g. Introduction to Art" name="courseTitle">
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
    
    		<input type="hidden" name="state" value="process">
			
			<input type='hidden' name='title' value='<?php echo urlencode($data["title"]);?>'>
			<input type='hidden' name='author' value='<?php echo urlencode($data["author"]);?>'>





    	</fieldset>
	</form>	
	
		
	<?php
	}



	
	function getGetFormData($data){
	
	?>
	
        <div class="pure-control-group">
            <label for="callNumber">ISBN</label> <?php echo $data["isbn"];?>
        </div>
        <div class="pure-control-group">
            <label for="location">OCLC</label> <?php echo $data["oclc"];?>
        </div>	


        <input type='hidden' name='isbn' value='<?php echo urlencode($data["isbn"]);?>'>
        <input type='hidden' name='oclc' value='<?php echo urlencode($data["oclc"]);?>'>
        <input type='hidden' name='useAPI' value='false'>	
	<?php
	}
	
	function getAPIFormData($data){

		?>
        <div class="pure-control-group">
            <label for="callNumber">Call Number</label> <?php echo $data["callNumber"];?>
        </div>
        <div class="pure-control-group">
            <label for="location">Location</label> <?php echo $data["location"];?>
        </div>
        <div class="pure-control-group">
            <label for="library">Library</label> <?php echo $data["libraryCode"];?>
        </div>
		<input type='hidden' name='callnumber' value='<?php echo urlencode($data["callNumber"]);?>'>
		<input type='hidden' name='lib' value='<?php echo urlencode($data["libraryCode"]);?>'>
        <input type='hidden' name='availability' value='<?php echo urlencode($data["availability"]);?>'>
        <input type='hidden' name='location' value='<?php echo urlencode($data["location"]);?>'>
        <input type='hidden' name='mmsid' value='<?php echo urlencode($data["mmsid"]);?>'>
        <input type='hidden' name='useAPI' value='true'>
	    <?php
	
	
	
	}
	
	function process(){
    	if ($this->sendNotice()){
			echo CIRCMESSAGE;
    	}
    	else {
    		echo "Message did not send. Sorry.";
    	}	

	}
	
	function sendNotice(){
		
		$title=urldecode($_POST["title"]);
		$author=urldecode($_POST["author"]);
		$profLname=urldecode($_POST["profLname"]);
				
		$to= CIRCEMAIL;
		$subject = EMAILSUBJECTPREFIX. "$title";
		$headers = "From: ". LIBRARYNAME." <".CIRCEMAIL.">" . "\r\n";
		$message= "Professor $profLname  has submitted the following course reserves request to ". LIBRARYNAME.":\n\n"
		$message.="Title: $title \n";
        $message.="Author: $author \n"
				
		if ($_POST["useAPI"]=="true"){$message.=$this->getAPIEmailFields();}
		if ($_POST["useAPI"]=="false"){$message.=$this->getGetEmailFields();}
        
        $message.="Course: ".$_POST["courseCode"]."\n";
        $message.="Course Title: ".$_POST["courseTitle"]."\n";
        $message.="Instructor: ".$_POST["profLname"]."\n";
        $message.="Instructor Email: ".$_POST["profEmail"]."\n";
        $message.="Reserve Loan Period: ".$_POST["loanPeriod"]."\n";
        $message.="Comment: ".$_POST["comment"]."\n\n";    
        $message.="Please fill this request within 24 hours, and inform the instructor.";		
				
    	if (mail($to, $subject, $message, $headers)){return true;}
    	else {return false;}  	
	
	
	}
	
	function getAPIEmailFields(){
		
		$callnumber=urldecode($_POST["callnumber"]);
		$location=urldecode($_POST["location"]);
		$library=urldecode($_POST["lib"]);
		$availability=urldecode($_POST["availability"]);		
		$url="";
		
		$fields="Call Number: $callnumber \n"
             ."Location: $location \n"
             ."Library: $library \n"
             ."Availability: $availability \n"
             ."URL: $url\n\n"; 	
             
        return $fields;
	
	
	
	}
	
	function getGetEmailFields(){
	
		$url="";
		$isbn=urldecode($_POST["isbn"]);
		$oclc=urldecode($_POST["oclc"]);
		
		$fields="ISBN: $isbn \n"
		."OCLC: $oclc \n"
		."URL: $url \n";
		
		return $fields;
	
	
	}
	
		
		
	
	function clean($var){	
		$clean=filter_var($var, FILTER_SANITIZE_SPECIAL_CHARS);
		return $clean;

	}	
}





?>