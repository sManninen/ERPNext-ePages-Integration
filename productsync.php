<?php
session_start();

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'products':
			syncProducts();
            break;
    }
}

function syncProducts(){
	
	set_time_limit(600);
	
	$erp_productCount = 0;
	$epages_productCount = 0;

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
	
	//======================get erpnext item names======================
	$erpParentItemNames = array();
	foreach($erp_parentItems["data"] as $value) {
		array_push($erpParentItemNames, $value["item_name"]);
	}

	$erpVariantItemNames = array();
	foreach($erp_variantItems["data"] as $value) {
		array_push($erpVariantItemNames, $value["item_name"]);
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
	
	//================get epages item names===========================
	$epagesItemNames = array();
	foreach($temparray as $value) {
		array_push($epagesItemNames, $value["name"]);
	}
	
	//=================gets the variant attributes from erpnext=========================
	$ch = curl_init('http://localhost:8080/api/resource/Item%20Attribute?&fields=["*"]&limit_page_length=1500]'); 
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);

	$erp_attributes = json_decode($result, true);
	$erp_attributeNames = array();
	
	foreach($erp_attributes["data"] as $value){
		array_push($erp_attributeNames, $value["name"]);
	}

	//================move items from epages to erpnext=====================
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
				"standard_rate" => ''.$value["priceInfo"]["price"]["amount"].'',
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
			
			//moves the variant attributes
			foreach($parentArray["data"]["attributes"] as $value2){
				if (!in_array($value2["attribute"], $erp_attributeNames)) {					
					$attributeArray = array();
					$attributeArray["data"] = array(
					"name" => ''.$value2["attribute"].'',
					"attribute_name" => ''.$value2["attribute"].''
					);
					
					$data = json_encode($attributeArray["data"]);

					$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item%20Attribute');
					curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

					$result=curl_exec($ch);
					array_push($erp_attributeNames, $value2["attribute"]);
				};
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
	
			$erp_productCount ++;
		}
	}

	// moves the variant products
	if ($tempVariantArray["data"]) {
					
		//gets the variant attribute values from erpnext.
		$ch = curl_init('http://localhost:8080/api/resource/Item%20Attribute%20Value?&fields=["*"]&limit_page_length=1500]'); 
		curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result=curl_exec($ch);

		$erp_attributeValues = json_decode($result, true);
		$erp_attributeValueNames = array();
		
		function array_push_assoc($array, $key, $value){
		$array[$key] = $value;
		return $array;
		}
		
		foreach($erp_attributeValues["data"] as $value){
			$erp_attributeValueNames = array_push_assoc($erp_attributeValueNames, $value["attribute_value"], $value["parent"]);
		}
		
		foreach ($tempVariantArray["data"] as $index => $value) {					
			foreach($value["attributes"] as $value2) {			
			//moves the variant attribute values
				if (!in_array($value2["attribute_value"], $erp_attributeValueNames)){
					$abbr = substr($value2["attribute_value"], 0, 3);
					
					$attributeValueArray = array();
					$attributeValueArray["data"] = array(
					"attribute_value" => ''.$value2["attribute_value"].'',
					"abbr" => ''.$abbr.'',
					"parent" => ''.$value2["attribute"].'',
					"parenttype" => "Item Attribute"
					);
					
					$data = json_encode($attributeValueArray["data"]);

					$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item%20Attribute%20Value');
					curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

					$result=curl_exec($ch);
					$erp_attributeValueNames = array_push_assoc($erp_attributeValueNames, $value2["attribute_value"], $value2["attribute"]);
				}
				else if (in_array($value2["attribute_value"], $erp_attributeValueNames)) { //checks if the attribute value is in erpnext...
					if ($erp_attributeValueNames[$value2["attribute_value"]] != $value2["attribute"]) { //...and if it's for the same parent attribute.
						$abbr = substr($value["attributes"]["attribute_value"], 0, 2);
						
						$attributeValueArray = array();
						$attributeValueArray["data"] = array(
						"attribute_value" => ''.$value2["attribute_value"].'',
						"abbr" => ''.$abbr.'',
						"parent" => ''.$value2["attribute"].''
						);
						
						$data = json_encode($attributeValueArray["data"]);

						$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item%20Attribute%20Value');
						curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

						$result=curl_exec($ch);
						$erp_attributeValueNames = array_push_assoc($erp_attributeValueNames, $value2["attribute_value"], $value2["attribute"]);
					}
				};
			}
			
			//moves the variant products
			$data = json_encode($tempVariantArray["data"][$index]);

			$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item');
			curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$result=curl_exec($ch);
		
			$erp_productCount ++;
		}
	}
	
	//===================move items from erpnext to epages=======================
	//variants cannot be created in epages through the api.
	foreach($erp_parentItems["data"] as $value){
	
		if(!in_array($value["item_name"], $epagesItemNames)) {
			$itemArray = array (
			"productNumber" => ''.$value["item_name"].'',
			"name" => ''.$value["item_name"].'',
			"shortDescription" => "testituote2",
			"description" => "testituote2",
			"manufacturer" => "testituote2",
			"price" => ''.$value["standard_rate"].'',
			"searchKeywords" => ["testituote2"],
			"visible" => 1
			);
			
			$data = json_encode($itemArray);
			
			$ch = curl_init(''.$_SESSION['epagesapiurl'].'products');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			
			$result=curl_exec($ch);
			
			$epages_productCount ++;	
		}		
	}
	
	curl_close($ch);
	echo "$erp_productCount items created in ERPNext.\n$epages_productCount items created in ePages.";
}
?>