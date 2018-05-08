<?php
session_start();

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'products':
			abc();
            break;
    }
}

function abc(){
	
	set_time_limit(600);
	
	$productCount = 0;

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

	//======================get items from erpnext=====================
	//get the normal/parent items
	$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item?fields=["item_name","standard_rate","item_group","item_code","description"]&limit_page_length=1500&filters=[["Item","variant_of","=",""]]'); 
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);
	
	$erp_parentItems = json_decode($result, true);

	//get variant items
	$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item?fields=["item_name","standard_rate","item_group","item_code","description"]&limit_page_length=1500&filters=[["Item","variant_of","!=",""]]'); 
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);

	$erp_variantItems = json_decode($result, true);

	$errorCheck = gettype($erp_parentItems);
	if ($errorCheck != "array") {
		curl_close($ch);
		echo"could not connect to ERPNext.";
		return;
	}

	//======================get itemcount from epages======================
	$authorization = 'Authorization: Bearer '.$_SESSION['epagestoken'].'';

	$ch = curl_init(''.$_SESSION['epagesapiurl'].'products?resultsPerPage=1&page=1');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);
	
	$epages_itemCount = json_decode($result, true);

	$errorCheck = substr($result, 2, 7);
	if ($errorCheck != "results") {
		curl_close($ch);
		echo"could not connect to ePages.";
		return;
	}

	//======================get erpnext item names======================
	$erpParentItemNames = array();
	foreach($erp_parentItems["data"] as $value) {
		array_push($erpParentItemNames, $value["item_name"]);
	}

	$erpVariantItemNames = array();
	foreach($erp_variantItems["data"] as $value) {
		array_push($erpVariantItemNames, $value["item_name"]);
	}

	//======================get items from epages======================
	$temparray = array();
	$tempVariantArray = array();
	$tempVariantArray["data"] = array();
	
	$count1 = ($epages_itemCount["results"]/100);
	$count2 = ceil($count1);

	while ($count2 >= 1) {

		$ch = curl_init(''.$_SESSION['epagesapiurl'].'products?resultsPerPage=100&page='.$count2.'');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result=curl_exec($ch);

		$epages_items = json_decode($result, true);

		foreach($epages_items["items"] as $index => $value) {
			array_push($temparray, $epages_items["items"][$index]);
		}

		$count2 --;

	}

	foreach($temparray as $value) {
	
		//creates a normal/parent product
		$parentArray = array();
		$parentArray["data"] = array(
		"item_name" => ''.$value["name"].'',
		"item_code" => ''.$value["name"].'',
		"item_group" => "Products"
		);
	
		//gets the variant data and makes the item a parent if it has variants
		if($value["productVariationType"] == "master") {
			$ch = curl_init(''.$value["links"]["1"]["href"].'');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result=curl_exec($ch);

			$epages_variantData = json_decode($result, true);
		
			$parentArray["data"]["has_variants"] = true;
			$parentArray["data"]["attributes"] = [];
			foreach($epages_variantData["variationAttributes"] as $index => $value_2) {
				$parentArray["data"]["attributes"][] = ["attribute" => ''.$epages_variantData["variationAttributes"][$index]["name"].''];
			}

			//creates a variant product
			foreach($epages_variantData["items"] as $value_2) {
			
				$variantArray = array(
				"item_name" => ''.$value["name"].'',
				"item_code" => ''.$value["name"].'',
				"item_group" => "Products",
				"variant_of" => ''.$value["name"].'',
				"attributes" => []
				);

				//gives attributes to the variant and names it after them
				foreach($epages_variantData["variationAttributes"] as $index => $value_3){
					$variantArray["item_name"] .= '-'.$value_2["attributeSelection"][$index]["value"].'';
					$variantArray["item_code"] .= '-'.$value_2["attributeSelection"][$index]["value"].'';
					$variantArray["attributes"][] = ["attribute" => ''.$value_2["attributeSelection"][$index]["name"].'', "attribute_value" => ''.$value_2["attributeSelection"][$index]["value"].''];
				}
				if (!in_array($variantArray["item_name"], $erpVariantItemNames)) {
					array_push($tempVariantArray["data"], $variantArray);
				}
			}
		}
	
		//moves the parent products
		if (!in_array($value["name"], $erpParentItemNames)) {
	
			$data = json_encode($parentArray["data"]);

			$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item');
			curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$result=curl_exec($ch);
	
			$productCount ++;
		}
	}

	// moves the variant products
	if ($tempVariantArray["data"]) {
		foreach ($tempVariantArray["data"] as $index => $value) {	
			$data = json_encode($tempVariantArray["data"][$index]);

			$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item');
			curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$result=curl_exec($ch);
		
			$productCount ++;
		}
	}
	curl_close($ch);
	echo "$productCount items created.";
}
?>