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

class tx_trade_div extends tslib_pibase {
	var $prefixId = 'tx_trade_div';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_trade_div.php';	// Path to this script relative to the extension dir.
	var $extKey = 'trade';	// The extension key.
	var $pi_checkCHash = TRUE;

	
	/***********************************************************************
	 * Session Handling Functions
	 **********************************************************************/
	function setSession($conf,$key,$val) {
		if ($conf['lockSessionToDomain']==1) {
			$domain=$GLOBALS['_SERVER']['HTTP_HOST'];
			$GLOBALS['_SESSION'][$domain]['tx_trade'][$key]=$val;	
		} else {
			$GLOBALS['_SESSION']['tx_trade'][$key]=$val;	
		}
	}	
	
	function getSession($conf,$key) {
		if ($conf['lockSessionToDomain']==1) {
			$domain=$GLOBALS['_SERVER']['HTTP_HOST'];
			return $GLOBALS['_SESSION'][$domain]['tx_trade'][$key];	
		} else {
			return $GLOBALS['_SESSION']['tx_trade'][$key];		
		}
	}	
	function removeSession($key) {
		unset($GLOBALS['_SESSION']['tx_trade'][$key]);	
	}
	
	function setUserCookie($val) {
		// one year expiry
		setcookie('tx_trade_pi_repeatuser',$val,time()+60*60*24*365)	;
	}
	
	function getUserCookie() {
		return $GLOBALS['_COOKIE']['tx_trade_pi_repeatuser'];
	}
	
	
	// order persistence - cookies or session. session has proved unreliable but should be more broadly implemented in browsers? Ahh well. COOKIES ARE REQUIRED
	function setCurrentOrder($val) {
		// one day expiry
		setcookie('tx_trade_pi_currentorder',$val,time()+60*60*24)	;
	}
	
	function getCurrentOrder() {
		//debug(array('get current order',$GLOBALS['_COOKIE']['tx_trade_pi_currentorder']));
		return $GLOBALS['_COOKIE']['tx_trade_pi_currentorder'];
	}
	
	function removeCurrentOrder()  {
		setcookie ("tx_trade_pi_currentorder", "", time() - 3600);
	}
	
	/**
	 * Determine if a bit is turned on in a given value.
	 */
	function isBitwiseOptionEnabled($option,$value) {
		$pos=($option-1);
		$pwer=pow(2,$pos);
		$res=(boolean)($value & $pwer);
		return $res;
	}
	
	/**
	 * Generate a random password with upper and lower case letters and up to one number
	 */
	function getRandomPassword() {
		$pass='';
		$j=3;
		for ($i=0; $i < 6; $i++) {
			$type=rand(1,$i);
			switch ($type) {
				// lowercase
				case 1:
					$pass.= chr(rand(97,122));
					break;
				// uppercase
				case 2:
					$pass.= chr(rand(65,90));
					break;
				// number
				case 3:
					$pass.= chr(rand(48,57));
					// just one number in password
					$j=2;
					break;
			}
		}
		return $pass;
	}
	
	/**
	 * Get a relative url from a string in the form EXT:trade/???
	 */
	function getFileName($val) {
		$parts=explode('/',$val);
		foreach ($parts as $pK => $pV) {
			if (substr($pV,0,4)=='EXT:') {
				$parts[$pK]=substr(t3lib_extMgm::extPath(substr($pV,4)),strlen(PATH_site));
				$parts[$pK]=substr($parts[$pK],0,strlen($parts[$pK])-1);
			}
		}
		$val2=implode('/',$parts);
		return $val2;
	}
	
	/**
	 * Calls user function defined in TypoScript
	 * This function was ripped from tt_products and modified to allow passing arbitrary additional data
	 *
	 * @param	integer		$mConfKey : if this value is empty the var $mConfKey is not processed
	 * @param	mixed		$passVar : this var is processed in the user function
	 * @param	mixed		$passString : if there is no value for the mConfKey, this value is returned
	 * @param	mixed		$callingObject : the instance of the trade_pi1 class that is calling this method
	 * @return	mixed		the processed $passVar
	 */
	function userProcess($mConfKey,&$passVar, $passString,&$callingObject) {
		if (strlen($this->conf['hooks.'][$mConfKey])>0) {
			//debug(array('user process',$mConfKey));
			$funcConf = $this->conf['hooks.'][$mConfKey . '.'];
			$funcConf['data'] = & $passVar;
			$funcConf['parentObj'] = & $callingObject;
			$passString = $GLOBALS['TSFE']->cObj->callUserFunction($this->conf['hooks.'][$mConfKey], $funcConf, $passString);
		}
		return $passString;
	}
	
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_div.php']);
}
?>