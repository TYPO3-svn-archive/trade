<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_trade_products');


t3lib_extMgm::addToInsertRecords('tx_trade_products');

$TCA["tx_trade_products"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:trade/locallang_db.xml:tx_trade_products',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_trade_products.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, title, subheader, code, description, category_uid, price1, price2, price3, tax, image, weight, stock, manufacturer_id, manufacturer, url, attributes, datasheet, special, other_products, viewcount",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_trade_categories');

$TCA["tx_trade_categories"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:trade/locallang_db.xml:tx_trade_categories',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_trade_categories.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, fe_group, title, description, image, parent",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_trade_orders');

$TCA["tx_trade_orders"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:trade/locallang_db.xml:tx_trade_orders',		
		'label'     => 'tracking_code',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_trade_orders.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, feusers_uid, tracking_code, status, order_data, comment, price_total_tax",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_trade_order_status');

$TCA["tx_trade_order_status"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:trade/locallang_db.xml:tx_trade_order_status',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_trade_order_status.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_trade_manufacturers');

$TCA["tx_trade_manufacturers"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:trade/locallang_db.xml:tx_trade_manufacturers',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_trade_manufacturers.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title, description, image",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(array('LLL:EXT:trade/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:trade/locallang_db.xml:tt_content.list_type_minibasket', $_EXTKEY.'_minibasket'),'list_type');

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:trade/flexform_ds_pi1.xml');

t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Trade");

$tempColumns = Array (
	"tx_trade_shipping_name" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_shipping_name",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_trade_shipping_address" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_shipping_address",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_trade_shipping_city" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_shipping_city",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_trade_shipping_zip" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_shipping_zip",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_trade_shipping_zone" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_shipping_zone",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_trade_shipping_country" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_shipping_country",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_trade_discount" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_discount",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_trade_wishlist" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_wishlist",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_trade_products",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_trade_state" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:trade/locallang_db.xml:fe_users.tx_trade_state",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_trade_shipping_name;;;;1-1-1, tx_trade_shipping_address, tx_trade_shipping_city, tx_trade_shipping_zip, tx_trade_shipping_zone, tx_trade_shipping_country, tx_trade_discount, tx_trade_wishlist, tx_trade_state");
?>