<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Steve Ryan, Roger Bunyan ()   
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Trade ' for the 'trade' extension.
 *
 * @author	Steve Ryan <stever@syntithenai.com>, Roger Bunyan <>
 */
require_once(PATH_tslib.'class.tslib_pibase.php');
// all methods used as static
require_once (t3lib_extMgm::extPath('trade').'/lib/class.tx_trade_pricecalc.php');
// all methods used as static
require_once (t3lib_extMgm::extPath('trade').'/lib/class.tx_trade_div.php');
// create instance and set this as parent
require_once (t3lib_extMgm::extPath('trade').'/lib/class.tx_trade_render.php');
require_once (t3lib_extMgm::extPath('trade').'/lib/credit_card.php');
require_once (t3lib_extMgm::extPath('trade').'/lib/email_message.php');
require_once (t3lib_extMgm::extPath('trade').'/lib/smtp.php');

class tx_trade_pi1 extends tslib_pibase {
	var $prefixId = 'tx_trade_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_trade_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'trade';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	// library instances
		var $renderer;
		var $TSParser;			
		var $LANG;

	// user session variables
		var $basket;			// an array of product records with array key basket_qty set
		var $order;				
		var $user;				// array corresponding to fe_users table
		var $payment;			// array containing payment selections and details
		var $shipping;			// user shipping details - array corresponding to extended fields in fe_users table
	// storage for database results between controller doing query and renderer-> init creating markers
		var $record;		// single view		
		var $list;			// search
		var $hideList=false;
	// control variables
		var $cmd='list';  	 	// default action for this request
			//set by post variables config or default action is list of items on this page
		var $renderWith;		// template section to render		
		var $template='';  		// contains content of main template file
		var $listType;			// derived from cmd where cmd begins with list to select custom list configuration
		var $doReset=false;		// set by controller if a complete session reset is required
		var $prevCmd;
	// feedback variables to pass error/warning feedback from controller to renderer
		var $errors;				
		var $messages;				
	// form constants for javascript
		var $formName='myform';
		var $searchButtonName='do_search';
		var $saveUserButtonName='do_save_user';
		var $finaliseButtonName='tx_trade_pi1[buttons][thanks]';
		

	/**
	 * Main controller, entry point to plugin execution
	 cmd is the main switching variable
	 cmd is initially set from piVars[cmd] but can be changed in the following circumstances
	 - any key is set for piVars[buttons], the array key is used as the cmd. This provides for different actions when different form buttons are pressed.
	 - cmd=list_??? -> list
	 - confirm/thanks/checkout go through the basket checkout logic
	 
	 renderWith is used to switch template sections. As a rule it is the same as cmd
	 
	 */
	function main($content,$conf)	{
//phpinfo();
//exit;
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		//setcookie('tx_trade_pi_testingval','trade test value',time()+60*60*24)	;
		//debug(array($this->conf,$this->renderWith,$this->listType));
		$attachments=array();
		$this->init();
		// process incoming information into valid session
		$this->processPostData();
		$this->validate();
		if (sizeof($this->errors)>0) {
			// if javascript was used to modify the cmd parameter, use prevcmd
			// (the cmd when the form was originally rendered)
			$this->cmd=$this->prevCmd;
			$this->renderWith=$this->cmd;   
		} else {
			// returning from external processing ?
			// assume payment completed successfully and this is a 'callback'
			if ($this->piVars['external_payment_complete']>0) {
				//debug(array('external payment callback',tx_trade_div::getCurrentOrder()));
				// check session for incomplete order cookie
				$closed=false;
				if (tx_trade_div::getCurrentOrder()>0) {
					//debug(array('currentorder',tx_trade_div::getCurrentOrder()));
					// load the order
					$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_orders','uid='.mysql_escape_string(tx_trade_div::getCurrentOrder()),'','','');
					if ($this->order=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						//debug(array('loaded currentorder',$this->order));
						if ($this->order['status']==1) {
							$this->cmd='alreadyclosedorder';   
							$this->renderWith='alreadyclosedorder'; 
							$closed=true;
						} else {
							// close the order and mail adm/cust
							$this->closeOrder();
							$closed=true;
							$this->cmd='thanks';   
							$this->renderWith='thanks'; 
							if (trim(strlen($this->renderer->markerArray["TRANSACTION_DETAILS"]))==0) $this->renderer->markerArray["TRANSACTION_DETAILS"]='&nbsp;';
							$this->renderer->markerArray["ORDER_TRACKING_LINK"]='http://'.$GLOBALS['_SERVER']['SERVER_NAME'].''.$GLOBALS['_SERVER']['PHP_SELF'].'?id='.$this->PIDS['order_history_list']['uid'].'&user='.$this->user['username'].'&pass='.$this->user['password'].'&logintype=login&pid='.$this->PIDS['userstorage']['uid'].'&tx_trade_pi1[cmd]=order_history_list';
						}
					}
				} 
				if (!$closed) {      
					//debug('faileclose the order and mail adm/cust');
					
					$this->cmd='list';
					$this->renderWith='list';
					$this->listType='';
					$this->processProductSearch();
					//debug($this->list);
				}
			}
			// catch final order processing
			else if ($this->piVars['finalise_checkout']==1) {
				//debug(array($this->user,$this->order,$this->basket));
				if (count($this->basket)==0) {
					//debug(array('lost order'));
					$this->cmd='lostorder';
					$this->renderWith='lostorder';
					$this->errors=array();
					$this->messages=array();
				} else {
					//debug(array('start finalise'));
					// initially the order is saved as hidden and deleted with status=0
					$this->order['hidden']='1';
					$this->order['deleted']='1';
					$this->processSaveOrderNoUser();
					//debug('preset cookie');
					tx_trade_div::setCurrentOrder($this->order['uid']);
					//debug('postset cookie');
					//debug(array('order saved'));
					// if there are any problems, the handle script should add to $this->errors[]
					// it is also possible to add markers to $this->renderer->markerArray 
					// and potentially even override $this->cmd which will cause 
					// rendering of a matching template section
		
					// another possibility is that the payment script renders and exits without returning here
						
					// the eway script returns errors on fail
					// the paypal script renders a paypal submit form and exits. Closure of an order 
					// requires returning to this script with tx_trade_pi1[external_payment_complete]=1
		
					if (trim(strlen($this->renderer->markerArray["TRANSACTION_DETAILS"]))==0) {
						$this->renderer->markerArray["TRANSACTION_DETAILS"]='&nbsp;';
					}
					$this->renderer->markerArray["ORDER_TRACKING_LINK"]='http://'.$GLOBALS['_SERVER']['SERVER_NAME'].''.$GLOBALS['_SERVER']['PHP_SELF'].'?id='.$this->PIDS['order_history_list']['uid'].'&user='.$this->user['username'].'&pass='.$this->user['password'].'&logintype=login&pid='.$this->PIDS['userstorage']['uid'].'&tx_trade_pi1[cmd]=order_history_list';
					if (strlen($this->conf['payment.'][$this->payment['method'].'.']['handleScript'])>0) {
						//debug(array('do payment processing',$basket['totalPrice']));
						//debug(array('start handle script',$this->conf['payment.'][$this->payment['method'].'.']['handleScript']));
						require(t3lib_div::getFileAbsFileName(($this->conf['payment.'][$this->payment['method'].'.']['handleScript'])));
						//debug(array('done handle script'));
					}
					//debug(array('finalise checkout',$this->piVars,$this->user,$this->order,$this->basket,$this->payment,$this->shipping));
					// if there were errors, stay at this stage in the process
					//debug(array('errors',$this->errors));
					if (sizeof($this->errors)>0) {  
						//debug(array('errors after payment script'));	
						// if javascript was used to modify the cmd parameter, use prevcmd
						// (the cmd when the form was originally rendered)
						$this->cmd=$this->prevCmd;         
						$this->renderWith=$this->cmd;   
					} else {
						//debug(array('read to close order'));
						$this->closeOrder();
						$this->cmd='thanks';
						$this->renderWith=$this->cmd;			
					}
				}
				//debug(array($this->cmd,$this->renderWith));
		
			} else {
				// test for all possible activities and take appropriate actions eg load records, ...
				$this->processUserInput();
		
				// if there were errors, stay at this stage in the process
				if (sizeof($this->errors)>0) {
					// if javascript was used to modify the cmd parameter, use prevcmd
					// (the cmd when the form was originally rendered)
					$this->cmd=$this->prevCmd;
					$this->renderWith=$this->cmd;
				}
				//debug(array($this->cmd,$this->renderWith));
				// if cmd is checkout then rewrite cmd,renderWith to show the appropriate checkout stage	
				$doCmd='';
				// dont allow direct calls to thanks template
				// force testing through TS config for checkout
				if ($this->cmd=='confirm') $this->cmd='checkout';
				if ($this->cmd=='thanks') $this->cmd='checkout';
				if ($this->cmd=='checkout') { 
					$currentCmd='basket';
					while ($doCmd=='') {  
						$cV=$this->conf['checkout.'][$currentCmd.'.'];
						$testResult=false;
						// set testResult
						eval($cV['condition']);
						//debug(array($currentCmd,$cV));
						if (!$testResult) {
							// set cmd
							$renderTemplate=$cV['templateSubpart'];
							$doCmd=$currentCmd;
						} else {
							$currentCmd=$cV['next'];
						}
					}
					$this->cmd=$currentCmd;
					$this->renderWith=$renderTemplate;
				}
			}
		}	
		//debug(array('after basket',$this->cmd,$this->renderWith));
		tx_trade_div::setSession('user',$this->user);
		
		// FINALLY RENDERING BASED ON renderWith
		$this->renderer->init($this);
		$content=$this->renderer->renderSection($this->renderWith);
		
		if ($this->doReset) {
			$this->processReset();
		}
		return $this->pi_wrapInBaseClass($content); 
	} 

	 function closeOrder() {
		//debug('clsoing order'.$this->order['uid']);
		if ($this->order['uid']>0) { 
			//debug(array($this->order));
			 $this->order['deleted']=0;
			 $this->order['hidden']=0;
			 $this->order['status']=1;
			 
			$comma='';
			if (strlen(trim($this->user['usergroup']))>0) $comma=',';
			foreach ($this->basket as $k => $v) {
			 	if ($v['tx_tradeusergroupasproduct_usergrouponpurchase']>0) {
			 		$this->user['usergroup']=$this->user['usergroup'].$comma.$v['tx_tradeusergroupasproduct_usergrouponpurchase'];
			 		$comma=',';
			 	}
			 }
			 	
			 if ($this->conf['customerUserGroupWhenOrderClosed']>0) {
				 $prepend='';
				 if (strlen(trim($this->user['usergroup']))>0) $prepend= $this->user['usergroup'].',';
				 $this->user['usergroup']=$prepend.$this->conf['customerUserGroupWhenOrderClosed'];
			 }
			 $this->processSaveOrder();
			 // send email confirmation of order to purchaser and admin
			
			 if (trim(strlen($this->renderer->markerArray["TRANSACTION_DETAILS"]))==0) {
				 $this->renderer->markerArray["TRANSACTION_DETAILS"]='&nbsp;';
			 }
			 $this->renderer->markerArray["ORDER_TRACKING_LINK"]='http://'.$GLOBALS['_SERVER']['SERVER_NAME'].''.$GLOBALS['_SERVER']['PHP_SELF'].'?id='.$this->PIDS['order_history_list']['uid'].'&user='.$this->user['username'].'&pass='.$this->user['password'].'&logintype=login&pid='.$this->PIDS['userstorage']['uid'].'&tx_trade_pi1[cmd]=order_history_list';
			$message=$this->renderer->renderSectionNoWrap('EMAIL_CHECKOUT');
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\n";
			if ($this->conf['plainTextEmails']==1) {
				$headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
			} else {
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
			}
			$headers .= 'From: '.$this->conf['adminEmail'] . "\n";
			$headers .='Reply-To: '.$this->conf['adminEmail'] . "\n";
			$headers .='Return-Path: '.$this->conf['adminEmail'] ."\n";
			//mail($this->user['email'],$this->conf['confirmEmailTitle'],$message,$headers);
			$attachments=array();
			if ($this->conf['emailDataSheet']==1) {
				foreach ($this->basket as $k => $v) {
					$datasheets=$v['datasheet'];
					foreach (explode(',',$datasheets) as $k => $datasheet) {
						$datasheet='uploads/tx_trade/'.$datasheet;
						if (strlen(trim($datasheet))>0&&file_exists($datasheet)&&is_readable($datasheet)) {
							$attachments[$datasheet]=$datasheet;	
						}
					}
				}
			}
			$this->sendMessageWithAttachments($this->conf['adminEmail'],'',$this->user['email'],'',$this->conf['confirmEmailTitle'],$message,$attachments);
			mail($this->conf['adminEmail'],$this->conf['confirmEmailAdminTitle'],$message,$headers);
			 // remove order from session after checkout completed
			 $this->doReset=true;
			 tx_trade_div::removeCurrentOrder();
		 }
	 }
	 
	/**
	 *   Initialisation of various resources
	 */	
	function init()  {
		// markers are added to the renderer markerArray variable during user input
		// processing and init so make the instance available first
		$this->renderer=t3lib_div::makeInstance('tx_trade_render');
		$this->prevCmd=t3lib_div::GPvar('prevcmd');
		session_start();
		// enable database debug
		//if ($this->conf['debug']==1) $GLOBALS['TYPO3_DB']->debugOutput=true;
		$this->includeFFConf();
		//debug($this->conf);
		// grab basket from session
		$this->basket=tx_trade_div::getSession('basket');

		// set PIDS
		$cmdList=$this->conf['cmdList'];
		//debug($this->conf['PIDS.']);
		foreach (explode(",",$cmdList) as $pK => $pV) {
			if ($this->conf['PIDS.'][$pV]>0) {
				$this->PIDS[$pV]['uid']=$this->conf['PIDS.'][$pV];
			} else {
				$this->PIDS[$pV]['uid']=$GLOBALS['TSFE']->id;
			}
			$this->PIDS[$pV]['link']='index.php?id='.$this->PIDS[$pV]['uid'].'&tx_trade_pi1[cmd]='.$pV;
		}
		//debug($this->PIDS);
		t3lib_div::loadTCA('fe_users');
		tslib_fe::includeTCA();
		require_once ('typo3/sysext/lang/lang.php');
		$this->LANG = t3lib_div::makeInstance('language'); 
		$this->LANG->init($BE_USER->uc['lang']);
		
		// cache order status picklist values
		$tmoStat=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title','tx_trade_order_status',"",'','','','');
		foreach ($tmoStat as $oK => $oV) {
			$this->orderStatus[$oV['uid']]=$oV['title'];	
		}
		
		// SET THIS->CMD AS A RESULT OF 
		// if a pivars cmd is posted, it is default
		if (strlen($this->piVars['cmd'])>0) $this->cmd=$this->piVars['cmd'];
		// hidden form cmd takes precedence
		if (strlen(t3lib_div::GPvar('cmd'))>0) $this->cmd=t3lib_div::GPvar('cmd');
		//debug($this->piVars['buttons']);
		if (sizeof($this->piVars['buttons'])>0)  {
			foreach ($this->piVars['buttons'] as $bK => $bV) {
				$this->cmd=$bK;
			} 
		}
		$this->cmd=strtolower($this->cmd);
		// by default, use the cmd as the name of the template subpart
		$this->renderWith=$this->cmd;
		
		// if cmd starts with list, extract list type
		if (strpos("X".$this->cmd,'list')==1) {
			$this->listType=substr($this->cmd,5);
			if (strlen(trim($this->listType))==0) $this->listType='default';
			// ensure that this list type exists
			if (strlen(trim($this->conf['lists.'][$this->listType.'.']['templateSubpart']))==0) {
				$this->listType='default';
			} 
			$this->renderWith=$this->conf['lists.'][$this->listType.'.']['templateSubpart'];
		}
		// is this a valid command
		// list, checkout or all individual stages of checkout are valid
		$validCmds=explode(',',$this->conf['validCmds']);
		foreach ($this->conf['checkout.'] as $cK => $cV) {
			$validCmds[]=substr($cK,0,strlen($cK)-1);
		}
		if (!is_array($this->conf['lists.'])) {
			$this->conf['lists.']=array();
		}
		foreach ($this->conf['lists.'] as $cK => $cV) {
			$validCmds[]='list_'.substr($cK,0,strlen($cK)-1);
		}
		if (!in_array($this->cmd,$validCmds)||(strlen(t3lib_div::GPvar('prevcmd'))>0&&!in_array(t3lib_div::GPvar('prevcmd'),$validCmds))) {
			$this->cmd='list_default';
			$this->listType='default';
			$this->renderWith=$this->conf['lists.']['default']['templateSubpart'];
		}	
	}
	
	
	


	function sendMessageWithAttachments($from_address,$from_name,$to_address,$to_name,$subject,$text_message,$attachments) {
		$reply_name=$from_name;
		$reply_address=$from_address;
		$reply_address=$from_address;
		$error_delivery_name=$from_name;
		$error_delivery_address=$from_address;
		
		$email_message=new email_message_class;
		$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
		$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
		$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);
		if (strlen(trim($this->conf['bccOrderEmail']))>0) {
			$email_message->SetEncodedEmailHeader("Bcc",trim($this->conf['bccOrderEmail']),'Admin User');
		}
		$email_message->SetHeader("Sender",$from_address);
		if(defined("PHP_OS")
		&& strcmp(substr(PHP_OS,0,3),"WIN"))
			$email_message->SetHeader("Return-Path",$error_delivery_address);
	
		$email_message->SetEncodedHeader("Subject",$subject);
		
		$email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message));
		foreach ($attachments as $k=>$v) {
			if (file_exists($v)&&is_file($v)&&is_readable($v)) {
				$image_attachment=array(
				"FileName"=>$v,
				"Content-Type"=>"automatic/name",
				"Disposition"=>"attachment"
				);
				$email_message->AddFilePart($image_attachment);
			}
		}
		$error=$email_message->Send();
	}
	
	/**
	 *   Merge flexform configuration with typoscript template configuration
	 */	
	function includeFFConf() {
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
		$this->lConf = array(); // Setup our storage array...
		// Assign the flexform data to a local variable for easier access
		$piFlexForm = $this->cObj->data['pi_flexform'];
		// Traverse the entire array based on the language...
		// and assign each configuration option to $this->lConf array...
		foreach ( $piFlexForm['data'] as $sheet => $data )
			foreach ( $data as $lang => $value )
				foreach ( $value as $key => $val )
					$this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);	
		// now merge with existing conf values (and check validity)
		// PRODUCT AND CATEGORY PIDS
		if (strlen(trim($this->lConf['record_storage']))>0) {
			$this->conf['PIDS']['productstorage']=$this->lConf['record_storage'];
		}
		// SHOP OWNER EMAIL
		if (strlen(trim($this->lConf['shop_owner_email']))>0) {
			$this->conf['adminEmail']=$this->lConf['shop_owner_email'];
		} 
		if (strlen(trim($this->conf['adminEmail']))==0) debug('!!!Configuration Error. You must set a shop owner email address.');
		// SHOP OWNER DETAILS
		if (strlen(trim($this->lConf['shop_owner_details']))>0) {
			$this->conf['shopOwnerDetails']=$this->lConf['shop_owner_details'];
		} 
		if (strlen(trim($this->conf['shopOwnerDetails']))==0) debug('!!!Configuration Error. You must provide shop owner details.');
		// EWAY 
		if ($this->conf['payment.']['disableFlexForm']!=1) {
			if (tx_trade_div::isBitwiseOptionEnabled(2,$this->lConf['payment_options'])) {
				if (strlen(trim($this->lConf['eway_merchant_code']))>0) {
					$this->conf['payment.']['10.']['merchantCode']=$this->lConf['eway_merchant_code'];
				} 
				if (strlen(trim($this->conf['payment.']['10.']['merchantCode']))==0) debug('!!!Configuration Error. You must set an eway merchant code if  are using the eway payment system.');
				
				if ($this->lConf['eway_test_mode']==1) {
					$this->conf['payment.']['10.']['useTestGateway']=1;
				} 
			} else {
			  unset ($this->conf['payment.']['10.'])	;
			}
			if (tx_trade_div::isBitwiseOptionEnabled(1,$this->lConf['payment_options'])) {
				// PAYPAL
				if (strlen(trim($this->lConf['paypal_email']))>0) {
					$this->conf['payment.']['20.']['paypalEmail']=$this->lConf['paypal_email'];
				} 
				if (strlen(trim($this->conf['payment.']['20.']['paypalEmail']))==0) debug('!!!Configuration Error. You must set a paypal email address if you are using the paypal payment system.');
			} else {
			  unset ($this->conf['payment.']['20.'])	;
			}
			if (tx_trade_div::isBitwiseOptionEnabled(3,$this->lConf['payment_options'])) {
			
			} else {
			  unset ($this->conf['payment.']['40.'])	;
			}
		}
		
		// merge direct conf settings
		$this->TSParser=t3lib_div::makeInstance('t3lib_TSparser');
		$this->TSParser->parse($this->lConf['conf_override']);
		$this->conf=t3lib_div::array_merge($this->conf,$this->TSParser->setup);
		// debug
		if (strlen(trim($this->lConf['enable_debug']))>0) {
			//$this->conf['debug']=$this->lConf['enable_debug'];
		} 
	}
	
	/***********************************************************************
	 * Controller Support Methods
	 **********************************************************************/

	/**
	 * Scan post data for appropriate flags and take relevant actions
	 */	
	function processUserInput() {		
		$this->processAddToBasket();
		
		// do search
		$list=array();
		//debug($this->listType);
		if (strlen($this->listType)>0) {
			$this->processProductSearch();
		}
		
		// ensure basket stays on same page when values are updated
		if (t3lib_div::GPvar('extrainfo')=='approvebasket') {
			 $this->user['basket_approved']=1;
		}
		
		// STEVE REMOVED CARD PROCESSING 1 HERE
		
		// quick reset for testing
		if ($GLOBALS['_GET']['cmd']=='reset') {
			$this->processReset();
		}
		
		// order history list
		if ($this->cmd=='order_history_list'&&$GLOBALS['TSFE']->fe_user->user['uid']>0) {
			$list=array();
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_orders',' feusers_uid='.mysql_escape_string($GLOBALS['TSFE']->fe_user->user['uid']),'','crdate desc','');  
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$list[]=$row;
			}
			//debug(array('ordre history list',$list));
			$this->list=$list;
		}
		
		// order history single view
		if ($this->cmd=='order_history_single'&&$this->piVars['order_uid']>0&&$GLOBALS['TSFE']->fe_user->user['uid']>0) {
			$list=array();
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_orders',' uid='.$this->piVars['order_uid'].' and  feusers_uid='.mysql_escape_string($GLOBALS['TSFE']->fe_user->user['uid']),'','crdate desc','');  
			$this->order=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); 
			//debug(array('ordre history list',$this->order));
		}
		
		// COPY ADDRESS TO SHIPPING
		if ($this->cmd=='copy_address_to_shipping') {
			$this->copyAddressToShipping();
		}
		
		// load a record for single view
		if ($this->piVars['uid']>0) {
			$this->renderer->markerArray['PRODUCT_SINGLE_VIEW']=$this->processLoadProduct();
		} else {
			$this->renderer->markerArray['PRODUCT_SINGLE_VIEW']=' ';
		}
		
	}
	
	/**
	 * Map data in the user fields to the shipping fields
	 */	
	function copyAddressToShipping() {
		$this->user['tx_trade_shipping_name']=$this->user['name'];
		$this->user['tx_trade_shipping_address']=$this->user['address'];
		$this->user['tx_trade_shipping_city']=$this->user['city'];
		$this->user['tx_trade_shipping_zip']=$this->user['zip'];
		$this->user['tx_trade_shipping_zone']=$this->user['tx_trade_state'];
		$this->user['tx_trade_shipping_country']=$this->user['country'];
		// force renderer back to previous editing page
		$this->errors[]=' ';
		// save updates
		tx_trade_div::setSession('user',$this->user);	
	}
	
	/**
	 * Load a product record and associated categories, then render it and store in this->renderer->markerArray
	 */	
	function processLoadProduct () {
		// load details
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_products','uid='.mysql_escape_string($this->piVars['uid']).' '.$this->getProductPIDQuery(),'','title ASC','');
		if ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// increment view counter
			$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_trade_products','uid='.mysql_escape_string($this->piVars['uid']),array('viewcount'=>$row['viewcount']+1));
		
			// get qty from session
			if (!is_array($this->basket)) $this->basket=array();
			$row['basket_qty']=$this->basket[$row['uid']]['basket_qty'];
			$markerArray=$this->getProductMarkers($row);
			if ($row['category_uid']>0) {
				$res2=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_categories','uid='.mysql_escape_string($row['category_uid']),'','title ASC','');
				if ($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
					$markerArray=t3lib_div::array_merge($this->getCategoryMarkers($row2),$markerArray);
				} else {
					$markerArray=t3lib_div::array_merge($this->getEmptyCategoryMarkers(),$markerArray);
				}
			} else {
				$markerArray=t3lib_div::array_merge($this->getEmptyCategoryMarkers(),$markerArray);
			}
			//debug(array($markerArray));	
			$this->renderer->markerArray=t3lib_div::array_merge($markerArray,$this->renderer->markerArray);
			// render
			$content=$this->renderer->init($this);
			$content=$this->renderer->renderComponent('ITEM_SINGLE_DISPLAY_COMPONENT');
			$content=$this->cObj->substituteMarkerArray($content,$this->renderer->markerArray,'###|###',true)  ;
			//debug(array($content,$this->renderer->markerArray));	
		} else {
			$content='No such product found';	
		}
		return $content;	
	}
	
	/**
	 * Capture post data into user,order,basket,search, shipping, payment
	 */	
	function processPostData() {
		// merge posted search params with session
		$this->search=tx_trade_div::getSession('search');
		if (!is_array($this->search)) $this->search=array();
		if (!is_array($this->piVars['search'])) $this->piVars['search']=array();
		$this->search=t3lib_div::array_merge($this->search,$this->piVars['search']);
		tx_trade_div::setSession('search',$this->search);
		
		// merge posted user details with session
		
		// clear user on logout
		if (t3lib_div::GPvar('logintype')=='logout') {
			$this->user=array();
			tx_trade_div::setSession('user',$this->user);
			unset($this->piVars['user']);
		} 
		// if login is submitted, dont catch user details
		else if (t3lib_div::GPvar('login')=='login'&&is_array($GLOBALS['TSFE']->fe_user->user)) {
			$this->user=$GLOBALS['TSFE']->fe_user->user;
			tx_trade_div::setUserCookie($GLOBALS['TSFE']->fe_user->user['username']);
			// do lookup to get extra fields
			//$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_products','uid='.mysql_escape_string($this->piVars['uid']),'','title ASC','');
			// override purely from fe user
			$this->user=$GLOBALS['TSFE']->fe_user->user;
		} else if (t3lib_div::GPvar('login')=='login') {
			$this->errors[]="Login failure. Incorrect user or password details";	
		} else {
			// grab user from session
			$this->user=tx_trade_div::getSession('user');
			if (!is_array($this->user)) $this->user=array();
			if (!is_array($this->piVars['user'])) $this->piVars['user']=array();
			if (!is_array($GLOBALS['TSFE']->fe_user->user)) $GLOBALS['TSFE']->fe_user->user=array();
			// overwrite from this user
			$this->user=t3lib_div::array_merge($GLOBALS['TSFE']->fe_user->user,$this->user);
			$this->user['uc']='0';
			// overwrite with pivars
			$this->user=t3lib_div::array_merge($this->user,$this->piVars['user']);
		}
		//debug(array($this->piVars,$this->user));
		
		/*if (strlen(trim($this->user['username']))==0) {
			$this->user['username_from_cookie']=tx_trade_div::getUserCookie(); 
			//debug('set username from cookie '.$this->user['username_from_cookie']);
		} else  {
			$this->user['username_from_cookie']='';
		}*/
		if (strlen(trim(tx_trade_div::getUserCookie()))>0&&$GLOBALS['TSFE']->fe_user->user['uid']==0) {
			$GLOBALS['TYPO3_DB']->debugOutput=true;
			$prevUser=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('name,username','fe_users',"username like '%".mysql_escape_string(trim(tx_trade_div::getUserCookie()))."%'",'','',1,''); // TODO remove $this->user['username_from_cookie'])."'",'','',1,'');
			$content=$this->renderer->loadTemplate($this);
			$content=$this->renderer->renderComponent('REPEAT_VISITOR');
			//debug(array('prev user',$prevUser,tx_trade_div::getUserCookie()));
			$content=$this->cObj->substituteMarkerArray($content,array('NAME'=>$prevUser[0]['name']),'###|###',true)  ;
			$this->renderer->markerArray['REPEAT_VISITOR']=$content;
		} else {
			$content=$this->renderer->loadTemplate($this);
			$content=$this->renderer->renderComponent('NEW_VISITOR');
			//debug(array('prev user',$prevUser,tx_trade_div::getUserCookie()));
			$content=$this->cObj->substituteMarkerArray($content,array('NAME'=>$prevUser[0]['name']),'###|###',true)  ;
			$this->renderer->markerArray['REPEAT_VISITOR']=$content;
		}
		tx_trade_div::setSession('user',$this->user);
		//debug('username in session is '.$this->user['username']);
		
		// merge posted shipping details with session
		$this->shipping=tx_trade_div::getSession('shipping');
		$shippingChanged=false;
		if (!is_array($this->shipping)) $this->shipping=array();
		if (!is_array($this->piVars['shipping'])) $this->piVars['shipping']=array();
		if ($this->shipping['method']>0&&$this->shipping['method']!=$this->piVars['shipping']['method']) $shippingChanged=true;
		$this->shipping=t3lib_div::array_merge($this->shipping,$this->piVars['shipping']);
		// set default shipping method
		if (!($this->shipping['method'] > 0) || !is_array($this->conf['shipping.'][$this->shipping['method'].'.'])) {
			foreach ($this->conf['shipping.'] as $pK => $pV) {
				$this->shipping['method']=substr($pK,0,strlen($pK)-1);
				if ($this->shipping['method']>0) {
					break;
				} 
			}
		}
		
		// merge posted payment details with session
		$this->payment=tx_trade_div::getSession('payment');
		if (!is_array($this->payment)) $this->payment=array();
		if (!is_array($this->piVars['payment'])) $this->piVars['payment']=array();
		$this->payment=t3lib_div::array_merge($this->payment,$this->piVars['payment']);
		$excluded=explode(',',$this->conf['shipping.'][$this->shipping['method'].'.']['excludePayment']);
		// set default payment method if none set or shipping method has changed and curren is excluded
		if ($this->payment['method'] == 0 || ($shippingChanged && in_array($this->payment['method'],$excluded))) {
			foreach ($this->conf['payment.'] as $pK => $pV) {
				$this->payment['method']=substr($pK,0,strlen($pK)-1);
				if ($this->payment['method']>0&&(!in_array($this->payment['method'],$excluded))) break;
			}
		}
		
		tx_trade_div::setSession('shipping',$this->shipping);
		
		tx_trade_div::setSession('payment',$this->payment);
		// merge posted order details with session
		$this->order=tx_trade_div::getSession('order');
		if (!is_array($this->order)) $this->order=array();
		if (!is_array($this->piVars['order'])) $this->piVars['order']=array();
		$this->order=t3lib_div::array_merge($this->order,$this->piVars['order']);
		tx_trade_div::setSession('order',$this->order);
	}
	
	/**
	 * Iterate tx_trade_pi1_addtobasket_* post vars, looking up and adding products to basket as necessary.
	 */
	function processAddToBasket() {
		// add to basket
		reset($GLOBALS['_POST']);
		foreach ($GLOBALS['_POST'] as $pK => $pV) {
			// if it is an add to basket post var
			if (strpos('x'.$pK,'tx_trade_pi1_addtobasket_')==1) {
				$aK=substr($pK,25);
				$aV=$pV;
				if (strlen(trim($aV))==0||trim($aV)=='0') {
					unset($this->basket[$aK]);
					tx_trade_div::setSession('basket',$this->basket);
				} else if ($aV>0) {
					$aV=intval($aV);
					$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_products','uid='.mysql_escape_string($aK).' '.$this->getProductPIDQuery(),'','title ASC','');
					if ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						if (trim($aV)=='') $aV=1;
						$row['basket_qty']=$aV;
						$this->basket[$row['uid']]=$row;
						tx_trade_div::setSession('basket',$this->basket);
					} else {
						//$this->errors[]=$this->pi_getLL('basket_no_product');	
					}
				} else {
					$this->messages[]=$this->pi_getLL('basket_invalid_qty');	
				}
			}
		}	
	}
	
	/**
	 * Return a query part in  the format - and (pid=? or pid=?  ...)  
	 * Limits PIDS lookup to TSofFFconf OR general record storage PIDS OR this page 
	 */
	function getProductPIDQuery() {
		$where="";
		$pages=$this->PIDS['productstorage']['uid'];
		$storagePID=$GLOBALS["TSFE"]->getStorageSiterootPids();
		$storagePID=$storagePID['_STORAGE_PID'];
		$pids=array();
		if ($storagePID>0) {
			$pids[]=$storagePID;
		}
		if (strlen($pages)>0) {
			$pageArr=explode(',',$pages);
			foreach ($pageArr as $pK =>$pV) {
				$pids[]=$pV;
			}
		} 
		if (sizeof($pids)>0) {
			$where.=' and (';
			$count=0;
			foreach ($pids as $pK =>$pV) {
				if ($count>0) $where.=' or '; 
				$where.=' pid='.mysql_escape_string($pV);
				$count++;
			}
			$where.=' ) ';
		}
		return $where;
	}
	
	
	/**
	 * Execute search query based on $this->search and list configuration
	 */
	function processProductSearch() {
		$this->renderer->markerArray['SUBMIT_TO_PREVIOUS']='';
		$this->renderer->markerArray['SUBMIT_TO_NEXT']='';
		$where=' (1=1) ';
		// if pages are selected in the content starting point field, select products from those pages
		$where.=$this->getProductPIDQuery();
		// allow for extraWhere
		if (strlen(trim($this->conf['lists.'][$this->listType.'.']['extraWhere']))>0) {
			$where.=' and '.$this->conf['lists.'][$this->listType.'.']['extraWhere'].' ';
		} 
		if ($this->piVars['search']['category']&&$this->piVars['search']['category']>0) {
			$where.=' and find_in_set('.intval($this->piVars['search']['category']).',category_uid) '; 
		}
		// search criteria
		//$this->hideList=false;
		$criteria=$this->search;
		if ($this->conf['lists.'][$this->listType.'.']['search']==1) {
			//if (strlen($GLOBALS['_POST']['do_search'])>0) {
				if (strlen($criteria['text'])>0) {
					$where.=" and (title like '%".mysql_escape_string($criteria['text'])."%' or subheader like '%".mysql_escape_string($criteria['text'])."%' or description like '%".mysql_escape_string($criteria['text'])."%' or code like '%".mysql_escape_string($criteria['text'])."%')";
				}
			//} 
			/*else {
				// only render 'list is empty' when search button is pressed
				$list=array();
				$list[0]='no_list';
				$this->list=$list;
				return;
			}*/
		}
		
		// enable fields
		// fix for typo3 src v 4.1.1
		//.$where.=t3lib_pageSelect::enableFields('tx_trade_products');
		$where.=$GLOBALS['TSFE']->sys_page->enableFields("tx_trade_products", 0);
		
		//debug(array($where));
		$orderBy=$this->conf['lists.'][$this->listType.'.']['orderBy'];
		// get count results and generate next/prev buttons
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('count(uid) numrows','tx_trade_products',$where,'','',$this->conf['maxListRows']);  
		if ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$numrows=$row['numrows'];
		}
		$this->numrows=$numrows;
		$liststart=$GLOBALS['_POST']['liststart'];
		if ($liststart>0) {
			$this->renderer->markerArray['SUBMIT_TO_PREVIOUS']='<input type="submit"  value="'.$this->pi_getLL('previous').'" onClick="document.'.$this->formName.'.liststart.value='.($liststart-$this->conf['maxListRows']).';" />';
		}
		if ($numrows>($liststart+$this->conf['maxListRows'])) {
			$this->renderer->markerArray['SUBMIT_TO_NEXT']='<input type="submit"  value="'.$this->pi_getLL('next').'" onClick="document.'.$this->formName.'.liststart.value='.($liststart+$this->conf['maxListRows']).'" />';
		}
		if ($liststart>0) {
			$limit=$liststart.','.$this->conf['maxListRows'];
		} else {
			$limit='0,'.$this->conf['maxListRows'];
		}
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_products',$where,'',$orderBy,$limit);  
		//if ($this->conf['debug']==1) debug(array($where,$orderBy,$limit));
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$list[]=$row;
		}
		$this->list=$list;
	}
	
	/**
	 * Execute search query based on $this->search and list configuration
	 */
	function processSaveUser() {
		$saveFields=explode(",",'uid,pid,uc,'.$GLOBALS['TCA']['fe_users']['feInterface']['fe_admin_fieldList']);
		// limit save fields to TCA[fe_users][feInterface][fe_admin_fieldList]
		//debug($this->user);
		reset($this->user);
		foreach ($saveFields as $uK =>$uV) {
			if (strlen(trim($this->user[$uV]))>0) {
				$user[$uV]=$this->user[$uV];
			}
		}
		//debug($user);
		
		//$user['module_sys_dmail_category']=$this->conf['dmailCategory']; 
		$currentGroups=explode(',',$user['usergroup']);
		if (!in_array($user['usergroup'],$currentGroups)) $user['usergroup']=$user['usergroup'].','.$this->conf['customerUserGroup'];
		
		if ($user['pid']<=0) $user['pid']=$this->PIDS['userstorage']['uid'];
		$user['tstamp']=time(); 	
		unset($user['force_new_user']);
		if ($this->user['uid']>0) {
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users','uid='.mysql_escape_string($user['uid']),$user);
			if ($this->conf['debug']=='1') $this->messages['saveuser']=$this->pi_getLL('save_successful');
		} else {
			$user['crdate']=time();
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users',$user);
			$uid=$GLOBALS['TYPO3_DB']->sql_insert_id();
			if ($uid>0) {
				$this->user['uid']=$uid;
				if ($this->conf['debug']=='1') $this->messages['saveuser']=$this->pi_getLL('save_successful');
				// PATCH STEVER
				tx_trade_div::setSession('user',$this->user);
			}	
		}
		tx_trade_div::setUserCookie($user['username']);
	}
	
	/**
	 * Insert/update the order details in the database
	 */                                                   
	function processSaveOrder() {
		//debug(array('save order'));
		tx_trade_pricecalc::updateBasketMarkers($this->renderer->markerArray,$this->basket,$this->conf,$this->shipping,$this->payment,$this->order,$this->user,$this);
		// remove non saved properties
		//  populate order information for save
		$this->processSaveUser();
		if (sizeof($this->errors)==0) { 
			unset($this->messages['saveuser']);
			if ($this->user['uid']>0) {
				if ($this->PIDS['orderstorage']['uid']>0)  {
					$this->order['pid']=$this->PIDS['orderstorage']['uid'];
				} else { 	
					$this->order['pid']=$GLOBALS['TSFE']->id;
				}
				$this->order['crdate']=strtotime("now");
				$this->order['feusers_uid']=$this->user['uid'];
				// skip the insert if this order has already been saved
				// useful when returning from external payment eg paypal
				// also applicable where payment processing fails
				if ($this->order['uid']==0) {
					// query the database for column names in this table
					$orderToSave=array();
					$tt=$GLOBALS['TYPO3_DB']->admin_get_fields('tx_trade_orders');
					$databaseColumns=array_keys($tt);
					foreach ($this->order as $k => $v) {
						if (in_array($k,$databaseColumns)) {
							$orderToSave[$k]=$v;	
						}
					}	
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_trade_orders',$orderToSave);
					$uid=$GLOBALS['TYPO3_DB']->sql_insert_id();
					//$this->order=$orderToSave;
					$this->order['uid']=$uid;
					tx_trade_div::setSession('order',$this->order);
				}
				if ($this->order['uid']>0) {
					if ($this->conf['tracking_code_increment']==0) $this->conf['tracking_code_increment']=1; 
					$this->order['tracking_code']=$this->conf['tracking_code_label'].($this->conf['tracking_code_start']+($this->order['uid']*$this->conf['tracking_code_increment']));
					$this->renderer->init($this);
					$this->order['order_data']=$this->renderer->renderSectionNoWrap('ORDER_ITEMS');
					//unset($this->order['price_processing']);
					//unset($this->order['price_shipping']);
					
					// query the database for column names in this table
					$orderToSave=array();
					$tt=$GLOBALS['TYPO3_DB']->admin_get_fields('tx_trade_orders');
					$databaseColumns=array_keys($tt);
					foreach ($this->order as $k => $v) {
						if (in_array($k,$databaseColumns)) {
							$orderToSave[$k]=$v;	
						}
					}	
					
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_trade_orders','uid='.mysql_escape_string($this->order['uid']),$orderToSave);
				} else {
					 $this->errors[]='Failed order processing when saving order details.';
				}
			}
		} else {
			 // failed save user
			 $this->errors[]='Failed order processing when saving user details.';
		}
	}
	/**
	 * Insert/update the order details in the database
	 */                                                   
	function processSaveOrderNoUser() {
		//debug(array('save order'));
		tx_trade_pricecalc::updateBasketMarkers($this->renderer->markerArray,$this->basket,$this->conf,$this->shipping,$this->payment,$this->order,$this->user,$this);
		// remove non saved properties
		//  populate order information for save
		if ($this->PIDS['orderstorage']['uid']>0)  {
			$this->order['pid']=$this->PIDS['orderstorage']['uid'];
		} else { 	
			$this->order['pid']=$GLOBALS['TSFE']->id;
		}
		$this->order['crdate']=strtotime("now");
		$this->order['feusers_uid']=$this->user['uid'];
		// skip the insert if this order has already been saved
		// useful when returning from external payment eg paypal
		// also applicable where payment processing fails
		if ($this->order['uid']==0) {
			// query the database for column names in this table
			$orderToSave=array();
			$tt=$GLOBALS['TYPO3_DB']->admin_get_fields('tx_trade_orders');
			$databaseColumns=array_keys($tt);
			foreach ($this->order as $k => $v) {
				if (in_array($k,$databaseColumns)) {
					$orderToSave[$k]=$v;	
				}
			}	
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_trade_orders',$orderToSave);
			$uid=$GLOBALS['TYPO3_DB']->sql_insert_id();
			//$this->order=$orderToSave;
			$this->order['uid']=$uid;
			tx_trade_div::setSession('order',$this->order);
		}
		if ($this->order['uid']>0) {
			if ($this->conf['tracking_code_increment']==0) $this->conf['tracking_code_increment']=1; 
			$this->order['tracking_code']=$this->conf['tracking_code_label'].($this->conf['tracking_code_start']+($this->order['uid']*$this->conf['tracking_code_increment']));
			$this->renderer->init($this);
			$this->order['order_data']=$this->renderer->renderSectionNoWrap('ORDER_ITEMS');
			//unset($this->order['price_processing']);
			//unset($this->order['price_shipping']);
			
			// query the database for column names in this table
			$orderToSave=array();
			$tt=$GLOBALS['TYPO3_DB']->admin_get_fields('tx_trade_orders');
			$databaseColumns=array_keys($tt);
			foreach ($this->order as $k => $v) {
				if (in_array($k,$databaseColumns)) {
					$orderToSave[$k]=$v;	
				}
			}	
			
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_trade_orders','uid='.mysql_escape_string($this->order['uid']),$orderToSave);
		} else {
			 $this->errors[]='Failed order processing when saving order details.';
		}
	}
	
	
	/**
	 * Order finalisation - save user, save order, process payment(handlescript), send confirm email, update order record.
	 
	function processFinaliseCheckout() {
		tx_trade_pricecalc::updateBasketMarkers($this->renderer->markerArray,$this->basket,$this->conf,$this->shipping,$this->payment,$this->order,$this->user,$this);
		//  populate order information for save
		$this->processSaveUser();
		if (sizeof($this->errors)==0) { 
			unset($this->messages['saveuser']);
			if ($this->user['uid']>0) {
				if ($this->PIDS['orderstorage']['uid']>0)  {
					$this->order['pid']=$this->PIDS['orderstorage']['uid'];
				} else { 	
					$this->order['pid']=$GLOBALS['TSFE']->id;
				}
				$this->order['crdate']=strtotime("now");
				$this->order['feusers_uid']=$this->user['uid'];
				// skip the insert if this order has already been saved
				// useful when returning from external payment eg paypal
				// also applicable where payment processing fails
				//debug('process finlaise checkout');
				//debug($this->order);
				if ($this->order['uid']==0) {
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_trade_orders',$this->order);
					$uid=$GLOBALS['TYPO3_DB']->sql_insert_id();
					$this->order['uid']=$uid;
					tx_trade_div::setSession('order',$this->order);
				}
				//debug('process finlaise checkout got inser id '.$this->order['uid']);
				if ($this->order['uid']>0) {
					// payment handling 
					// if there are any problems, the handle script should add to $this->errors[]
					// it is also possible to add markers to $this->renderer->markerArray 
					// and potentially even override $this->cmd which will cause 
					// rendering of a matching template section
					// eg trn confirm code
					// update main and myrenderer renderers to set transaction details
					$this->renderer->markerArray["TRANSACTION_DETAILS"]='&nbsp;';
					$this->renderer->markerArray["ORDER_TRACKING_LINK"]='http://'.$GLOBALS['_SERVER']['SERVER_NAME'].''.$GLOBALS['_SERVER']['PHP_SELF'].'?id='.$this->PIDS['order_history_list']['uid'].'&user='.$this->user['username'].'&pass='.$this->user['password'].'&logintype=login&pid='.$this->PIDS['userstorage']['uid'].'&tx_trade_pi1[cmd]=order_history_list';
					if (strlen($this->conf['payment.'][$this->payment['method'].'.']['handleScript'])>0) {
						//debug(array('do payment processing',$basket['totalPrice']));
						require(t3lib_div::getFileAbsFileName(($this->conf['payment.'][$this->payment['method'].'.']['handleScript'])));
						//debug(array('done handle script'));
					}
					if (sizeof($this->errors)==0) { 
						$this->order['tracking_code']=$this->conf['tracking_code_label'].($this->conf['tracking_code_start']+($uid*$this->conf['tracking_code_increment']));
	 					//debug($this->order['tracking_code']);
	 					$this->order['status']=1;
						$this->renderer->init($this);
						$this->order['order_data']=$this->renderer->renderSectionNoWrap('ORDER_ITEMS');
						// following results in empty query error ??
						// PATCH STEVER - CHANGE UID TO ORDER[UID]
						$query="update tx_trade_orders set status='".mysql_escape_string($this->order['status'])."', tracking_code='".mysql_escape_string($this->order['tracking_code'])."', order_data='".mysql_escape_string($this->order['order_data'])."' where uid='".mysql_escape_string($this->order['uid'])."'"; 
						//debug(array($this->order,$query));
						$GLOBALS['TYPO3_DB']->sql_query($query);
						// send email confirmation of order to purchaser and admin
						$message=$this->renderer->renderSectionNoWrap('EMAIL_CHECKOUT');
						// To send HTML mail, the Content-type header must be set
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						if ($this->conf['plainTextEmails']==1) {
							$headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
						} else {
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						}
						$headers .= 'From: '.$this->conf['adminEmail'] . "\r\n";
						$headers .='Reply-To: '.$this->conf['adminEmail'] . "\r\n";
						$headers .='Return-Path: '.$this->conf['adminEmail'] ."\r\n";
						mail($this->user['email'],$this->conf['confirmEmailTitle'],$message,$headers);
						mail($this->conf['adminEmail'],$this->conf['confirmEmailAdminTitle'],$message,$headers);
						//debug($this->conf['adminEmail']);
						$this->doReset=true;
						tx_trade_div::setSession('order',$this->order);
					}
				} else {
					 $this->errors[]='Failed order processing when saving order details.';
				}
			}
		} else {
			 // failed save user
			 $this->errors[]='Failed order processing when saving user details.';
		}
	}
	*/
	/**
	 * Clear session of all shopping data
	 */
	function processReset() {
		//unset($this->user);
		unset($this->shipping);
		unset($this->payment);
		unset($this->order);
		unset($this->basket);
		unset($this->user);
		//$this->user['basket_approved']=0;
		tx_trade_div::removeSession('user');
		tx_trade_div::removeSession('shipping');
		tx_trade_div::removeSession('payment');
		tx_trade_div::removeSession('order');
		tx_trade_div::removeSession('basket');
		unset($GLOBALS['TSFE']->fe_user->user);
		unset($GLOBALS['_COOKIE']['tx_trade_pi_repeatuser']);
		//header("Location: index.php?id=".$GLOBALS['TSFE']->id);
		//exit();	
	}
	

	
	/***********************************************************************
	 * Marker Array Methods
	 **********************************************************************/
	 
	/**
	 * Create markers for form wrapper template
	 */ 
	function getFormMarkers() {
		$formMarkers=array();
		$formMarkers['FORM_URL']='index.php';
		$formMarkers['FORM_NAME']=$this->formName;
		$formMarkers['HIDDEN_FIELDS']='' .
				'<input type="hidden" name="id" value="'.$GLOBALS['TSFE']->id.'">' .
				'<input type="hidden" name="cmd" value="'.$this->cmd.'">'.
				'<input type="hidden" name="prevcmd" value="'.$this->cmd.'">'.
				'<input type="hidden" name="extrainfo" value="">'.
				'<input type="hidden" name="liststart" value="'.$GLOBALS['_POST']['liststart'].'">';
		foreach ($this->PIDS as $pK =>$pV) {
			$formMarkers['PID_'.strtoupper($pK)]=$pV['uid'];
			$formMarkers['SUBMIT_TO_'.strtoupper($pK)]=' name="'.$this->prefixId.'["buttons"]['.strtolower($pK).']" onClick="document.'.$this->formName.'.id.value='.$pV['uid'].'; document.'.$this->formName.'.cmd.value=\''.strtolower($pK).'\';"';
			$formMarkers['LINK_TO_'.strtoupper($pK)]='index.php?id='.$pV['uid'].'&tx_trade_pi1[cmd]='.strtolower($pK);
		}
		$errors=' ';
		if (is_array($this->errors)) {
			reset($this->errors);
			foreach ($this->errors as $eK => $eV) {
				$markerArray=array();
				$markerArray['ERROR_SINGLE']=$eV;
				$content=$this->cObj->getSubpart($this->renderer->template,'ERROR_WRAPPER');
				$content=$this->cObj->substituteMarkerArray($content,$markerArray,'###|###',true)  ;	
				$errors.=$content;
			}
		}
		$formMarkers['ERROR_MESSAGES']=$errors;
		$messages=' ';
		if (is_array($this->messages)) {
			reset($this->messages);
			foreach ($this->messages as $eK => $eV) {
				$markerArray=array();
				$markerArray['MESSAGE_SINGLE']=$eV;
				$content=$this->cObj->getSubpart($this->renderer->template,'MESSAGE_WRAPPER');
				$content=$this->cObj->substituteMarkerArray($content,$markerArray,'###|###',true)  ;	
				$messages.=$content;
			}
		}
		$formMarkers['MESSAGE_MESSAGES']=$messages;
		
		$hideAt=explode(",",$this->conf['hideMenuAt']);
		$progressBar="";
		if (($this->conf['showProgressBar']==1) && !in_array($this->cmd,$hideAt)) {
			reset($this->conf['checkout.']);
			$noMoreLinks=false;
			foreach ($this->conf['checkout.'] as $cK => $cV) {
				$cKNoDot=substr($cK,0,strlen($cK)-1);
				$item=$cV['label'];	
				$testResult=false;
				if ($this->cmd!=$cKNoDot&&$noMoreLinks==false) {
					eval($cV['condition']);
					if ($testResult) {
						$item='<a href="index.php?id='.$this->PIDS[$cKNoDot]['uid'].'&tx_trade_pi1[cmd]='.$cKNoDot.'" >'.$item.'</a>';
					}
				} else {
					$item=$this->cObj->stdWrap($item,$this->conf['progressBar.']['currentStdWrap.']);
					$noMoreLinks=true;
				}
				$item=$this->cObj->stdWrap($item,$cV['label.']['stdWrap.']);
				$item=$this->cObj->stdWrap($item,$this->conf['progressBar.']['itemStdWrap.']);
				$progressBar.=$item;
			}
			$progressBar=$this->cObj->stdWrap($progressBar,$this->conf['progressBar.']['stdWrap.']);
		}
		$formMarkers['PROGRESS_BAR']=$progressBar;
		
		// login box
		$showLoginAt=explode(',',$this->conf['showLoginAt']);
		$content='';
		if (in_array($this->cmd,$showLoginAt)) {
			if ($GLOBALS['TSFE']->fe_user->user['uid']==0) {
	  			$content=$this->renderer->renderComponent('LOGIN_BOX_COMPONENT',$this->renderer->markerArray);
	  		} else {
	         	$content=$this->pi_getLL('logged_in_as').$GLOBALS['TSFE']->fe_user->user['name'];
	         	if ($GLOBALS['TSFE']->fe_user->user['tx_trade_discount']>0) {
	         			$content.='<br>'.$this->pi_getLL('user_discount').$GLOBALS['TSFE']->fe_user->user['tx_trade_discount'];
	         	}
	         	if ($this->conf['priceField'] > 1) {
	         		$content.='<br>'.$this->pi_getLL('group_discount').$this->conf['priceField'];
	         	}
	        }
		}
		$formMarkers['LOGIN_BOX_USER']=$content;
		return $formMarkers;
	} 
	
	/**
	 * Create various markers including the various component level markers
	 */
	function getGlobalMarkers($markerArray) {
		//debug('get global markers');
		if (!is_array($markerArray)) $markerArray=array();
		$markerArray=t3lib_div::array_merge($markerArray,$this->getPaymentMarkers($this->payment));
		$markerArray=t3lib_div::array_merge($markerArray,$this->getOrderMarkers($this->order));
		$markerArray=t3lib_div::array_merge($markerArray,$this->getUserMarkers($this->user));
		$markerArray=t3lib_div::array_merge($markerArray,$this->getSearchBoxMarkers());
		$markerArray=tx_trade_pricecalc::updateBasketMarkers($markerArray,$this->basket,$this->conf,$this->shipping,$this->payment,$this->order,$this->user,$this);
		
		// major sections
		
		if ($this->conf['lists.'][$this->listType.'.']['search']==1) { 
			$markerArray['SEARCH_BOX_COMPONENT']=$this->renderer->renderComponent('ITEM_SEARCH_COMPONENT');
		} else {
			$markerArray['SEARCH_BOX_COMPONENT']='&nbsp;';
		}
		
		$markerArray['PAYMENT_METHOD_SECTION']=$this->renderer->renderPaymentMethod();
		$markerArray['SHIPPING_METHOD_SECTION']=$this->renderer->renderShippingMethod();
		if ($this->payment['method']>0) {
			$template=$this->cObj->getSubpart($this->renderer->template,'PAYMENT_DETAILS_ALL');
			$subtemplate=$this->cObj->getSubpart($template,'PAYMENT_DETAILS_'.$this->payment['method']);
			$markerArray['PAYMENT_DETAILS_SECTION']=$subtemplate;
			$template=$this->cObj->getSubpart($this->renderer->template,'PAYMENT_DETAILS_VIEW_SUPERSECTION');
			$subtemplate=$this->cObj->getSubpart($template,'PAYMENT_DETAILS_'.$this->payment['method']);
			$markerArray['PAYMENT_DETAILS_VIEW_SECTION']=$subtemplate;
		} else {
			$markerArray['PAYMENT_DETAILS_SECTION']='&nbsp;';
			$markerArray['PAYMENT_DETAILS_VIEW_SECTION']='&nbsp;';
		}
		if ($this->conf['shipping.'][$this->shipping['method']]['hideDetails']==1) {
			$markerArray['SHIPPING_DETAILS_SECTION']='';
			$markerArray['SHIPPING_DETAILS_VIEW_SECTION']='';
		} else {
			$markerArray['SHIPPING_DETAILS_SECTION']=$this->renderer->renderComponent('SHIPPING_DETAILS_COMPONENT');
			$markerArray['SHIPPING_DETAILS_VIEW_SECTION']=$this->renderer->renderComponent('SHIPPING_DETAILS_VIEW_COMPONENT');
		}
		$markerArray['USER_DETAILS_SECTION']= $this->renderer->renderComponent('USER_DETAILS_COMPONENT');
		$markerArray['USER_DETAILS_VIEW_SECTION']= $this->renderer->renderComponent('USER_DETAILS_VIEW_COMPONENT');
		$markerArray['ORDER_ITEMS_SECTION']= $this->renderer->renderSectionNoWrap('ORDER_ITEMS');
		if ($this->conf['showMiniBasket']==1) {
			$markerArray['MINI_BASKET']= $this->renderer->renderSectionNoWrap('BASKET_OVERVIEW');	
		} else {
			$markerArray['MINI_BASKET']=' ';
		}
   		$markerArray['FORM_URL']='index.php';		
		// other global
		foreach ($this->PIDS as $pK =>$pV) {
			$markerArray['PID_'.strtoupper($pK)]=$pV['uid'];
			// old js based version onClick="document.'.$this->formName.'.id.value='.$pV['uid'].'; document.'.$this->formName.'.cmd.value=\''.strtolower($pK).'\';"
			if ($pK=='checkout'&&$this->conf['force_checkout_to_https']==1) {
				$markerArray['SUBMIT_TO_'.strtoupper($pK)]=' name="'.$this->prefixId.'[buttons]['.strtolower($pK).']" onClick="document.'.$this->formName.'.action=\'https://'.$_SERVER["HTTP_HOST"].'/index.php\'   ;document.'.$this->formName.'.id.value='.$pV['uid'].'; " ';
			} else {
				$markerArray['SUBMIT_TO_'.strtoupper($pK)]=' name="'.$this->prefixId.'[buttons]['.strtolower($pK).']" onClick="document.'.$this->formName.'.id.value='.$pV['uid'].'; " ';
			}
			$formMarkers['LINK_TO_'.strtoupper($pK)]='index.php?id='.$pV['uid'].'&tx_trade_pi1[cmd]='.strtolower($pK);
		}
		$markerArray['FORM_NAME']=$this->formName;
		$markerArray['SEARCH_BUTTON_NAME']=$this->searchButtonName;
		$markerArray['SAVE_USER_BUTTON_NAME']=$this->saveUserButtonName;
		$markerArray['FINALISE_BUTTON_NAME']=$this->finaliseButtonName;
		$markerArray['CURRENCY_CODE']=$this->conf['currencyCode'];
		$markerArray['CURRENCY_SYMBOL']=$this->conf['currencySymbol'];
		$markerArray['LIST_TITLE']=$this->conf['lists.'][$this->listType.'.']['title'];
		$markerArray['SHOP_OWNER_DETAILS']=$this->conf['shopOwnerDetails'];
		$listMenu="";
		if ($this->conf['showMenu']==1&&sizeof($this->conf['lists.'])>0) {
			reset($this->conf['lists.']);
			$cmdList=explode(',',$this->conf['cmdList']);
			foreach ($this->conf['lists.'] as $cK => $cV) {
				$cKNoDot=substr($cK,0,strlen($cK)-1);
				if (in_array('list_'.$cKNoDot,$cmdList)) {
					$item=$cV['label'];	
					$item='<a href="index.php?id='.$this->PIDS['list_'.$cKNoDot]['uid'].'&tx_trade_pi1[cmd]=list_'.$cKNoDot.'" >'.$item.'</a>';
					$item=$this->cObj->stdWrap($item,$cV['label.']['stdWrap.']);
					$item=$this->cObj->stdWrap($item,$this->conf['listMenu.']['itemStdWrap.']);
					$listMenu.=$item;
				}
			}
			$listMenu=$this->cObj->stdWrap($listMenu,$this->conf['listMenu.']['stdWrap.']);
		} else {
			$listMenu='&nbsp;';
		} 
		$markerArray['LIST_MENU']=$listMenu;
		
		return $markerArray;
	}
	 
	/**
	 * Create markers related to form data
	 */
	function getOrderMarkers($data) {
		$markerArray=array();
		if (is_array($data)) {
			reset($data);
			foreach ($data as $dK=>$dV) {
				$key="ORDER_".strtoupper($dK);
				$markerArray[$key]=$dV;
			}
			// ensure tracking code marker which may have been set by payment processing scripts
			if (strlen(trim($data['tracking_code']))>0) 		$markerArray['ORDER_TRACKING_CODE']=$data['tracking_code'];
			else $markerArray['ORDER_TRACKING_CODE']='nbsp;';
			// other marker
			$markerArray['ORDER_CRDATE']=date('d/m/Y',$data['crdate']);
			if (strlen($data['comment'])==0) $markerArray['ORDER_COMMENT']='';
			$markerArray['ORDER_STATUS']=$this->orderStatus[$data['status']];
			if (strlen(trim($markerArray['ORDER_STATUS']))==0) $markerArray['ORDER_STATUS']='Not processed';
			//$this->conf['currencySymbol'].
			$markerArray['ORDER_PRICE_TOTAL_TAX']=sprintf("%01.2f",$data['price_total_tax']); 
		}
		$markerArray['LINK_TO_ORDER_HISTORY_SINGLE']='index.php?id='.$this->PIDS['order_history_single']['uid'].'&tx_trade_pi1[cmd]=order_history_single&tx_trade_pi1[order_uid]='.$data['uid'];
		return $markerArray;
	}
	
	/**
	 * Create markers related to payment data
	 */
	function getPaymentMarkers($data) {
		$markerArray=array();
		if (is_array($data)) {
			reset($data);				
			foreach ($data as $dK=>$dV) {
				$key="PAYMENT_".strtoupper($dK);
				$markerArray[$key]=$dV;
			}
		}
		if (strlen($data['card_number'])==0) $markerArray['PAYMENT_CARD_NUMBER']='';
		if (strlen($data['card_name'])==0) $markerArray['PAYMENT_CARD_NAME']='';
		
		for ($i=date('Y'); ($i-date('Y') < 12); $i++) {
			if ($i==$this->payment['CARD_EXP_YEAR']) $sel=' selected="true" '; else $sel='';
			$yearSelector.='<option value='.$i.'>'.$i.'</option>';
		}
		$markerArray['SELECT_YEAR_OPTIONS']=$yearSelector;
		for ($i=1; ($i < 13); $i++) {
			if ($i==$this->payment['CARD_EXP_MONTH']) $sel=' selected="true" '; else $sel='';
			$monthSelector.='<option value='.$i.'>'.sprintf("%02d",$i).'</option>';
		}
		$markerArray['SELECT_MONTH_OPTIONS']=$monthSelector;
		$markerArray['PAYMENT_TITLE']=$this->conf['payment.'][$this->payment['method'].'.']['title'];
		
		return $markerArray;
	}

	/**
	 * Create markers related to user data
	 */
	function getUserMarkers($data) {
		$markerArray=array();
		foreach (explode(",","uid,".$GLOBALS['TCA']['fe_users']['feInterface']['fe_admin_fieldList']) as $tK => $tV) {
			$key="USER_".strtoupper($tV);
			$markerArray[$key]='';
			$key2="USER_TX_TRADE_SHIPPING_".strtoupper($tV);
			$markerArray[$key2]='';
		}
		if (is_array($data)) {
			reset($data);
			foreach ($data as $dK=>$dV) {
				$key="USER_".strtoupper($dK);
				$markerArray[$key]=$dV;
			}
		}
		$markerArray['USER_PASSWORD1']=$this->user['password'];
		$markerArray['USER_PASSWORD2']=$this->user['password'];
		if (strlen($this->conf['shipping.'][$this->shipping['method'].'.']['image.']['file'])>0) $markerArray['SHIPPING_IMAGE']='<img src="'.tx_trade_div::getFileName($this->conf['shipping.'][$this->shipping['method'].'.']['image.']['file']).'" >';
		else $markerArray['SHIPPING_IMAGE']='';
		$markerArray['SHIPPING_TITLE']=$this->conf['shipping.'][$this->shipping['method'].'.']['title'];
		
		return $markerArray;
	}	


	/**
	 * Create markers related to the search form
	 */
	function getSearchBoxMarkers($markerArray=array()) {
			$markerArray['FORM_URL']=$this->PIDS['search']['link'];
			$markerArray['SWORDS']=$this->search['text'];
			$markerArray['SEARCH_FIELD_NAME']=$this->prefixId.'[search][text]';
			return $markerArray;
	}
	
	/***********************************************************************
	 * These functions differs from the previous marker functions in that they
	 * are used iteratively to compile a list
	 **********************************************************************/
	 
	 
	/**
	 * Create markers related to product data
	 */ 
	function getProductMarkers($data) {
		$markerArray=array();
		foreach ($data as $dK=>$dV) {
			$key="PRODUCT_".strtoupper($dK);
			$markerArray[$key]=$dV;
		}
		$markerArray["PRODUCT_ANCHOR"]="<a name='product-".$data['uid']."' />";
		$markerArray=tx_trade_pricecalc::updateProductMarkers($markerArray,$data,$this->user,$this);
		// need to use no brackets to allow javascript update
		$markerArray['FIELD_NAME']='tx_trade_pi1_addtobasket_'.$data['uid'];
		$imgFiles=explode(",",$data['image']);
		
		for ($i=1; $i<4; $i++ ) {
			$j=$i-1;
			if (strlen(trim($imgFiles[($i-1)]))>0) $imgFiles[$j] = 'uploads/tx_trade/'.$imgFiles[($i-1)];
			else if ($i==1) $imgFiles[0]=tx_trade_div::getFilename($this->conf['noImageAvailable']);
			if (strlen(trim($imgFiles[($i-1)]))>0) {
				$this->conf['singleViewImage.']['file']=$imgFiles[($i-1)];
				$markerArray['PRODUCT_SINGLE_IMAGE_'.$i]=$this->cObj->IMAGE($this->conf['singleViewImage.']);
				//'<img src="typo3/thumbs.php?size='.$this->conf['imageWidthSingle'].'&file=../uploads/tx_trade/'.$imgFiles[($i-1)].'" border="0" />';
				// generate the md5 hash required for the security path to thumbs.php
				// If the filereference $this->file is relative, we correct the path
				/*$file=$imgFiles[($i-1)];
				$mtime = filemtime($file);
				// Create MD5 check to prevent viewing of images without permission
				$markerArray['PRODUCT_LIST_IMAGE_'.$i]='';
				if ($mtime)     {
					// Always use the absolute path for this check!
					$check = basename($file).':'.$mtime.':'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
					$md5_real = t3lib_div::shortMD5($check);
					$markerArray['PRODUCT_LIST_IMAGE_'.$i]='<img src="typo3/thumbs.php?size='.$this->conf['imageWidthList'].'&md5sum='.$md5_real.'&file=../'.$imgFiles[($i-1)].'" border="0" />';
				}*/
				$this->conf['listViewImage.']['file']=$imgFiles[($i-1)];
				$markerArray['PRODUCT_LIST_IMAGE_'.$i]=$this->cObj->IMAGE($this->conf['listViewImage.']);
				
				$markerArray['PRODUCT_IMAGE_'.$i]=$imgFiles[($i-1)];
			} else {
				$markerArray['PRODUCT_SINGLE_IMAGE_'.$i]="&nbsp;";	
				$markerArray['PRODUCT_LIST_IMAGE_'.$i]="&nbsp;";
				$markerArray['PRODUCT_IMAGE_'.$i]="&nbsp;";
			}
		}
		$markerArray['PRODUCT_DESCRIPTION']=$this->cObj->stdWrap($markerArray['PRODUCT_DESCRIPTION'],$this->conf['RTE.']['stdWrap.']);
		//$markerArray['PRODUCT_TX_TRADEUSERGROUPASPRODUCT_ORDER_INSTRUCTIONS']=$this->cObj->stdWrap($markerArray['PRODUCT_TX_TRADEUSERGROUPASPRODUCT_ORDER_INSTRUCTIONS'],$this->conf['RTE.']['stdWrap.']);
		//$markerArray['PRODUCT_PRICE1']=$this->conf['currencySymbol'].$data['price1'];
		$markerArray['PRODUCT_PRICE2']=$this->conf['currencySymbol'].$data['price2'];
		$markerArray['PRODUCT_PRICE3']=$this->conf['currencySymbol'].$data['price3'];
		$markerArray['PRODUCT_PRICE1']=$this->conf['currencySymbol'].tx_trade_pricecalc::getPrice($data,$this->user,$this);
		if (strlen(trim($data['url']))>0) $markerArray['PRODUCT_URL']='<a href="'.$data['url'].'" target="_new">External Website</a>';
		else $markerArray['PRODUCT_URL']='';
		$markerArray['LISTTYPE']=$this->piVars['listtype'];
		if ($this->piVars['backPID']>0)  {
			$markerArray['LISTTYPE_PID']=$this->piVars['backPID'];
		} else {
			$markerArray['LISTTYPE_PID']=$this->PIDS['list_'.$this->piVars['listtype']]['uid'];
		}
		if (strlen(trim($data['datasheet']))>0) {
			$markerArray['PRODUCT_DATASHEET_LINK']='<a href="uploads/tx_trade/'.$data['datasheet'].'" >Data Sheet</a>';
		} else {
			$markerArray['PRODUCT_DATASHEET_LINK']='';
		}
		return $markerArray;
	}
	
	/**
	 * Create markers related to product categories
	 */ 
	function getCategoryMarkers($data) {
		$markerArray=array();
		foreach ($data as $dK=>$dV) {
			$key="CATEGORY_".strtoupper($dK);
			$markerArray[$key]=$dV;
		}
		$categoryAnchor="<a name='category-".$data['uid']."' />";
		// indent second level categories
		if ($data['parent']>0) {
			$markerArray['CATEGORY_TITLE_HEADER']="<H2>".$markerArray['CATEGORY_TITLE']."</H2>".$categoryAnchor;
			$markerArray['CATEGORY_TITLE_HEADER_2']=$markerArray['CATEGORY_TITLE'].$categoryAnchor;
			$markerArray['CATEGORY_TITLE_HEADER_1']='';
		}
		else {
			$markerArray['CATEGORY_TITLE_HEADER']="<H1>".$markerArray['CATEGORY_TITLE']."</H1>".$categoryAnchor;
			$markerArray['CATEGORY_TITLE_HEADER_1']=$markerArray['CATEGORY_TITLE'].$categoryAnchor;
			$markerArray['CATEGORY_TITLE_HEADER_2']='';
		}
		$markerArray['CATEGORY_DESCRIPTION']=$this->cObj->stdWrap($markerArray['CATEGORY_DESCRIPTION'],$this->conf['RTE.']['stdWrap.']);
		
		return $markerArray;
	}
	
	/**
	 * Create empty markers related to product categories
	 */
	function getEmptyCategoryMarkers() {
		$markerArray['CATEGORY_TITLE']=" ";
		$markerArray['CATEGORY_DESCRIPTION']=" ";
		$markerArray['CATEGORY_TITLE_HEADER_1']=" ";
		$markerArray['CATEGORY_TITLE_HEADER_2']=" ";
		$markerArray['CATEGORY_TITLE_HEADER']=" ";
		return $markerArray;
	}
	
	
	/***********************************************************************
	 * Validation Functions
	 **********************************************************************/
	 
	
	/***********************************************************************
	 * Determine what data to validate based on hidden fields in each form component
	 **********************************************************************/		
	function validate() {
		// dont validate when logging in
		if (trim($GLOBALS['_POST']['login'])!='login'||$this->piVars['validate_now']==1) {
			if ($this->piVars['submit_payment_method']&&$this->payment['method']==0) {
				$this->errors[]=$this->pi_getLL('payment_method_required');	
			}
			if ($this->piVars['submit_shipping_method']&&$this->shipping['method']==0) {
				$this->errors[]=$this->pi_getLL('shipping_method_required');	
			}
			if ($this->piVars['submit_user_details']) {
				$this->validateUserDetails();	
			}
			if ($this->piVars['submit_shipping_details']&&$this->cmd!='copy_address_to_shipping') {
				$this->validateShippingDetails();	
			}
			if ($this->piVars['submit_payment_details']) {
				$this->validatePaymentDetails();	
			}
			
		}	
	}

	/**
	 * Validate user details and update user data to ensure unique username and possibly generate a password
	 */
	function validateUserDetails() {
		$valid=1;
		// valid email
		// didnt allow email addresses at localhost
		//if (!t3lib_div::validEmail($this->user['email'])) {
		if (strlen(trim(($this->user['email'])))==0||strpos($this->user['email'],'@')==0) {
			$valid=0;
			$this->errors[]='Invalid email address';
		}
		if ($this->user['skipusernamechecks']!=1) { 
			// if username is not sent as post, use email address
			//debug($this->piVars['user']['force_new_user']);
			if ((strlen(trim($this->user['username']))==0&&(strlen(trim($this->piVars['user']['username']))==0)||$this->piVars['user']['force_new_user']==1)) {
			//if (!isset($this->user['username'])) {
				if ($this->piVars['user']['force_new_user']==1) {
					$this->user['username']=rand(100,999).'_'.$this->user['email'];
				} else {
					$this->user['username']=$this->user['email'];
				}
				$usernameGenerated=true;
			}
			// is username in use?
			// if logged in or forcing new user, it certainly is and that is fine
			if ($GLOBALS['TSFE']->fe_user->user['uid']==0) {
				// db lookup
				$prevUser=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid','fe_users',"username='".mysql_escape_string($this->user['username'])."'".' and pid='.$this->PIDS['userstorage']['uid'].' and disable=0 and deleted=0','','',1,'');
				if (sizeof($prevUser)>0) {
					if ($usernameGenerated) {
						$this->errors[]=$this->pi_getLL('email_in_use');
						$this->user['force_new_user']=1;
						$valid=0;
					} else {
						$this->errors[]=$this->pi_getLL('username_in_use').$this->user['username'];
						$this->user['force_new_user']=1;
						$valid=0;
					}	
				}
			}
		}
		$this->user['valid']=$valid;
		tx_trade_div::setSession('user',$this->user);	
		
		// if password is not passed as post var and there is not FE user, generate a password
		if ((!isset($this->piVars['user']['password1']))) {
			if (($GLOBALS['TSFE']->fe_user->user['uid']==0)) {
				$this->user['password']=tx_trade_div::getRandomPassword();
				$this->user['password1']=$this->user['password'];
				$this->user['password2']=$this->user['password'];
			} else {
				$this->user['password']=$GLOBALS['TSFE']->fe_user->user['password'];
				$this->user['password1']=$this->user['password'];
				$this->user['password2']=$this->user['password'];
			}
		} 	
		
		// match passwords
		if ($this->user['password1']!=$this->user['password2']) {
			$valid=0;
			$this->errors[]=$this->pi_getLL('password_no_match');;		
		} else { 
			$this->user['password']=$this->user['password1'];
		}
		// required fields
		foreach (explode(',',$this->conf['userRequiredFields']) as $uK =>  $uV) {
			if (strlen(trim($uV))>0 && !strlen(trim($this->user[$uV]))>0) {
				$valid=0;
				$fieldName=$GLOBALS['TCA']['fe_users']['columns'][$uV]['label'];
				$fieldName=$this->LANG->sL($fieldName);
				$this->errors[]=$this->pi_getLL('missing_required_field').$fieldName;
			}
		}
		
		
	}
	
	/**
	 * Validate required shipping details
	 */
	function validateShippingDetails() {
		$valid=1;
		// TODO ensure shipping methods is set		if ()
		foreach (explode(',',trim($this->conf['shippingRequiredFields'])) as $uK =>  $uV) {
			if (strlen(trim($uV))>0 && !strlen(trim($this->user[$uV]))>0) {
				$valid=0;
				$fieldName=$GLOBALS['TCA']['fe_users']['columns'][$uV]['label'];
				$fieldName=$this->LANG->sL($fieldName);
				$this->errors[]=$this->pi_getLL('missing_required_field').$fieldName;
			}
		}
		if ($this->shipping['method']>0) {
			$this->user['valid_shipping_details']=$valid;
		} else {
			$this->user['valid_shipping_details']=0;
			$this->errors[]=$this->pi_getLL('shipping_method_required');
		}
		tx_trade_div::setSession('user',$this->user);
		return $this->errors;
	}
	
	/**
	 * Validate required payment details
	 */	
	function validatePaymentDetails() {
		// has a payment method been selected
		// evaluate detailsOK testing for selected payment method 
		if (strlen($this->conf['payment.'][$this->payment['method'].'.']['detailsOK'])>0) 	{
			eval($this->conf['payment.'][$this->payment['method'].'.']['detailsOK']);
		} else  {
			$detailsOK=true;	
		}
		$this->user['valid_payment_details']=0;
		if ($this->payment['method']>0) {
			if ($this->user['valid']==1) {
				if ($this->user['valid_shipping_details']==1&&$this->conf['shipping.'][$this->payment['shipping'].'.']['detailsRequired']!=1) {
					if ($detailsOK) {
							$this->user['valid_payment_details']=1;	
					} else {
						$this->errors[]=$this->conf['payment.'][$this->payment['method'].'.']['detailsError'];
					}
				} else {
					$this->errors[]=$this->pi_getLL('missing_shipping_details');
				}
			} else {
				$this->errors[]=$this->pi_getLL('missing_user_details');
			}
		} else {
			$this->errors[]=$this->pi_getLL('payment_method_required');
		}
		tx_trade_div::setSession('user',$this->user);
	}
	
	/**
	 * General credit card validity checks
	 */
	function validateCreditCard($cc_no,$month,$year,$cc_name,$cvn='') {
		if (credit_card::validate ($cc_no)) {
			//$this->errors[]=$this->pi_getLL('invalid_cc_number');
		}
		if (!credit_card::checkDate ($month, $year)) {
			//$this->errors[]=$this->pi_getLL('invalid_cc_date');
		}
		/*if ($this->conf['requireCVN']==1&&strlen(trim($cvn))==0) {
			$this->errors[]=$cvn.$this->pi_getLL('cc_cvn_required');
		}*/
		if (strlen(trim($cc_name))==0) {
				$this->errors[]=$this->pi_getLL('cc_name_required');
		}
		if (count($this->errors)==0) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Calls user function defined in TypoScript
	 * This function was ripped from tt_products
	 *
	 * @param	integer		$mConfKey : if this value is empty the var $mConfKey is not processed
	 * @param	mixed		$passVar : this var is processed in the user function
	 * @return	mixed		the processed $passVar
	 */
	function userProcess($mConfKey, $passVar) {
		if ($this->conf[$mConfKey]) {
			$funcConf = $this->conf[$mConfKey . '.'];
			$funcConf['parentObj'] = & $this;
			$passVar = $GLOBALS['TSFE']->cObj->callUserFunction($this->conf[$mConfKey], $funcConf, $passVar);
		}
		return $passVar;
	}		
}		


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_pi1.php']);
}
//debug($GLOBALS['_SERVER']);
		//debug($GLOBALS['_POST']);
		//debug($this->piVars);
		//debug($this->piVars['buttons.']);
		//debug($GLOBALS['_SESSION']);
		//debug($GLOBALS['_GET']);
		//debug($this->piVars);
		//debug($GLOBALS['TSFE']->fe_user->user);
		
?>
