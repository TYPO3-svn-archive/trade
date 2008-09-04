<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_trade_products=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_trade_categories=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_trade_orders=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_trade_order_status=1
');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_trade_pi1.php','_pi1','list_type',0);
//t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_trade_minibasket.php','_minibasket','list_type',0);
?>