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
 * @author	Steve Ryan, Roger Bunyan <>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_trade_pricecalc_pi1 extends tslib_pibase {
	var $prefixId = 'tx_trade_pricecalc_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_trade_pricecalc_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'trade';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	function getPrice($data) {
		// TODO allow for different user groups using different prices
		return $data['price1'];	
	}
	// item calcs
	function updateProductMarkers($markerArray,$data) {
		$markerArray['FIELD_QTY']=$data['basket_qty'];
		$markerArray['PRICE_TOTAL_TAX']=$data['basket_qty']*tx_trade_pricecalc_pi1::getPrice($data);
		$markerArray['PRICE_TAX']=tx_trade_pricecalc_pi1::getPrice($data);
		return $markerArray;
	}
	// basket calcs
	function updateBasketMarkers($markerArray) {
		$total=0;
		$totalPrice=0;
		reset($this->basket);
		foreach ($this->basket as $bK => $bV ) {
			$total+=$bV['basket_qty'];
			$totalPrice+=$bV['basket_qty']*tx_trade_pricecalc_pi1::getPrice($bV);
		}
		$markerArray['NUMBER_GOODSTOTAL']=$total;
		$markerArray['PRICE_GOODSTOTAL_TAX']=$totalPrice;
		
		$markerArray['PRICE_SHIPPING_TAX']='2.50';
		$markerArray['PRICE_PAYMENT_TAX']='.25';
		
		$markerArray['PRICE_TOTAL_TAX']=$totalPrice+$markerArray['PRICE_SHIPPING_TAX']+$markerArray['PRICE_PAYMENT_TAX'];
		
		return $markerArray;
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_pi1.php']);
}

?>