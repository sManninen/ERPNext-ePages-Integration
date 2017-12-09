<?php
session_start();
?>

<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
<?php
if ( ! empty($_POST['erpuser'])){
$_SESSION['erpuser'] = $_POST['erpuser'];
}
if ( ! empty($_POST['erppw'])){
$_SESSION['erppw'] = ($_POST['erppw']);
}
if ( ! empty($_POST['erpapiurl'])){
$_SESSION['erpapiurl'] = ($_POST['erpapiurl']);
}
if ( ! empty($_POST['epagesapiurl'])){
$_SESSION['epagesapiurl'] = ($_POST['epagesapiurl']);
}
if ( ! empty($_POST['epagestoken'])){
$_SESSION['epagestoken'] = ($_POST['epagestoken']);
}

if (isset($_POST['jousubmit'])){
	
	// Redirect to this page. ( Post/Redirect/Get )
   header("Location: " . $_SERVER['REQUEST_URI']);
   exit();
}
else{}
?>

<form action='' method='POST'>
<div class="row">
<div class="col-1"><label>ERPNext username: </label></div>
<div class="col-2"><input type="text" name="erpuser" id="textfield1" size="30" value="<?php echo($_SESSION['erpuser']) ?>" <?php if ($_SESSION['erpuser'] != null) {?> style="background-color:#E2E2E2;", readonly <?php } ?> oninput="changefunction1()"/></div>
<div class="col-3"><input type="button" id="edit1" <?php if ($_SESSION['erpuser'] != null) { ?> value="edit" <?php } else { ?> value="set" <?php } ?> class='button' <?php if ($_SESSION['erpuser'] == null) { ?> disabled <?php } ?> onclick="disablefunction1(), confirmbtndisabled()"></input></div>
</div>

<div class="row">
<div class="col-1"><label>ERPNext password: </label></div>
<div class="col-2"><input type="text" name="erppw" id="textfield2" size="30" value="<?php echo($_SESSION['erppw']) ?>" <?php if ($_SESSION['erppw'] != null) {?> style="background-color:#E2E2E2;", readonly <?php } ?> oninput="changefunction2()"/></div>
<div class="col-3"><input type="button" id="edit2" <?php if ($_SESSION['erppw'] != null) { ?> value="edit" <?php } else { ?> value="set" <?php } ?> class='button' <?php if ($_SESSION['erppw'] == null) { ?> disabled <?php } ?> onclick="disablefunction2(), confirmbtndisabled()"></input></div>
</div>

<div class="row">
<div class="col-1"><label>ERPNext api url: </label></div>
<div class="col-2"><input type="text" name="erpapiurl" id="textfield3" size="30" value="<?php echo($_SESSION['erpapiurl']) ?>" <?php if ($_SESSION['erpapiurl'] != null) {?> style="background-color:#E2E2E2;", readonly <?php } ?> oninput="changefunction3()"/></div>
<div class="col-3"><input type="button" id="edit3" <?php if ($_SESSION['erpapiurl'] != null) { ?> value="edit" <?php } else { ?> value="set" <?php } ?> class='button' <?php if ($_SESSION['erpapiurl'] == null) { ?> disabled <?php } ?> onclick="disablefunction3(), confirmbtndisabled()"></input></div>
</div>

<div class="row">
<div class="col-1"><label>ePages api url: </label></div>
<div class="col-2"><input type="text" name="epagesapiurl" id="textfield4" size="30" value="<?php echo($_SESSION['epagesapiurl']) ?>" <?php if ($_SESSION['epagesapiurl'] != null) {?> style="background-color:#E2E2E2;", readonly <?php } ?> oninput="changefunction4()"/></div>
<div class="col-3"><input type="button" id="edit4" <?php if ($_SESSION['epagesapiurl'] != null) { ?> value="edit" <?php } else { ?> value="set" <?php } ?> class='button' <?php if ($_SESSION['epagesapiurl'] == null) { ?> disabled <?php } ?> onclick="disablefunction4(), confirmbtndisabled()"></input></div>
</div>

<div class="row">
<div class="col-1"><label>ePages token: </label></div>
<div class="col-2"><input type="text" name="epagestoken" id="textfield5" size="30" value="<?php echo($_SESSION['epagestoken']) ?>" <?php if ($_SESSION['epagestoken'] != null) {?> style="background-color:#E2E2E2;", readonly <?php } ?> oninput="changefunction5()"/></div>
<div class="col-3"><input type="button" id="edit5" <?php if ($_SESSION['epagestoken'] != null) { ?> value="edit" <?php } else { ?> value="set" <?php } ?> class='button' <?php if ($_SESSION['epagestoken'] == null) { ?> disabled <?php } ?> onclick="disablefunction5(), confirmbtndisabled()"></input></div>
</div>

<div class="row">
<div class="col-1"></div>
<div class="col-2"></div>
<div class="col-3"><input type='submit' name='jousubmit' id='confirmbtn' value='confirm' class='button' <?php if ($_SESSION['erpuser'] == null || $_SESSION['erppw'] == null || $_SESSION['epagesapiurl'] == null || $_SESSION['erpapiurl'] == null || $_SESSION['epagestoken'] == null){ ?> disabled <?php } ?> /></div>
</div>
</form>

<div class="row">
<div class="col-1"></div>
<div class="col-2"><input type='button' id='btn' value='sync products' class='buttonBig' /></div>
</div>

</body>
</html>

<script>
// change functions for textfields and buttons
function disablefunction1() {
	var editbutton1 = document.getElementById("edit1");
	var textfield1 = document.getElementById("textfield1");
	var val = textfield1.value;
	
	if (editbutton1.value === "edit") textfield1.readOnly = false, textfield1.style.backgroundColor = "white", textfield1.focus(), textfield1.value = '', textfield1.value = val, editbutton1.value = "set";	
	else if (editbutton1.value === "set") textfield1.readOnly = true, textfield1.style.backgroundColor = "#E2E2E2", editbutton1.value = "edit";
}

function disablefunction2() {
	var editbutton2 = document.getElementById("edit2");
	var textfield2 = document.getElementById("textfield2");
	var val = textfield2.value;
	
	if (editbutton2.value === "edit") textfield2.readOnly = false, textfield2.style.backgroundColor = "white", textfield2.focus(), textfield2.value = '', textfield2.value = val, editbutton2.value = "set";	
	else if (editbutton2.value === "set") textfield2.readOnly = true, textfield2.style.backgroundColor = "#E2E2E2", editbutton2.value = "edit";
}

function disablefunction3() {
	var editbutton3 = document.getElementById("edit3");
	var textfield3 = document.getElementById("textfield3");
	var val = textfield3.value;
	
	if (editbutton3.value === "edit") textfield3.readOnly = false, textfield3.style.backgroundColor = "white", textfield3.focus(), textfield3.value = '', textfield3.value = val, editbutton3.value = "set";	
	else if (editbutton3.value === "set") textfield3.readOnly = true, textfield3.style.backgroundColor = "#E2E2E2", editbutton3.value = "edit";
}

function disablefunction4() {
	var editbutton4 = document.getElementById("edit4");
	var textfield4 = document.getElementById("textfield4");
	var val = textfield4.value;
	
	if (editbutton4.value === "edit") textfield4.readOnly = false, textfield4.style.backgroundColor = "white", textfield4.focus(), textfield4.value = '', textfield4.value = val, editbutton4.value = "set";	
	else if (editbutton4.value === "set") textfield4.readOnly = true, textfield4.style.backgroundColor = "#E2E2E2", editbutton4.value = "edit";
}

function disablefunction5() {
	var editbutton5 = document.getElementById("edit5");
	var textfield5 = document.getElementById("textfield5");
	var val = textfield5.value;
	
	if (editbutton5.value === "edit") textfield5.readOnly = false, textfield5.style.backgroundColor = "white", textfield5.focus(), textfield5.value = '', textfield5.value = val, editbutton5.value = "set";	
	else if (editbutton5.value === "set") textfield5.readOnly = true, textfield5.style.backgroundColor = "#E2E2E2", editbutton5.value = "edit";
	
	if (textfield5.length == 0) editbutton5.disabled = true;
}

//functions to disable confirm button if textfield is empty
function changefunction1() {
	var textfield1 = document.getElementById("textfield1");
	var editbutton1 = document.getElementById("edit1");
	
	if(textfield1.value !== ""){
	editbutton1.disabled = false;
	}else{
	editbutton1.disabled = true;
	}
}

function changefunction2() {
	var textfield2 = document.getElementById("textfield2");
	var editbutton2 = document.getElementById("edit2");
	
	if(textfield2.value !== ""){
	editbutton2.disabled = false;
	}else{
	editbutton2.disabled = true;
	}
}

function changefunction3() {
	var textfield3 = document.getElementById("textfield3");
	var editbutton3 = document.getElementById("edit3");
	
	if(textfield3.value !== ""){
	editbutton3.disabled = false;
	}else{
	editbutton3.disabled = true;
	}
}

function changefunction4() {
	var textfield4 = document.getElementById("textfield4");
	var editbutton4 = document.getElementById("edit4");
	
	if(textfield4.value !== ""){
	editbutton4.disabled = false;
	}else{
	editbutton4.disabled = true;
	}
}

function changefunction5() {
	var textfield5 = document.getElementById("textfield5");
	var editbutton5 = document.getElementById("edit5");
	
	if(textfield5.value !== ""){
	editbutton5.disabled = false;
	}else{
	editbutton5.disabled = true;
	}
}

function confirmbtndisabled() {
	var textfield1 = document.getElementById("textfield1");
	var textfield2 = document.getElementById("textfield2");
	var textfield3 = document.getElementById("textfield3");
	var textfield4 = document.getElementById("textfield4");
	var textfield5 = document.getElementById("textfield5");
	var confirmbutton = document.getElementById("confirmbtn");
	
	if(textfield1.readOnly == true && textfield2.readOnly == true && textfield3.readOnly == true && textfield4.readOnly == true && textfield5.readOnly == true){
		confirmbutton.disabled = false;
	}else{
		confirmbutton.disabled = true;
	}
}


// ajax product sync
$(document).ready(function(){
    $('#btn').click(function(){
		document.getElementById("btn").disabled = true;
        var clickBtnValue = $(this).val();
        var ajaxurl = 'productsync.php',
        data =  {'action': clickBtnValue};
        $.post(ajaxurl, data, function (response) {
            // Response div goes here.
			alert(response);
			document.getElementById("btn").disabled = false;
          
        });
    });

});

</script>