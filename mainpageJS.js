window.onload = function() {

var erpuser = "";
var erppw = "";
var erpapiurl = "";
var epagesapiurl = "";
var epagestoken = "";

var textfield1 = document.getElementById("textfield1");
var textfield2 = document.getElementById("textfield2");
var textfield3 = document.getElementById("textfield3");
var textfield4 = document.getElementById("textfield4");
var textfield5 = document.getElementById("textfield5");

var editbutton1 = document.getElementById("edit1");
var editbutton2 = document.getElementById("edit2");
var editbutton3 = document.getElementById("edit3");
var editbutton4 = document.getElementById("edit4");
var editbutton5 = document.getElementById("edit5");

var helper1 = document.getElementById("helper1");
var helper2 = document.getElementById("helper2");
var helper3 = document.getElementById("helper3");
var helper4 = document.getElementById("helper4");
var helper5 = document.getElementById("helper5");

var log = document.getElementById("log");
var confirmbutton = document.getElementById("confirmbtn");
var fillables = document.getElementById("cont-1");
var syncbuttons = document.getElementById("cont-3");
var overlay = document.getElementById("overlay");

var anim1 = document.getElementById("processingAnim1");
var anim2 = document.getElementById("processingAnim2");
var anim3 = document.getElementById("processingAnim3");

textfield1.value = ""; textfield2.value = ""; textfield3.value = ""; textfield4.value = ""; textfield5.value = "";

editbutton1.disabled = true; editbutton2.disabled = true; editbutton3.disabled = true; editbutton4.disabled = true; editbutton5.disabled = true;

log.value = '';
log.value += 'Fill out the fields on the left.'+'\n';

//to keep track how many textfields are being edited
var editCounter = 5;

//disables editbutton if textfield is empty, keeps helper-text minimized if textfield is not empty
function inputFunction(e) {
	if (e.target.id == "textfield1") {
		var editbutton = document.getElementById("edit1");
	} else if (e.target.id == "textfield2") {
		var editbutton = document.getElementById("edit2");
	} else if (e.target.id == "textfield3") {
		var editbutton = document.getElementById("edit3");
	} else if (e.target.id == "textfield4") {
		var editbutton = document.getElementById("edit4");
	} else if (e.target.id == "textfield5") {
		var editbutton = document.getElementById("edit5");
	}	
	var value = e.target.value;
	var fieldLength = value.length;
	
	if (fieldLength > 0) {
		e.target.parentElement.classList.add("FocusWithText");
		editbutton.style.backgroundColor = "#30baff";
		editbutton.disabled = false;
	} else {
		e.target.parentElement.classList.remove("FocusWithText");
		editbutton.style.backgroundColor = "#c4c4c4";
		editbutton.disabled = true;
	}	
}

//enables/disables text-fields when appropriate. Enables confirm-button when all fields are filled.
function disableFunction(e) {
	if (e.target.id == "edit1") {
		var textfield = document.getElementById("textfield1");
		var sesVar = erpuser;
	} else if (e.target.id == "edit2") {
		var textfield = document.getElementById("textfield2");
		var sesVar = erppw;
	} else if (e.target.id == "edit3") {
		var textfield = document.getElementById("textfield3");
		var sesVar = erpapiurl;
	} else if (e.target.id == "edit4") {
		var textfield = document.getElementById("textfield4");
		var sesVar = epagesapiurl;
	} else if (e.target.id == "edit5") {
		var textfield = document.getElementById("textfield5");
		var sesVar = epagestoken;
	}	
	var value = e.target.value;
	
	// edit value
	if (value === "edit") {
		confirmbutton.disabled = true, confirmbutton.style.backgroundColor = "#c4c4c4", editCounter++, textfield.readOnly = false, textfield.style.borderColor = null, textfield.style.backgroundColor = null, textfield.style.color = "black", textfield.style.pointerEvents = null, textfield.focus(), e.target.value = "set"; syncbuttons.style.filter = "grayscale(100%)"; syncbuttons.style.pointerEvents = "none";
	
	// set value		
	// if no values have been edited and all other values are set
	} else if (e.target.value === "set" && textfield1.value == erpuser && textfield2.value == erppw && textfield3.value == erpapiurl && textfield4.value == epagesapiurl && textfield5.value == epagestoken && editCounter == 1) {
		confirmbutton.disabled = true, confirmbutton.style.backgroundColor = "#c4c4c4", editCounter--, textfield.readOnly = true, textfield.style.borderColor = "#63ff60", textfield.style.backgroundColor = "#e0e0e0", textfield.style.color = "#848484", textfield.style.pointerEvents = "none", e.target.value = "edit"; syncbuttons.style.filter = "grayscale(0%)"; syncbuttons.style.pointerEvents = "auto";
	// if no values have been edited but some value(s) are not set
	} else if (e.target.value === "set" && textfield1.value == erpuser && textfield2.value == erppw && textfield3.value == erpapiurl && textfield4.value == epagesapiurl && textfield5.value == epagestoken && editCounter != 1) {
		confirmbutton.disabled = true, confirmbutton.style.backgroundColor = "#c4c4c4", editCounter--, textfield.readOnly = true, textfield.style.borderColor = "#63ff60", textfield.style.backgroundColor = "#e0e0e0", textfield.style.color = "#848484", textfield.style.pointerEvents = "none", e.target.value = "edit";
	// if value was not edited but other value(s) have been edited	
	} else if (e.target.value === "set" && textfield.value == sesVar && (textfield1.value != erpuser || textfield2.value != erppw || textfield3.value != erpapiurl || textfield4.value != epagesapiurl || textfield5.value != epagestoken)) {
		confirmbutton.disabled = false, confirmbutton.style.backgroundColor = "#30baff", editCounter--, textfield.readOnly = true, textfield.style.borderColor = "#63ff60", textfield.style.backgroundColor = "#e0e0e0", textfield.style.color = "#848484", textfield.style.pointerEvents = "none", e.target.value = "edit";
	// if all other values are set and all values have been edited
	} else if (e.target.value === "set" && textfield1.value != erpuser && textfield2.value != erppw && textfield3.value != erpapiurl && textfield4.value != epagesapiurl && textfield5.value != epagestoken && editCounter == 1) {
		confirmbutton.disabled = false, confirmbutton.style.backgroundColor = "#30baff", editCounter--, textfield.readOnly = true, textfield.style.borderColor = "#faff00", textfield.style.backgroundColor = "#e0e0e0", textfield.style.color = "#848484", textfield.style.pointerEvents = "none", e.target.value = "edit";
	// if value was edited and all other values are set
	} else if (e.target.value === "set" && textfield.value != sesVar && editCounter == 1) {
		confirmbutton.disabled = false, confirmbutton.style.backgroundColor = "#30baff", editCounter--, textfield.readOnly = true, textfield.style.borderColor = "#faff00", textfield.style.backgroundColor = "#e0e0e0", textfield.style.color = "#848484", textfield.style.pointerEvents = "none", e.target.value = "edit";
	// if value was edited and other value(s) are not set
	} else if (e.target.value === "set" && textfield.value != sesVar && editCounter != 1) {
		confirmbutton.disabled = true, confirmbutton.style.backgroundColor = "#c4c4c4", editCounter--, textfield.readOnly = true, textfield.style.borderColor = "#faff00", textfield.style.backgroundColor = "#e0e0e0", textfield.style.color = "#848484", textfield.style.pointerEvents = "none", e.target.value = "edit";
	}
	
}

textfield1.addEventListener('input', inputFunction);
textfield2.addEventListener('input', inputFunction);
textfield3.addEventListener('input', inputFunction);
textfield4.addEventListener('input', inputFunction);
textfield5.addEventListener('input', inputFunction);

editbutton1.addEventListener('click', disableFunction);
editbutton2.addEventListener('click', disableFunction);
editbutton3.addEventListener('click', disableFunction);
editbutton4.addEventListener('click', disableFunction);
editbutton5.addEventListener('click', disableFunction);


// move products
    $('#btn').click(moveProducts);

// move customers
    $('#btn2').click(moveCustomers);

// move orders
    $('#btn3').click(moveOrders);
	
	function moveProducts() {	
		overlay.style.opacity = "1";
		syncbuttons.style.pointerEvents = "none";
		anim1.classList.add("processingAnim");
		anim1.style.color = "#ffde07";
		log.value += 'Starting product sync.'+'\n';
		fillables.style.webkitFilter = "grayscale(100%)"; fillables.style.pointerEvents = "none";
        var clickBtnValue = "products";
        var ajaxurl = 'productsync.php',
        data =  {'action': clickBtnValue};
        $.post(ajaxurl, data, function (response) {
			log.value += response+'\n';
			log.scrollTop = log.scrollHeight;
			clearAnim();
        });
	};
	
	function moveCustomers() {		
		overlay.style.opacity = "1";
		syncbuttons.style.pointerEvents = "none";
		anim2.classList.add("processingAnim");
		anim2.style.color = "#38a5ff";
		log.value += 'Starting customer sync.'+'\n';
		fillables.style.webkitFilter = "grayscale(100%)"; fillables.style.pointerEvents = "none";
        var clickBtnValue = "customers";
        var ajaxurl = 'customersync.php',
        data =  {'action': clickBtnValue};
        $.post(ajaxurl, data, function (response) {
			log.value += response+'\n';
			log.scrollTop = log.scrollHeight;
			clearAnim();
        });
	};
	
	function moveOrders() { 
		$(function(){
		promiseFunction1().done(function(){
		promiseFunction2().done(function(){
			anim3.classList.add("processingAnim");
			anim3.style.color = "#0bdd23";
			log.value += 'Starting order sync.'+'\n';
			var clickBtnValue = "orders";
			var ajaxurl = 'ordersync.php',
			data =  {'action': clickBtnValue};
			$.post(ajaxurl, data, function (response) {
				log.value += response+'\n';
				log.scrollTop = log.scrollHeight;
				clearAnim();
			});		
		});
		});
		});
	
		function promiseFunction1() {	
			var dfrd = $.Deferred();	
			overlay.style.opacity = "1";
			syncbuttons.style.pointerEvents = "none";
			anim1.classList.add("processingAnim");
			anim1.style.color = "#ffde07";
			log.value += 'Starting product sync.'+'\n';
			fillables.style.webkitFilter = "grayscale(100%)"; fillables.style.pointerEvents = "none";
			var clickBtnValue = "products";
			var ajaxurl = 'productsync.php',
			data =  {'action': clickBtnValue};
			$.post(ajaxurl, data, function (response) {
				log.value += response+'\n';
				log.scrollTop = log.scrollHeight;
				anim1.classList.remove("processingAnim");
				anim1.style.color = "white";
				if(response.slice(-8) == "created.") {
					dfrd.resolve();
				} else {
					clearAnim();
					return;
				}
			});
			return $.when(dfrd).done(function(){
			}).promise();
		}
		
		function promiseFunction2() {	
			var dfrd = $.Deferred();	
			anim2.classList.add("processingAnim");
			anim2.style.color = "#38a5ff";
			log.value += 'Starting customer sync.'+'\n';
			var clickBtnValue = "customers";
			var ajaxurl = 'customersync.php',
			data =  {'action': clickBtnValue};
			$.post(ajaxurl, data, function (response) {
				log.value += response+'\n';
				log.scrollTop = log.scrollHeight;
				anim2.classList.remove("processingAnim");
				anim2.style.color = "white";
				if(response.slice(-8) == "created.") {
					dfrd.resolve();
				} else {
					clearAnim();
					return;
				}
			});
			return $.when(dfrd).done(function(){
			}).promise();
		}
	}

// set session variables
    $('#confirmbtn').click(function(){
        var clickBtnValue = "setSesVars";
        var ajaxurl = 'mainpage.php',
		data = {'action': clickBtnValue, 'erpuser': textfield1.value, 'erppw': textfield2.value, 'erpapiurl': textfield3.value, 'epagesapiurl': textfield4.value, 'epagestoken': textfield5.value};
        $.post(ajaxurl, data, function (response) {			
			confirmbutton.disabled = true; confirmbutton.style.backgroundColor = "#c4c4c4";		
			erpuser = textfield1.value; erppw = textfield2.value; erpapiurl = textfield3.value; epagesapiurl = textfield4.value; epagestoken = textfield5.value;
			syncbuttons.style.filter = "grayscale(0%)"; syncbuttons.style.pointerEvents = "auto";
			textfield1.style.borderColor = "#63ff60"; textfield2.style.borderColor = "#63ff60"; textfield3.style.borderColor = "#63ff60"; textfield4.style.borderColor = "#63ff60"; textfield5.style.borderColor = "#63ff60";			
			log.value += 'Variables have been set.'+'\n';
			log.scrollTop = log.scrollHeight;			       
        });
    });
	
// clear session variables
    $('#clearbtn').click(function(){
        var clickBtnValue = "clearSesVars";
        var ajaxurl = 'mainpage.php',
        data =  {'action': clickBtnValue};
        $.post(ajaxurl, data, function (response) {          
			editCounter = 5;
			erpuser = ""; erppw = ""; erpapiurl = ""; epagesapiurl = ""; epagestoken = "";
			textfield1.value = ""; textfield2.value = ""; textfield3.value = ""; textfield4.value = ""; textfield5.value = "";
			syncbuttons.style.filter = "grayscale(100%)"; syncbuttons.style.pointerEvents = "none";
			confirmbutton.disabled = true; confirmbutton.style.backgroundColor = "#c4c4c4";
			textfield1.parentElement.classList.remove("FocusWithText"); textfield2.parentElement.classList.remove("FocusWithText"); textfield3.parentElement.classList.remove("FocusWithText"); textfield4.parentElement.classList.remove("FocusWithText"); textfield5.parentElement.classList.remove("FocusWithText");
			textfield1.style.color = "black"; textfield2.style.color = "black"; textfield3.style.color = "black"; textfield4.style.color = "black"; textfield5.style.color = "black";
			textfield1.style.backgroundColor = "white"; textfield2.style.backgroundColor = "white"; textfield3.style.backgroundColor = "white"; textfield4.style.backgroundColor = "white"; textfield5.style.backgroundColor = "white";
			textfield1.style.borderColor = "#555a93"; textfield2.style.borderColor = "#555a93"; textfield3.style.borderColor = "#555a93"; textfield4.style.borderColor = "#555a93"; textfield5.style.borderColor = "#555a93";
			textfield1.style.pointerEvents = "auto"; textfield2.style.pointerEvents = "auto"; textfield3.style.pointerEvents = "auto"; textfield4.style.pointerEvents = "auto"; textfield5.style.pointerEvents = "auto";
			textfield1.readOnly = false; textfield2.readOnly = false; textfield3.readOnly = false; textfield4.readOnly = false; textfield5.readOnly = false;
			editbutton1.value = "set"; editbutton2.value = "set"; editbutton3.value = "set"; editbutton4.value = "set"; editbutton5.value = "set";
			editbutton1.style.backgroundColor = "#c4c4c4"; editbutton2.style.backgroundColor = "#c4c4c4"; editbutton3.style.backgroundColor = "#c4c4c4"; editbutton4.style.backgroundColor = "#c4c4c4"; editbutton5.style.backgroundColor = "#c4c4c4";
			editbutton1.disabled = true; editbutton2.disabled = true; editbutton3.disabled = true; editbutton4.disabled = true; editbutton5.disabled = true;		
			log.value += 'Variables have been cleared.'+'\n';
			log.scrollTop = log.scrollHeight;    
        });
    });
	
// clear animation
	function clearAnim() {
		overlay.style.opacity = "0";
		syncbuttons.style.pointerEvents = "auto";
		anim1.classList.remove("processingAnim");
		anim1.style.color = "white";
		anim2.classList.remove("processingAnim");
		anim2.style.color = "white";
		anim3.classList.remove("processingAnim");
		anim3.style.color = "white";
		fillables.style.webkitFilter = "grayscale(0%)"; fillables.style.pointerEvents = "auto";
	}
}