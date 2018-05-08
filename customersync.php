<?php
session_start();

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'customers':
			def();
            break;
    }
}

function def(){
	
	set_time_limit(600);
	
	$customerCount = 0;

	//======================login to erpnext======================
	$COOKIE_FILE = dirname(__FILE__)."/cookie.txt";

	$ch = curl_init(''.$_SESSION['erpapiurl'].'method/login');
	curl_setopt( $ch, CURLOPT_POSTFIELDS,array(
	'usr'=>$_SESSION['erpuser'],
	'pwd'=>$_SESSION['erppw']
	));
	curl_setopt ($ch, CURLOPT_COOKIEJAR, $COOKIE_FILE); 
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);

	//======================get customers from erpnext======================
	$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Customer?fields=["customer_name","name"]&limit_page_length=1500'); 
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);

	$erp_customers = json_decode($result, true);

	$errorCheck = gettype($erp_customers);
	if ($errorCheck != "array") {
		curl_close($ch);
		echo"could not connect to ERPNext.";
		return;
	}

	//======================get customer count from epages======================
	$authorization = 'Authorization: Bearer '.$_SESSION['epagestoken'].'';

	//get customer count
	$ch = curl_init(''.$_SESSION['epagesapiurl'].'customers?resultsPerPage=1&page=1');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);
	
	$epages_customerCount = json_decode($result, true);

	$errorCheck = substr($result, 2, 7);
	if ($errorCheck != "results") {
		curl_close($ch);
		echo"could not connect to ePages.";
		return;
	}

	//====================get erpnext customer names==============
	$erpCustomerNames = array();
	foreach($erp_customers["data"] as $value) {
		array_push($erpCustomerNames, $value["customer_name"]);
	}

	//======================get customers from epages======================
	$temparray = array();
	
	$count1 = ($epages_customerCount["results"]/100);
	$count2 = ceil($count1);

	while ($count2 >= 1) {

		$ch = curl_init(''.$_SESSION['epagesapiurl'].'customers?resultsPerPage=100&page='.$count2.'');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result=curl_exec($ch);

		$epages_customers = json_decode($result, true);

		foreach($epages_customers["items"] as $value) {
			array_push($temparray, $value);
		}

		$count2 --;

	}

	foreach($temparray as $value) {
	
		$CustomerFullName = $value['billingAddress']['firstName'] . ' ' . $value['billingAddress']['lastName'];
		$CustomerFullNameId = $CustomerFullName . ' - ' . $value['customerNumber'];
	
		if (!in_array($CustomerFullNameId, $erpCustomerNames)) {
	
			//creates a customer
			$customerArray = array();
			$customerArray["data"] = array(
			"customer_name" => $CustomerFullNameId,
			"customer_type" => "Company",
			"customer_group" => "Commercial",
			"territory" => "All Territories"
			);

			//creates an address
			$addressArray = array();
			$addressArray["data"] = array(
			"customer" => $CustomerFullNameId,
			"address_title" => $CustomerFullNameId,
			"address_type" => "Billing",
			"address_line1" => $value['billingAddress']['street'],
			"city" => $value['billingAddress']['city']
			);
	
			//creates a country
			$countryArray = array();
			$countryArray["data"] = array(
			"country_name" => $value['billingAddress']['country']
			);
	
			//moves the customer
			$data = json_encode($customerArray["data"]);

			$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Customer');
			curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$result=curl_exec($ch);
	
			if($value['billingAddress']['street'] != "" || $value['billingAddress']['city'] != ""){
			
				if($value['billingAddress']['country'] != ""){
		
					//======================get countries from erpnext======================
					$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Country?fields=["country_name"]&limit_page_length=1500'); 
					curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result=curl_exec($ch);

					$erp_countries = json_decode($result, true);
	
					//====================get erpnext country names==============
					$erpCountryNames = array();
					foreach($erp_countries["data"] as $value_1) {
						array_push($erpCountryNames, $value_1["country_name"]);
					}

					//moves the country
					if (!in_array($value['billingAddress']['country'], $erpCountryNames)) {
		
						$data = json_encode($countryArray["data"]);

						$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Country');
						curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

						$result=curl_exec($ch);
					}
					$addressArray["data"]["country"] = $value['billingAddress']['country'];
				}

				else if($value['billingAddress']['country'] = "") {
					$addressArray["data"]["country"] = "Finland";
				}		
		
				//moves the address
				$data = json_encode($addressArray["data"]);

				$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Address');
				curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

				$result=curl_exec($ch);
			}
	
			$customerCount ++;
		}
	}
	curl_close($ch);
	echo "$customerCount customers created.";
}
?>