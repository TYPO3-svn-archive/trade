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
 * @author	Steve Ryan <stever@syntithenai.com>, Roger Bunyan 
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_trade_pricecalc extends tslib_pibase {
	var $prefixId = 'tx_trade_pricecalc';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_trade_pricecalc.php';	// Path to this script relative to the extension dir.
	var $extKey = 'trade';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * Return a price value taking into account any user or group discounts
	 * Pass in an array containing fields of a product record
	 */
	function getPrice($data,$user,$caller) {
		$priceTax=0;
		$fieldToUse='1';
		// allow for using different prices by user group
		if (strlen($this->conf['priceField'])>0) $fieldToUse=$this->conf['priceField'];
		$priceTax=$data['price'.$fieldToUse];
		// apply user discount
		if ($GLOBALS['TSFE']->fe_user->user['tx_trade_discount']>0) {
			$priceTax-=($priceTax*($GLOBALS['TSFE']->fe_user->user['tx_trade_discount']/100));
		} 
		//debug(array('get price',$priceTax,$GLOBALS['TSFE']->fe_user->user['name'],$GLOBALS['TSFE']->fe_user->user['tx_trade_discount'],$fieldToUse));
		$params=array($data,$user,$priceTax);
		return  tx_trade_div::userProcess('getPrice',$params,$priceTax,$caller); 
	}
	
	/**
	 * Calculate and return the cost of shipping for this order
	 */
	function getShippingPrice($conf,$shipping,$totalPrice,$caller) {
		$priceTax=0;
		if ($this->conf['shipping.'][$shipping['method'].'.']['priceTax']>0) {
			$priceTax=$this->conf['shipping.'][$shipping['method'].'.']['priceTax'];	
		}
		if ($this->conf['shipping.'][$shipping['method'].'.']['percentOfGoodsTotal']>0) {
			$priceTax+=($this->conf['shipping.'][$shipping['method'].'.']['percentOfGoodsTotal']/100)*$totalPrice;
		}
		if (strlen($this->conf['shipping.'][$shipping['method'].'.']['calculationScript'])>0) {
			require(t3lib_div::getFileAbsFileName($this->conf['shipping.'][$shipping['method'].'.']['calculationScript']));
		} 
		$params=array($conf['shipping.'][$shipping['method'].'.'],$shipping,$totalPrice);
		//debug(array('get shipping price',$params));
		return  tx_trade_div::userProcess('getShippingPrice',$params,$priceTax,$caller); 

	}
	
	/**
	 * Calculate and return the cost of payment processing for this order
	 */
	function getPaymentProcessingPrice($conf,$payment,$totalPrice,$caller) {
		$priceTax=0;
		if ($this->conf['payment.'][$payment['method'].'.']['priceTax']>0) {
			$priceTax=$this->conf['payment.'][$payment['method'].'.']['priceTax'];	
		}
		if ($this->conf['payment.'][$payment['method'].'.']['percentOfGoodsTotal']>0) {
			$priceTax=($this->conf['payment.'][$payment['method'].'.']['percentOfGoodsTotal']/100)*$totalPrice;
		}
		if ($this->conf['payment.'][$payment['method'].'.']['calculationScript']>0) {
			require(t3lib_div::getFileAbsFileName($this->conf['payment.'][$payment['method'].'.']['calculationScript']));
		}
		$params=array($payment,$totalPrice);
		return  tx_trade_div::userProcess('getPaymentProcessingPrice',$params,$priceTax,$caller);
	}
	
	
	
	/**
	 * Generate a marker array containing price and qty totals for a single product
	 */
	function updateProductMarkers($markerArray,$data,$user,$caller) {
		$markerArray['FIELD_QTY']=$data['basket_qty'];
		$markerArray['PRICE_TOTAL_TAX']=$this->conf['currencySymbol'].sprintf("%01.2f",$data['basket_qty']*tx_trade_pricecalc::getPrice($data,$user,$caller));
		$markerArray['PRICE_TAX']=$this->conf['currencySymbol'].sprintf("%01.2f",tx_trade_pricecalc::getPrice($data,$user,$caller));
		
		/*$markerArray['REMOVE_FROM_CART']='
			<form action=index.php?id='.$GLOBALS['TSFE']->id.'" method="POST" >
			<input type="hidden" name="tx_trade_pi1_addtobasket_'.$data['uid'].'" value="0" >
			<input type="submit" value="Remove"  >
			</form>';*/
		$markerArray['REMOVE_FROM_CART']='<input type="submit" value="Remove"  onclick="document.myform.tx_trade_pi1_addtobasket_'.$data['uid'].'.value=\'0\';};  ">';
		return $markerArray;
	}
	
	/**
	 * Generate a marker array containing price and qty totals for a basket of products.
	 * Update the order combined price in the process
	 */
	function updateBasketMarkers($markerArray,&$basket,$conf,$shipping,$payment,&$order,$user,$caller) {
		//debug(array('update basket markers',$basket));
		$total=0;
		$totalPrice=0;
		if (is_array($basket)) {
			reset($basket);
			foreach ($basket as $bK => $bV ) {
				$total+=$bV['basket_qty'];
				$totalPrice+=$bV['basket_qty']*tx_trade_pricecalc::getPrice($bV,$user,$caller);
			}
		}
		$markerArray['NUMBER_GOODSTOTAL']=$total;
		$order['total_items']=$total;
		$markerArray['PRICE_GOODSTOTAL_TAX']=$this->conf['currencySymbol'].sprintf("%01.2f", $totalPrice);
		
		
		$shippingPrice=tx_trade_pricecalc::getShippingPrice($conf,$shipping,$totalPrice,$caller);
		$paymentProcessingPrice=tx_trade_pricecalc::getPaymentProcessingPrice($conf,$payment,$totalPrice,$caller);
		
		$markerArray['PRICE_SHIPPING_TAX']=$this->conf['currencySymbol'].sprintf("%01.2f", $shippingPrice);
		$markerArray['PRICE_PAYMENT_TAX']=$this->conf['currencySymbol'].sprintf("%01.2f", $paymentProcessingPrice);

		$order['price_total_tax']=sprintf("%01.2f",$totalPrice+$shippingPrice+$paymentProcessingPrice);
		$order['price_shipping']=sprintf("%01.2f",$shippingPrice);
		$order['price_processing']=sprintf("%01.2f",$paymentProcessingPrice);		
		
		$markerArray['PRICE_NOSHIPPINGPAYMENT_TAX']=$this->conf['currencySymbol'].sprintf("%01.2f", $totalPrice);
		$markerArray['PRICE_TOTAL_TAX']=$this->conf['currencySymbol'].sprintf("%01.2f", $totalPrice+$shippingPrice+$paymentProcessingPrice);
		
		
		return $markerArray;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_pricecalc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_pricecalc.php']);
}
?>