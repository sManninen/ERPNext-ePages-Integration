<?php
//stops active sessions on page load
if (session_status() == PHP_SESSION_ACTIVE) {
	session_unset();
	session_destroy();
}
?>

<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style.css">
	<script src="jquery.js"></script>
	<script type="text/javascript" src="mainpageJS.js"></script>
</head>
<body>

<div class="row" id="bigRow">
	<div class="container" id="cont-1">
		<form action='' method='POST' id="form">
		
			<div class="row">
				<div class="col-2"><input type="text" name="erpuser" id="textfield1" class="textField" value="" /><p class="helper" id="helper1">ERPNext username</p></div>
				<div class="col-3"><input type="button" id="edit1" value="set" class='button' ></input></div>
			</div>

			<div class="row">
				<div class="col-2"><input type="text" name="erppw" id="textfield2" class="textField" size="30" value="" /><p class="helper" id="helper2">ERPNext password</p></div>
				<div class="col-3"><input type="button" id="edit2" value="set" class='button' ></input></div>
			</div>

			<div class="row">
				<div class="col-2"><input type="text" name="erpapiurl" id="textfield3" class="textField" size="30" value="" /><p class="helper" id="helper3">ERPNext api url</p></div>
				<div class="col-3"><input type="button" id="edit3" value="set" class='button' ></input></div>
			</div>

			<div class="row">
				<div class="col-2"><input type="text" name="epagesapiurl" id="textfield4" class="textField" size="30" value="" /><p class="helper" id="helper4">ePages api url</p></div>
				<div class="col-3"><input type="button" id="edit4" value="set" class='button' ></input></div>
			</div>

			<div class="row">
				<div class="col-2"><input type="text" name="epagestoken" id="textfield5" class="textField" size="30" value="" /><p class="helper" id="helper5">ePages token</p></div>
				<div class="col-3"><input type="button" id="edit5" value="set" class='button' ></input></div>
			</div>

			<div class="row" id="lastRow">
				<div class="col-2"><input type='button' name='clearbutton' id='clearbtn' value='clear' class='button' /></div>
				<div class="col-3"><input type='button' name='confirmbutton' id='confirmbtn' value='submit' class='button' disabled /></div>
			</div>

		</form>
	</div>

	<div class="container" id="cont-2">
		<div class="overlay" id="overlay2"></div>
		<textarea id="log" maxlength="20"  readonly></textarea>
	</div>
</div>

<div class="row" id="bigRow">
	<div class="overlay" id="overlay"><a class="processingText" id="processingAnim1">P</a><a class="processingText">R</a><a class="processingText" id="processingAnim3">O</a><a class="processingText" id="processingAnim2">C</a><a class="processingText">ESSING</a></div>

	<div class="container" id="cont-3">
		<div class="row" id="lastRow">
			<div class="col-1"><input type='button' id='btn' value='products' class='buttonBig'></input></div>
			<div class="col-1"><input type='button' id='btn2' value='customers' class='buttonBig'></input></div>
			<div class="col-1"><input type='button' id='btn3' value='orders' class='buttonBig'></input></div>
		</div>
	</div>
</div>

</body>
</html>

<?php
if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'setSesVars':
			setSesVarsFunction();
            break;
    }
}

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'clearSesVars':
			clearSesVarsFunction();
            break;
    }
}

function setSesVarsFunction() {	
	session_start();
	$_SESSION['erpuser'] = $_REQUEST['erpuser'];
	$_SESSION['erppw'] = $_REQUEST['erppw'];
	$_SESSION['erpapiurl'] = $_REQUEST['erpapiurl'];
	$_SESSION['epagesapiurl'] = $_REQUEST['epagesapiurl'];
	$_SESSION['epagestoken'] = $_REQUEST['epagestoken'];
}

function clearSesVarsFunction() {
	$_SESSION['erpuser'] = "";
	$_SESSION['erppw'] = "";
	$_SESSION['erpapiurl'] = "";
	$_SESSION['epagesapiurl'] = "";
	$_SESSION['epagestoken'] = "";	
	session_unset();
	session_destroy();
}
?>
