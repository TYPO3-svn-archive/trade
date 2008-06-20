<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Steve Ryan (stever@syntithenai.com)
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

class tx_steverlist_flexformItems {

	function tableItems($config) {
	  for ($x = 1;  $x < 255; $x++ ){	    	    
	    $label = chr($x);
	    $config['items'][] = array ($label, $key);
	  }
	  return $config;
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/stever_list/class.tx_steverlist_flexformItems.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/stever_list/class.tx_steverlist_flexformItems.php']);
}
?>