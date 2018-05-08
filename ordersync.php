<?php
session_start();

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'orders':
			ghi();
            break;
    }
}

function ghi(){
	
	set_time_limit(600);
	
	$orderCount = 0;

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

	//======================get orders from erpnext======================
	$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Sales%20Order?&fields=["*"]&limit_page_length=1500]'); 
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);

	$erp_orders = json_decode($result, true);
	$errorCheck = gettype($erp_orders);
	if ($errorCheck != "array") {
		curl_close($ch);
		echo"could not connect to ERPNext.";
		return;
	}

	//======================get ordercount from epages======================
	$authorization = 'Authorization: Bearer '.$_SESSION['epagestoken'].'';

	//get product count
	$ch = curl_init(''.$_SESSION['epagesapiurl'].'orders?resultsPerPage=1&page=1');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec($ch);
	
	$epages_orderCount = json_decode($result, true);

	$errorCheck = substr($result, 2, 7);
	if ($errorCheck != "results") {
		curl_close($ch);
		echo"could not connect to ePages.";
		return;
	}

	//======================get erpnext order names======================
	$erpOrderNames = array();
	foreach($erp_orders["data"] as $value) {
		array_push($erpOrderNames, $value["title"]);
	}

	//======================get orders from epages======================
	$temparray = array();

	$count1 = ($epages_orderCount["results"]/100);
	$count2 = ceil($count1);

	if ($count2 >= 1) {
		while ($count2 >= 1) {

			$c2 = curl_init(''.$_SESSION['epagesapiurl'].'orders?resultsPerPage=100&page='.$count2.'');
			curl_setopt($c2, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
			curl_setopt($c2, CURLOPT_RETURNTRANSFER, true);
			$result=curl_exec($c2);

			$epages_orders = json_decode($result, true);

			foreach($epages_orders["items"] as $indexi => $uusiorder) {
				array_push($temparray, $epages_orders["items"][$indexi]);
			}

			$count2 --;
		}
	}

	foreach($temparray as $value) {
	
		if (!in_array($value["orderNumber"], $erpOrderNames)) {
	
			$dDate = date("Y-m-d",strtotime(date("Y-m-d") . "+14 days"));
	
			//creates an order
			$orderArray = array();
			$orderArray["data"] = array(
			"title" => ''.$value["orderNumber"].'',
			"customer" => ''.$value["billingAddress"]["firstName"].' '.$value["billingAddress"]["lastName"].' - '.$value["customerNumber"].'',
			"delivery_date" => ''.$dDate.'',
			"items" => []
			);
	
			//gets the products included in the order
			$ch = curl_init(''.$value["links"]["0"]["href"].'');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result=curl_exec($ch);

			$epages_orderProducts = json_decode($result, true);
	
			foreach($epages_orderProducts["lineItemContainer"]["productLineItems"] as $index => $value_1) {
				$orderArray["data"]["items"][] = ["item_code" => ''.$value_1["name"].'',
				"qty" => ''.$value_1["quantity"]["amount"].'',
				"rate" => ''.$value_1["singleItemPrice"]["amount"].''];
		
				//gets variant data if the item is one
				if($value_1["variationString"] != "") {
					$ch = curl_init(''.$value_1["links"]["0"]["href"].'');
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result=curl_exec($ch);

					$epages_orderProductVariantData = json_decode($result, true);
					foreach($epages_orderProductVariantData["productVariationSelection"] as $value11) {
						$orderArray["data"]["items"][$index]["item_code"] .= '-'.$value11["value"].'';
					}
				}
			}
	
			//moves the orders
			$data = json_encode($orderArray["data"]);

			$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Sales%20Order');
			curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$result=curl_exec($ch);
	
			$orderCount ++;
		}
	}
	curl_close($ch);
	echo "$orderCount orders created.";
}
?>