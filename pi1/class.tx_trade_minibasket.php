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
require_once (t3lib_extMgm::extPath('trade').'/lib/class.tx_trade_render.php');
require_once (t3lib_extMgm::extPath('trade').'/lib/class.tx_trade_div.php');
class tx_trade_minibasket extends tslib_pibase {
	var $prefixId = 'tx_trade_minibasket';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_trade_minibasket.php';	// Path to this script relative to the extension dir.
	var $extKey = 'trade';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	// library instances
		var $renderer;
	// other
		var $basket;
		var $PIDS;	
		
	/**
	 * Main controller, entry point to plugin execution
	*/	
	function main($content,$conf) {
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->renderer=t3lib_div::makeInstance('tx_trade_render');
		//debug($this->conf['PIDS.']);
		$cmdList=$this->conf['cmdList'];
		foreach (explode(",",$cmdList) as $pK => $pV) {
			if ($this->conf['PIDS.'][$pV]>0) {
				$this->PIDS[$pV]['uid']=$this->conf['PIDS.'][$pV];
			} else {
				$this->PIDS[$pV]['uid']=$GLOBALS['TSFE']->id;
			}
			$this->PIDS[$pV]['link']='index.php?id='.$this->PIDS[$pV]['uid'].'&tx_trade_pi1[cmd]='.$pV;
		}
		
		//session_start();
		// get basket contents
		$this->basket=tx_trade_div::getSession('basket');
		// allow for post vars
		//debug(array($basket));
		$this->basket=$this->processAddToBasket($this->basket);
		$content=' ';
		if (count($this->basket) >0) { 
			// render basket and return
			//debug(array($basket));
			$this->renderer->init($this);
			//debug(array('here.',$this->renderer->template));
			$content='<FORM  method="post" action="index.php?id='.$GLOBALS['TSFE']->id.'" name="myform"  >'
			.'<input type="hidden" name="tx_trade_pi1[cmd]" value="checkout"  >'
			.$this->renderer->renderSectionNoWrap('BASKET_OVERVIEW').'</form>';	
		}
		return $content;
	}
	/**
	 * Update the basket but don't update session (leave this to 
	 * main plugin)
	 */
	function processAddToBasket($basket) {
		// add to basket
		reset($GLOBALS['_POST']);
		foreach ($GLOBALS['_POST'] as $pK => $pV) {
			// if it is an add to basket post var
			if (strpos('x'.$pK,'tx_trade_pi1_addtobasket_')==1) {
				$aK=substr($pK,25);
				$aV=$pV;
				if (strlen(trim($aV))==0||trim($aV)=='0') {
					unset($basket[$aK]);
				} else if ($aV>0) {
					$aV=intval($aV);
					$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_products','uid='.mysql_escape_string($aK),'','title ASC','');
					if ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						if (trim($aV)=='') $aV=1;
						$row['basket_qty']=$aV;
						$basket[$row['uid']]=$row;
					}
				}
			}
		}
		return $basket;	
	}
	
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
				$markerArray['SUBMIT_TO_'.strtoupper($pK)]=' '; // name="'.$this->prefixId.'[buttons]['.strtolower($pK).']" onClick="document.'.$this->formName.'.action=\'https://'.$_SERVER["HTTP_HOST"].'/index.php\'   ;document.'.$this->formName.'.id.value='.$pV['uid'].'; " ';
			} else {
				$markerArray['SUBMIT_TO_'.strtoupper($pK)]=' '; // name="'.$this->prefixId.'[buttons]['.strtolower($pK).']" onClick="document.'.$this->formName.'.id.value='.$pV['uid'].'; " ';
			}
			$formMarkers['LINK_TO_'.strtoupper($pK)]=' '; //index.php?id='.$pV['uid'].'&tx_trade_pi1[cmd]='.strtolower($pK);
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
		/*if (sizeof($this->conf['lists.'])>0) {
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
		*/
		
		$markerArray['LIST_MENU']=$listMenu;
		//debug($markerArray);
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
	
}
	
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_minibasket.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_minibasket.php']);
}
		
?>
