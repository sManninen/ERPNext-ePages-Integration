<?php
session_start();

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'sync products':
			abc();
            break;
    }
}

function abc(){
	
	set_time_limit(600);
	
//tuotevientilaskuri
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

// Read the session saved in the cookie file
//$file = fopen("cookie.txt", 'r');

//======================get items from erpnext======================

//hakee kaikki paitsi variantit 
$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item?fields=["item_name","standard_rate","item_group","item_code","description"]&limit_page_length=1500&filters=[["Item","variant_of","=",""]]'); 
curl_setopt ($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result=curl_exec($ch);

//hakee variantit
$chVariant = curl_init(''.$_SESSION['erpapiurl'].'resource/Item?fields=["item_name","standard_rate","item_group","item_code","description"]&limit_page_length=1500&filters=[["Item","variant_of","!=",""]]'); 
curl_setopt ($chVariant, CURLOPT_COOKIEFILE, $COOKIE_FILE); 
curl_setopt($chVariant, CURLOPT_RETURNTRANSFER, true);
$resultVariant=curl_exec($chVariant);

$hakudata = json_decode($result, true);
$hakudataVariant = json_decode($resultVariant, true);

 //======================get itemcount from epages======================
$authorization = 'Authorization: Bearer '.$_SESSION['epagestoken'].'';

//get product count
$cGetCount = curl_init(''.$_SESSION['epagesapiurl'].'products?resultsPerPage=1&page=1');
curl_setopt($cGetCount, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
curl_setopt($cGetCount, CURLOPT_RETURNTRANSFER, true);
$result=curl_exec($cGetCount);

$hakudata6 = json_decode($result, true);
$count1 = ($hakudata6["results"]/100);
$count2 = ceil($count1);

curl_close($cGetCount);

//======================erittelee erpnext kentät======================
$erpTuoteNimet = array();
foreach($hakudata["data"] as $value) {
		array_push($erpTuoteNimet, $value["item_name"]);
}

$erpVariantTuoteNimet = array();
foreach($hakudataVariant["data"] as $value) {
		array_push($erpVariantTuoteNimet, $value["item_name"]);
}

//======================get items from epages======================

$temparray = array();
$tempVariantArray = array();
$tempVariantArray["data"] = array();

while ($count2 >= 1) {

$c2 = curl_init(''.$_SESSION['epagesapiurl'].'products?resultsPerPage=100&page='.$count2.'');
curl_setopt($c2, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
curl_setopt($c2, CURLOPT_RETURNTRANSFER, true);
$result=curl_exec($c2);

$hakudata2 = json_decode($result, true);

foreach($hakudata2["items"] as $indexi => $uusituote) {
	array_push($temparray, $hakudata2["items"][$indexi]);
}

$count2 --;

}
curl_close($c2);

$responseLimit = false;
$responseLimit2 = false;

foreach($temparray as $value) {
	
	//tekee templatetuotteen (tai varsinaisen tuotteen, jos sillä ei ole variantteja)
	$variantParentArray = array();
	$variantParentArray["data"] = array(
	"item_name" => ''.$value["name"].'',
	"item_code" => ''.$value["name"].'',
	"item_group" => "Products"
	);
	
	//hakee varianttitiedot ja tekee tuotteesta parentin, jos sillä on variantteja
	if($value["productVariationType"] == "master") {
		$c77 = curl_init(''.$value["links"]["1"]["href"].'');
		curl_setopt($c77, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization )); 
		curl_setopt($c77, CURLOPT_RETURNTRANSFER, true);
		$result=curl_exec($c77);

		$hakudata77 = json_decode($result, true);
		curl_close($c77);
		
		$variantParentArray["data"]["has_variants"] = true;
		$variantParentArray["data"]["attributes"] = [];
		foreach($hakudata77["variationAttributes"] as $index3 => $value4) {
		$variantParentArray["data"]["attributes"][] = ["attribute" => ''.$hakudata77["variationAttributes"][$index3]["name"].''];
		}

//tekee varianttituotteet
	foreach($hakudata77["items"] as $value2) {
			
		$variantChildArray = array(
		"item_name" => ''.$value["name"].'',
		"item_code" => ''.$value["name"].'',
		"item_group" => "Products",
		"variant_of" => ''.$value["name"].'',
		"attributes" => []
		);

	// lisää varianteille attribuutit ja nimeää tuotteen niiden mukaan
	foreach($hakudata77["variationAttributes"] as $index2 => $value3){
		$variantChildArray["item_name"] .= '-'.$value2["attributeSelection"][$index2]["value"].'';
		$variantChildArray["item_code"] .= '-'.$value2["attributeSelection"][$index2]["value"].'';
		$variantChildArray["attributes"][] = ["attribute" => ''.$value2["attributeSelection"][$index2]["name"].'', "attribute_value" => ''.$value2["attributeSelection"][$index2]["value"].''];
	}
	if (!in_array($variantChildArray["item_name"], $erpVariantTuoteNimet)) {
		array_push($tempVariantArray["data"], $variantChildArray);
	}
	}
	}
	
//vie variantittomat ja parent tuotteet
if (!in_array($value["name"], $erpTuoteNimet)) {
	
	$data = json_encode($variantParentArray["data"]);

	$ch = curl_init(''.$_SESSION['erpapiurl'].'resource/Item');
	curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIE_FILE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$result2=curl_exec($ch);
	curl_close($ch);
	
	$productCount ++;
}
}

// vie variant-tuotteet
if ($tempVariantArray["data"]) {
	foreach ($tempVariantArray["data"] as $index => $value) {	
		$data2 = json_encode($tempVariantArray["data"][$index]);

		$ch4 = curl_init(''.$_SESSION['erpapiurl'].'resource/Item');
		curl_setopt($ch4, CURLOPT_COOKIEFILE, $COOKIE_FILE);
		curl_setopt($ch4, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch4, CURLOPT_POST, true);
		curl_setopt($ch4, CURLOPT_POSTFIELDS, $data2);

		$result4=curl_exec($ch4);
		curl_close($ch4);
		
		$productCount ++;
	}
}
echo "$productCount items created";
}
?>