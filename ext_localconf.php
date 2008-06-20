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

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_trade_pi1 = < plugin.tx_trade_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_trade_pi1.php','_pi1','list_type',0);
?>