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

class tx_trade_render extends tslib_pibase {
	var $prefixId = 'tx_trade_render';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_trade_render.php';	// Path to this script relative to the extension dir.
	var $extKey = 'trade';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $parent='';
	var $template='';
	var $cObj='';
	var $conf;
	var $markerArray=array(); 
	

	
	/***********************************************************************
	 * General Render Methods
	 **********************************************************************/
			
	/***********************************************************************
	 * This function initialises the parent object and main template file
	 **********************************************************************/ 
	function init(&$parent) {
		$this->loadTemplate($parent);
		// cache all session data (plus tweaks) in a marker array
		$this->markerArray=$this->parent->getGlobalMarkers($this->markerArray);
	}
	
	/***********************************************************************
	 * This function loads the contents of the configured templates into $this->template
	 **********************************************************************/ 
	 function loadTemplate(&$parent) {
	 	$this->parent=&$parent;
		$this->cObj=&$parent->cObj;
		$this->conf=&$parent->conf;
		// dont reload
	 	if (strlen($this->template)>0) return;
		// read template file
		$templateFiles=explode(",",$this->parent->conf['template']);
		$this->template="";
		if (strlen($this->conf['templateCache']>0)&&file_exists($this->conf['templateCache'])&&is_readable(($this->conf['templateCache']))) {
			$this->template=file_get_contents($this->conf['templateCache']);
		} 
		if (strlen(trim($this->template))==0) { 
			foreach ($templateFiles as $tK => $tV) {
				$templateFile=t3lib_div::getFileAbsFileName(trim($tV));
				$this->template.=file_get_contents($templateFile);
			}
			if (strlen($this->conf['templateCache'])>0) {
				$handle = fopen($this->conf['templateCache'], 'w');
				fwrite($handle,$this->template);
			}
		}
		//debug(array($this->template));
	}
	
	/***********************************************************************
	 * This function embeds content in a form (from main marker FORM_WRAPPER)
	 **********************************************************************/ 
	function renderFormWrap($content,$markers) {		
		// wrap in form with its own handfull of markers
		$template=$this->cObj->getSubpart($this->template,'FORM_WRAPPER');
		if (!is_array($markers)) $markers=array();
		$markers=t3lib_div::array_merge($this->parent->getFormMarkers(),$markers);
		$markers['CONTENT']=$content;
		// substite again to achieve wrap
		$ret=$this->cObj->substituteMarkerArray($template,$markers,'###|###',true)  ;	
		$ret=$this->cObj->substituteMarkerArray($ret,$markers,'###|###',true)  ;
		return $ret;
	} 	
	

	/****************************************
	 * This function renders $templateSection subpart from the main template
	 * and iterates through the nested templates in the standard format
	 * ITEM_CATEGORY_AND_ITEMS
	 *		ITEM_CATEGORY
	 *		ITEM_LIST
	 *			ITEM_SINGLE
	 * $listArray is the array of products sourced from a query or from the cart
	 * 
	 * Used by renderProductList, renderBasket, renderConfirm, renderThanks, email templates, ...
	 ****************************************/
	function renderCategorisedProductList($templateSection,$listArray) {
		$content='';
		// get template sections
		$itemListMainTemplate=$this->cObj->getSubpart($this->template,$templateSection);
		$itemListAndCategoryTemplate=$this->cObj->getSubpart($itemListMainTemplate,"ITEM_CATEGORY_AND_ITEMS");
		$itemCategoryTemplate=$this->cObj->getSubpart($itemListAndCategoryTemplate,"ITEM_CATEGORY");
		$itemListTemplate=$this->cObj->getSubpart($itemListAndCategoryTemplate,"ITEM_LIST");
		$itemSingleTemplate=$this->cObj->getSubpart($itemListTemplate,"ITEM_SINGLE");
		
		// was query performed ?
		/*if ($listArray[0]=='no_list')  {
			//debug('hide list '.$templateSection);
			// remove excess empty sections
			$itemListMainTemplate=$this->cObj->substituteSubpart($itemListMainTemplate,'###EMPTY###','&nbsp;',false)  ;			
			$list=$this->renderProductListItems(array(),$itemSingleTemplate,$itemListTemplate,$formMarkers);
		// are there any items in the list ?
		} else 
		*/
		if (sizeof($listArray)==0)  {
			//debug('empty list '.$templateSection);
			$content=$this->cObj->getSubpart($itemListMainTemplate,'###EMPTY###');
			return $content;
		} else if (sizeof($listArray)>0)  {
			//debug('full list '.$templateSection);
			// remove excess empty sections
			$itemListMainTemplate=$this->cObj->substituteSubpart($itemListMainTemplate,'###EMPTY###','&nbsp;',false)  ;
			
			// cache all categories
			$catOrder=$this->parent->conf['lists.'][$this->parent->listType.'.']['categoryOrderBy'];
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_categories','','',$catOrder,'');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$this->categories[$row['uid']]	= $row;
				$this->categoriesByParent[$row['parent']][]	= $row;
			}
			// load and iterate categories/items
			// load root level categories
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_categories','parent=0','',$catOrder,'');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$hasProducts=false;
				// show first level category heading
				$markerArray=$this->parent->getCategoryMarkers($row);
				$category=$this->cObj->substituteMarkerArray($itemCategoryTemplate,$markerArray,'###|###',true)  ;
				// show products for root category
				$categoryList=array();
				reset($listArray );
				foreach ($listArray as $lK => $lV) {
					if ($lV['category_uid']==$row['uid']) $categoryList[$lK]=$lV;
				}
				$header='';	
				if (sizeof($categoryList)>0) {
					$hasProducts=true;
					// form header replaces
					$list=$this->renderProductListItems($categoryList,$itemSingleTemplate,$itemListTemplate,$formMarkers);
					$tmp=$this->cObj->substituteSubpart($itemListAndCategoryTemplate,'###ITEM_LIST###',$list,false)  ;
					
				} else  {
					$tmp=$this->cObj->substituteSubpart($itemListAndCategoryTemplate,'###ITEM_LIST###','&nbsp;',false)  ;
				}
				$tmp=$this->cObj->substituteSubpart($tmp,'###ITEM_CATEGORY###',$category,false)  ;
				$header.=$tmp;
				// load second level categories
				$res2=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_trade_categories','parent='.$row['uid'],'','title ASC',''); 
				$inner='';
				while ($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
					// show second level category heading
					$markerArray=$this->parent->getCategoryMarkers($row2);
					$category=$this->cObj->substituteMarkerArray($itemCategoryTemplate,$markerArray,'###|###',true)  ;
					// show products for second level category
					$categoryList=array();
						reset($listArray );
						foreach ($listArray as $lK => $lV) {
							if ($lV['category_uid']==$row2['uid']) $categoryList[$lK]=$lV;
						}
						if (sizeof($categoryList)>0) {
							$hasProducts=true;
							$list=$this->renderProductListItems($categoryList,$itemSingleTemplate,$itemListTemplate,$formMarkers);
						
							// combine list and heading
							$tmp=$this->cObj->substituteSubpart($itemListAndCategoryTemplate,'###ITEM_CATEGORY###',$category,false)  ;
							$tmp=$this->cObj->substituteSubpart($tmp,'###ITEM_LIST###',$list,false)  ;
							$inner.=$tmp;
						}
				}
				if ($hasProducts) $content.=$header.$inner;			
			}
			// uncategorised products
			$categoryList=array();
			reset($listArray );
			foreach ($listArray as $lK => $lV) {
				if ($lV['category_uid']==$row['uid']) $categoryList[$lK]=$lV;
			}
			$list=$this->renderProductListItems($categoryList,$itemSingleTemplate,$itemListTemplate,$formMarkers);
		}
		// place combined into main template
		$content=$this->cObj->substituteSubpart($itemListMainTemplate,'ITEM_CATEGORY_AND_ITEMS',$content.$list,false)  ;
		return $content;		
	}
	
	/***********************************************************************
	 * This function iterates $list replacing and concatenating $itemSingleTemplate
	 **********************************************************************/ 
	function renderProductListItems($list,$itemSingleTemplate) {
		$listItems='';
		if (is_array($list)) {
			reset($list);
			foreach ($list as $clK => $clV) {
				$markerArray=$this->parent->getProductMarkers($clV);
				$tmp=$this->cObj->substituteMarkerArray($itemSingleTemplate,$markerArray,'###|###',true)  ;
				// ITEM_LINK SUBPART
				do {
					$itemLink=$this->cObj->getSubpart($tmp,'LINK_ITEM');
					//debug(array('link item',$itemLink));
					if (strlen(trim($itemLink))==0) break;
					//$itemLink='<a href="index.php?id='.$this->parent->PIDS['singleview']['uid'].'&tx_trade_pi1[cmd]=singleview&tx_trade_pi1[uid]='.$clV['uid'].'&tx_trade_pi1[backPID]='.$GLOBALS['TSFE']->id.'&tx_trade_pi1[listtype]='.$this->parent->listType.'" >'.$itemLink.'</a>';
					$linkConf['parameter']=$this->parent->PIDS['singleview']['uid'];
					$linkConf['additionalParams']='&tx_trade_pi1[cmd]=singleview&tx_trade_pi1[uid]='.$clV['uid'].'&tx_trade_pi1[backPID]='.$GLOBALS['TSFE']->id.'&tx_trade_pi1[listtype]='.$this->parent->listType;
					$itemLink=$this->cObj->typoLink($itemLink,$linkConf);
					$tmp=$this->cObj->substituteSubpart($tmp,'###LINK_ITEM###',$itemLink,false)  ;
				} while (true);
				
				
				$listItems.=$tmp;
			}
		}
		return $listItems;
	}
	
	function renderList($templateSection,$listArray) {//     $mainSection,$listSection,$mainListSection,$mainMarkers,$list,$extraListMarkers) {
		$content='';
		// get template sections
		$itemListMainTemplate=$this->cObj->getSubpart($this->template,$templateSection);
		$itemListTemplate=$this->cObj->getSubpart($itemListMainTemplate,"ITEM_LIST");
		$itemSingleTemplate=$this->cObj->getSubpart($itemListTemplate,"ITEM_SINGLE");

		// are there any items in the list ?
		if (!is_array($listArray)||sizeof($listArray)==0) {
			$content=$this->cObj->getSubpart($itemListMainTemplate,'###EMPTY###');
			return $content;
		}
		// remove excess empty sections
		$itemListMainTemplate=$this->cObj->substituteSubpart($itemListMainTemplate,'###EMPTY###','&nbsp;',false)  ;
		
		
		$list=$this->renderListItems($listArray,$itemSingleTemplate);
		//debug(array($list));
		// place combined into main template
		$content=$this->cObj->substituteSubpart($itemListMainTemplate,'ITEM_SINGLE',$list,false)  ;
		return $content;		
	}
		
	/***********************************************************************
	 * This function iterates $list replacing and concatenating $itemSingleTemplate
	 **********************************************************************/ 
	function renderListItems($list,$itemSingleTemplate) {
		$listItems='';
		if (is_array($list)) {
			reset($list);
			foreach ($list as $clK => $clV) {
				$markerArray=$this->parent->getOrderMarkers($clV);
				$markerArray=t3lib_div::array_merge($markerArray,$this->parent->getProductMarkers($clV));
				$markerArray=t3lib_div::array_merge($markerArray,$this->parent->getUserMarkers($clV));
				$tmp=$this->cObj->substituteMarkerArray($itemSingleTemplate,$markerArray,'###|###',true)  ;
				do {
					$itemLink=$this->cObj->getSubpart($tmp,'LINK_ITEM');
					if (strlen(trim($itemLink))==0) break;
					//$itemLink='<a href="index.php?id='.$this->parent->PIDS['singleview']['uid'].'&tx_trade_pi1[cmd]=singleview&tx_trade_pi1[uid]='.$clV['uid'].'&tx_trade_pi1[backPID]='.$GLOBALS['TSFE']->id.'&tx_trade_pi1[listtype]='.$this->parent->listType.'" >'.$itemLink.'</a>';
					$linkConf['parameter']=$this->parent->PIDS['singleview']['uid'];
					$linkConf['additionalParams']='&tx_trade_pi1[cmd]=singleview&tx_trade_pi1[uid]='.$clV['uid'].'&tx_trade_pi1[backPID]='.$GLOBALS['TSFE']->id.'&tx_trade_pi1[listtype]='.$this->parent->listType;
					$itemLink=$this->cObj->typoLink($itemLink,$linkConf);
					$tmp=$this->cObj->substituteSubpart($tmp,'###LINK_ITEM###',$itemLink,false)  ;
				} while (true);
				
				$listItems.=$tmp;
			}
		}
		return $listItems;
	}
	
	
	/***********************************************************************
	 * This function renders and arbitrary content record by $uid
	 ********************************************************************* 
	function renderContent($uid) {
		$table='tt_content';
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$table,' uid='.$uid,'','',1);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$lCObj=t3lib_div::makeInstance('tslib_cObj');
		$lCObj->setParent($this->cObj->data,$this->c0bj->currentRecord);
		$lCObj->start($row,$table);
		$content=$lCObj->cObjGetSingle($conf["renderObj"],$conf["renderObj."]);
		return $content;
	}
	*/
	
	
		
	/****************************************
	 * This function creates a shipping method selector
	 ****************************************/
	function renderShippingMethod() {
		// first payment input items
		$content='';
		$conf=$this->conf['shipping.'];
		if (sizeof($conf)==0) {
			debug('No shipping options configured. Critical Error.');
			exit;	
		}
		ksort($conf);	
		if ($conf['radio']==1) {
			// render as radio buttons
			foreach ($conf as $cK => $cV) {
				$cK=substr($cK,0,strlen($cK)-1);
				if ($cK>0) {
					if ($this->parent->shipping['method']==$cK)  {
						 $checked=' checked="checked" ';
					} else {
						$checked='';
					}
					if (strlen($cV['image.']['file'])>0) $image='<img src="'.tx_trade_div::getFileName($cV['image.']['file']).'" border="0" >';
					$content.='<input id="shippingmethod'.$cK.'" name="tx_trade_pi1[shipping][method]" onClick="document.myform.id.value='.$this->parent->PIDS['basket']['uid'].'; document.myform.cmd.value=\'basket\'; submit();" value="'.$cK.'" '.$checked.' type="radio"><label for="shippingmethod'.$cK.'"  >'.$cV['title'].'&nbsp;'.$image.'<label><br/>
					';
				}	
			}
		} else {
			// render as select	
			foreach ($conf as $cK => $cV) {
				$cK=substr($cK,0,strlen($cK)-1);
				if ($cK>0) {
					if ($this->parent->shipping['method']==$cK) {
						$checked=' selected="true" ';
					} else {
						$checked='';
					}
					if (strlen($cV['image.']['file'])>0&&strlen($checked)>0) $image='<img src="'.tx_trade_div::getFileName($cV['image.']['file']).'" border="0" >';
					$content.='<option value="'.$cK.'" '.$checked.' >'.$cV['title'].'</option>
					';
				}	
			}
			$content='<select  name="tx_trade_pi1[shipping][method]"  onChange="document.myform.id.value='.$this->parent->PIDS['basket']['uid'].'; document.myform.cmd.value=\'basket\'; submit();" >'.$content.'</select>&nbsp;'.$image;
		}
		return '<input name="tx_trade_pi1[submit_shipping_method]" value="1" type="hidden">'.$content;
	}
	
	/****************************************
	 * This function creates a payment method selector
	 ****************************************/
	function renderPaymentMethod() {
		// first payment input items
		$content='';
		$conf=$this->conf['payment.'];
		if (sizeof($conf)==0) {
			debug('No payment options configured. Critical Error.');
			exit;	
		}
		// remove excluded payment options
		if ($this->parent->shipping['method']>0) {
			if (strlen($this->conf['shipping.'][$this->parent->shipping['method'].'.']['excludePayment'])>0) {
				foreach (explode(",",$this->conf['shipping.'][$this->parent->shipping['method'].'.']['excludePayment']) as $eK => $eV) {
					unset($conf[$eV.'.']);	
				}	
			}
		} 
		if (sizeof($conf)==0) {
			debug('No payment options allowed after shipping excludePayment. Critical Error.');
			exit;	
		}
		$description='';
		ksort($conf);	
		if ($this->conf['payment.']['radio']==1) {
			// render as radio buttons
			foreach ($conf as $cK => $cV) {
				$cK=substr($cK,0,strlen($cK)-1);
				if ($cK>0) {
					if ($this->parent->payment['method']==$cK) {
						$checked=' checked="checked" ';
						$description=$this->conf['payment.'][$cK.'.']['description'];
					} else {
						$checked='';
					}
					if (strlen($cV['image.']['file'])>0) $image='<img src="'.tx_trade_div::getFileName($cV['image.']['file']).'" border="0" >';
					$content.='<input id="paymentmethod'.$cK.'" name="tx_trade_pi1[payment][method]"  onClick="document.myform.id.value='.$this->parent->PIDS['basket']['uid'].'; document.myform.cmd.value=\'basket\'; document.myform.submit();" value="'.$cK.'" '.$checked.' type="radio"><label for="paymentmethod'.$cK.'"  >'.$cV['title'].'&nbsp;'.$image.'</label><br/>
					'; 
				}	
			}
			$content.='<br/>'.$description;
		} else {
			// render as select	
			foreach ($conf as $cK => $cV) {
				$cK=substr($cK,0,strlen($cK)-1);
				if ($cK>0) {
					if ($this->parent->payment['method']==$cK) {
						$checked=' selected="true" ';
						$description=$this->conf['payment.'][$cK.'.']['description'];
					} else {
						$checked='';
					}
					if (strlen($cV['image.']['file'])>0&&strlen($checked)>0) $image='<img src="'.tx_trade_div::getFileName($cV['image.']['file']).'" border="0" >';
					$content.='<option value="'.$cK.'" '.$checked.' >'.$cV['title'].'</option>
					';
				}	
			}
			$content='<select  name="tx_trade_pi1[payment][method]"  onChange="document.myform.id.value='.$this->parent->PIDS['basket']['uid'].'; document.myform.cmd.value=\'basket\'; submit();" >'.$content.'</select>&nbsp;'.$image.'<br/>'.$description;
		}
		return '<input name="tx_trade_pi1[submit_payment_method]" value="1" type="hidden">'.$content;
	} 
	
	/****************************************
	 * Render a template section
	 ****************************************/
	function renderSectionNoWrap($section) {
		$section=strtoupper($section.'_TEMPLATE');
		$content=$this->cObj->getSubpart($this->template,strtoupper($section));
		if (strlen(trim($content))==0) debug(array('template not found',$section));
		$catTemplate=$this->cObj->getSubpart($content,'ITEM_CATEGORY_AND_ITEMS');
		$listTemplate=$this->cObj->getSubpart($content,'ITEM_LIST');
		// does the template have a section ITEM_CATEGORY_AND_ITEMS
		if (strlen($catTemplate)>0) {
			if (strpos($section,'_LIST')>0) {
			    $whichList=$this->parent->list;
				if (!is_array($whichList)) $whichList=array();
				foreach($whichList as $lK => $lV) {
					if (sizeof($this->parent->basket[$lV['uid']])>0) {
						$whichList[$lK]['basket_qty']=$this->parent->basket[$lV['uid']]['basket_qty'];
					}
				}
				reset($whichList);
			} else {
			    $whichList=$this->parent->basket;
			}
			$content=$this->renderCategorisedProductList($section,$whichList);
		// does the template have a section ITEM_LIST	
		} else if (strlen($listTemplate)>0)  {
			// get the list of orders using the single view subpart
			$content=$this->renderList($section,$this->parent->list);
		// otherwise do single render		
		} else {
			$content=$this->renderComponent($section);
			
		}	
		// do all replacements of single constants
		$content=$this->cObj->substituteMarkerArray($content,$this->markerArray,'###|###',true)  ;	
		// and again to replace field values in subtemplates that were replaced last round
		$content=$this->cObj->substituteMarkerArray($content,$this->markerArray,'###|###',true)  ;
		$content=$this->cObj->substituteMarkerArray($content,$this->markerArray,'###|###',true)  ;			
		return $content;
	}
	
	/****************************************
	 * Render a template section and wrap in the form template
	 ****************************************/
	function renderSection($section) {
		$content=$this->renderFormWrap($this->renderSectionNoWrap($section),$this->markerArray);
		if (strlen(trim($content))==0) debug(array('could not find section ',$section,' in your HTML template. Please ensure this template subpart is available. ','Also ensure that there are no conflicting markers'));
		return $content;
	}
	
	/****************************************
	 * Return a template subpart
	 ****************************************/
	function renderComponent($section) {
		$content=$this->cObj->getSubpart($this->template,strtoupper($section));
		if (strlen(trim($content))==0) debug(array('could not find component ',$section,' in your HTML template. Please ensure this template subpart is available. ','Also ensure that there are no conflicting markers'));
		return $content;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_render.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/trade/pi1/class.tx_trade_render.php']);
}

?>
