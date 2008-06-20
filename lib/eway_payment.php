<?php


	if ($GLOBALS['_SERVER']['SERVER_PORT'] != "443") {
		$warning="<FONT COLOR=RED><B>WARNING:</B></FONT>This transaction was <b>INSECURE</b>\n<br/>";
	}
	$customerfirstname=strtok($this->user["name"]," ");
	$customerlastname="";
	$customeremail=$this->user["email"];
	$customeraddress=$this->user["address"]." ".$this->user["fax"];
	$customerpostcode=$this->user["zip"];
	$invoicedescription=$this->conf['invoiceDescription'];
	$cardname=$this->payment["card_name"];
	$expirymonth=$this->payment["card_exp_month"];
	$expiryyear=$this->payment["card_exp_year"];
	$totalamount=round($this->order['price_total_tax']*100);
	$cardnumber=$this->piVars["card_number"];
	$merchantnumber=$this->conf["payment."][$this->payment['method'].'.']['merchantCode'];
	$txref=$orderUid;

	if($this->conf["payment."][$this->payment['method'].'.']["useTestGateway"] == "1") {
		define (GATEWAY_URL, "https://www.eway.com.au/gateway/xmltest/TestPage.asp");
		$merchantnumber="12345678900000000000";
		$cardnumber="4111111111111111";
		$cardname="fred j nerk";
		$expirymonth="12";
		$expiryyear="07";
		$warning="TEST TRANSACTION</br>";
	}else {
		define (GATEWAY_URL, "https://www.eway.com.au/gateway/xmlpayment.asp");
	}
	include "epayment.php";

	$test_transaction = new electronic_payment ("$merchantnumber", "$totalamount", 
 				"$customerfirstname", "$customerlastname",
                            "$customeremail", "$customeraddress", 
                            "$customerpostcode", "$invoicedescription", "$txref", "$cardname", 
                            "$cardnumber", "$expirymonth", "$expiryyear");   
	$error=$test_transaction->trxn_error();
	$status=$test_transaction->trxn_status();
	$number=$test_transaction->trxn_number();
	$reference=$test_transaction->trxn_reference();
	
	
	
	if ($this->conf["payment."][$this->payment['method'].'.']["useTestGateway"] == "1") {
		$this->renderer->markerArray["TRANSACTION_DETAILS"]='TEST TRANSACTION<br/> status:'.$error.' <br/>number:'.$number.' <br/>reference: '.$reference;
	}
	// if failed live processing, throw back to previous screen with error message
	//else if (strlen($error)>0) {
	//else if (substr($error,0,2)!="08") {
	else if ($status!="True") {
		$this->errors[]='Payment processing failed.<br/>'.$error;
	} else {
		$this->renderer->markerArray["TRANSACTION_DETAILS"]=$warning.'Transaction Details<BR/>Code : '.$number.'<BR/>';
//.'Reference : '.$reference.'<BR/>';
	}
?>
