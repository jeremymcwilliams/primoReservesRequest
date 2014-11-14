<?php

class almaAPI{


	
	function retrieveBibRecord($APIkey, $mmsid){
	
		echo $APIkey;
		$ch = curl_init();
		$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/{mms_id}';
		$templateParamNames = array('{mms_id}');
		$templateParamValues = array(urlencode($mmsid));
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
	
	function errorCheck($xml){
		
		echo $xml->errorsExist;
		if ($xml->errorsExist=="true"){return true;}
		else{return false;}
	
	
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

    function getTitle($xml){
    
		$data=$xml->xpath("//datafield[@tag='245']/subfield[@code='a']");
		return $data[0];        
    
    }

    function getAuthor($xml){
    
		$data=$xml->xpath("//datafield[@tag='100']/subfield[@code='a']");
		return $data[0];        
    
    }	
	
}




?>




