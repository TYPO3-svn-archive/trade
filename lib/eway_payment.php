<?php


	if ($GLOBALS['_SERVER']['SERVER_PORT'] != "443") {
		$warning="<FONT COLOR=RED><B>WARNING:</B></FONT>This transaction was <b>INSECURE</b>\n<br/>";
	}
	//debug($this->piVars);
	// map fields here
	$names=explode(' ',trim($this->user['name']));
	$customerfirstname=$names[0];
	$customerlastname=$names[count($names)-1];
	$customeremail=$this->user["email"];
	$customeraddress=$this->user["address"]." ".$this->user["fax"];
	$customerpostcode=$this->user["zip"];
	$country=$this->user["country"];
	$invoicedescription=$this->conf['invoiceDescription'];
	$cardname=$this->payment["card_name"];
	if (strlen($cardname)<1) {
		$this->errors[]='Invalid card holder name';
		return; 
	}
	$expirymonth=$this->payment["card_exp_month"];
	if ($expirymonth<1||$expirymonth>12) {
		$this->errors[]='Invalid card expiry month';
		return; 
	}
	$expiryyear=$this->payment["card_exp_year"];
	if ($expiryyear<2008||$expiryyear>2090) {
		$this->errors[]='Invalid card expiry year';
		return; 
	}
	$totalamount=round($this->order['price_total_tax']*100);
	$cardnumber=$this->piVars["card_number"];
	if (strlen($cardnumber)<10) {
		$this->errors[]='Invalid card number';
		return; 
	}
	$cardcvn=$this->piVars["card_cvn"];
	$merchantnumber=$this->conf["payment."][$this->payment['method'].'.']['merchantCode'];
	$orderUid=$this->order['tracking_code'];
	$gateway=REAL_TIME;
	if ($this->conf['requireCVN']==1) {
		$gateway=REAL_TIME_CVN;
		if (strlen($cardcvn)<3) {
			$this->errors[]='Invalid CVN number';
			return; 
		}
	//	debug('cvn');
	}
	else if ($this->conf['useGeoIP']==1) {
		$gateway=GEO_IP_ANTI_FRAUD;
	//	debug('gipip');
	} else {
	//	debug('std');	
	}
	$useTest=1;
	if($this->conf["payment."][$this->payment['method'].'.']["useTestGateway"] == "1") {
		$useTest=0;
		//debug('test');
		$merchantnumber='87654321';
		$cardnumber="4444333322221111";
		$cardcvn="321";
	//			debug($merchantnumber);
	} else {
		//debug('notest');
	}
	//debug('amount:'.$totalamount);
	//debug('usetest:'.$useTest);
	require_once("EwayPaymentLive.php");
	// input customerID,  method (REAL_TIME, REAL_TIME_CVN, GEO_IP_ANTI_FRAUD) and liveGateway or not
	$eway = new EwayPaymentLive($merchantnumber, $gateway, $useTest);
	$eway->setTransactionData("TotalAmount", $totalamount); //mandatory field
	$eway->setTransactionData("CustomerFirstName",$customerfirstname );
	$eway->setTransactionData("CustomerLastName", $customerlastname);
	$eway->setTransactionData("CustomerEmail", $customeremail);
	$eway->setTransactionData("CustomerAddress", $customeraddress);
	$eway->setTransactionData("CustomerPostcode", $customerpostcode);
	$eway->setTransactionData("CustomerInvoiceDescription", $invoicedescription);
	$eway->setTransactionData("CustomerInvoiceRef", $orderUid);
	$eway->setTransactionData("CardHoldersName", $cardname); //mandatory field
	$eway->setTransactionData("CardNumber", $cardnumber); //mandatory field
	$eway->setTransactionData("CVN", $cardcvn);
	$eway->setTransactionData("CardExpiryMonth", $expirymonth); //mandatory field
	$eway->setTransactionData("CardExpiryYear", $expiryyear); //mandatory field
	$eway->setTransactionData("TrxnNumber", $orderUid);
	$eway->setTransactionData("Option1", $customeremail);
	$eway->setTransactionData("Option2", $customeremail);
	$eway->setTransactionData("Option3", $customeremail);
//	debug($eway->$myTransactionData);
	
	//for GEO_IP_ANTI_FRAUD
	$eway->setTransactionData("CustomerIPAddress", $eway->getVisitorIP()); //mandatory field when using Geo-IP Anti-Fraud
	$eway->setTransactionData("CustomerBillingCountry", $country); //mandatory field when using Geo-IP Anti-Fraud
	//special preferences for php Curl
	$eway->setCurlPreferences(CURLOPT_SSL_VERIFYPEER, 0);  //pass a long that is set to a zero value to stop curl from verifying the peer's certificate 
	//$eway->setCurlPreferences(CURLOPT_CAINFO, "/usr/share/ssl/certs/my.cert.crt"); //Pass a filename of a file holding one or more certificates to verify the peer with. This only makes sense when used in combination with the CURLOPT_SSL_VERIFYPEER option. 
	//$eway->setCurlPreferences(CURLOPT_CAPATH, "/usr/share/ssl/certs/my.cert.path");
	//$eway->setCurlPreferences(CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //use CURL proxy, for example godaddy.com hosting requires it
	//$eway->setCurlPreferences(CURLOPT_PROXY, "http://proxy.shr.secureserver.net:3128"); //use CURL proxy, for example godaddy.com hosting requires it
	
	$ewayResponseFields = $eway->doPayment();
	
	// map response 
	$status=$ewayResponseFields["EWAYTRXNSTATUS"] ;
       $error=$ewayResponseFields["EWAYTRXNERROR"] ;
       $reference=$ewayResponseFields["EWAYTRXNREFERENCE"] ;
       $number=$ewayResponseFields["EWAYTRXNNUMBER"];
       $testTransaction='';
   if ($this->conf["payment."][$this->payment['method'].'.']["useTestGateway"] == "1") {
		$testTransaction='TEST TRANSACTION<br/>';
	} 
	
	// feedback for controller/rendering
	if($status=="True"||($this->conf["payment."][$this->payment['method'].'.']["useTestGateway"] == "1" && strpos($error,'Do Not Honour')>0)){
		$this->renderer->markerArray["TRANSACTION_DETAILS"]=$testTransaction.'Success<br>Transaction Details<br/>number:'.$number.' <br/>reference: '.$reference;
	} else if($status=="False"){
		$this->errors[]=$testTransaction.'Payment processing failed.<br/>'.$error;
	} 
	//debug(array($ewayResponseFields));
	
	//exit;

?>
