<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_trade_products"] = Array (
	"ctrl" => $TCA["tx_trade_products"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,title,subheader,code,description,category_uid,price1,price2,price3,tax,image,weight,stock,manufacturer,url,attributes,datasheet,special,other_products"
	),
	"feInterface" => $TCA["tx_trade_products"]["feInterface"],
	"columns" => Array (
		'sys_language_uid' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_trade_products',
				'foreign_table_where' => 'AND tx_trade_products.pid=###CURRENT_PID### AND tx_trade_products.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array (		
			'config' => Array (
				'type' => 'passthrough'
			)
		),
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"subheader" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.subheader",		
			"config" => Array (
				"type" => "text",
				"cols" => "48",	
				"rows" => "3",
			)
		),
		"code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "12",
			)
		),
		"description" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"category_uid" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.category_uid",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_trade_categories",	
				"foreign_table_where" => "AND tx_trade_categories.pid=###STORAGE_PID### ORDER BY tx_trade_categories.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"price1" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.price1",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"price2" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.price2",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"price3" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.price3",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"tax" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.tax",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"image" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_trade",
				"show_thumbs" => 1,	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 3,
			)
		),
		"weight" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.weight",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"stock" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.stock",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"manufacturer" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.manufacturer",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"url" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.url",		
			"config" => Array (
				"type" => "input",
				"size" => "15",
				"max" => "255",
				"checkbox" => "",
				"eval" => "trim",
				"wizards" => Array(
					"_PADDING" => 2,
					"link" => Array(
						"type" => "popup",
						"title" => "Link",
						"icon" => "link_popup.gif",
						"script" => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					)
				)
			)
		),
		"attributes" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.attributes",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"datasheet" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.datasheet",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => 'doc,pdf,xls,sxw,odt,ppt,zip,html',	
				"max_size" => 5000,	
				"uploadfolder" => "uploads/tx_trade",
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 20,
			)
		),
		"special" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.special",		
			"config" => Array (
				"type" => "check",
			)
		),
		"other_products" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_products.other_products",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_trade_products",	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 10,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, subheader;;;;3-3-3, code, description;;;richtext[*]:rte_transform[mode=ts], category_uid, price1, price2, price3, tax, image, weight, stock, manufacturer, url, attributes, datasheet, special, other_products")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_trade_categories"] = Array (
	"ctrl" => $TCA["tx_trade_categories"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,fe_group,title,description,image,parent"
	),
	"feInterface" => $TCA["tx_trade_categories"]["feInterface"],
	"columns" => Array (
		'sys_language_uid' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_trade_categories',
				'foreign_table_where' => 'AND tx_trade_categories.pid=###CURRENT_PID### AND tx_trade_categories.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array (		
			'config' => Array (
				'type' => 'passthrough'
			)
		),
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_categories.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"description" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_categories.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"image" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_categories.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_trade",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"parent" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_categories.parent",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_trade_categories",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, description;;;richtext[*]:rte_transform[mode=ts];3-3-3, image, parent")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "fe_group")
	)
);



$TCA["tx_trade_orders"] = Array (
	"ctrl" => $TCA["tx_trade_orders"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,feusers_uid,tracking_code,status,order_data,comment,price_total_tax"
	),
	"feInterface" => $TCA["tx_trade_orders"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"feusers_uid" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_orders.feusers_uid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"tracking_code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_orders.tracking_code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"status" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_orders.status",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_trade_order_status",	
				"foreign_table_where" => "ORDER BY tx_trade_order_status.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"order_data" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_orders.order_data",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"comment" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_orders.comment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"price_total_tax" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_orders.price_total_tax",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, feusers_uid, tracking_code, status, order_data;;;richtext[*], comment, price_total_tax")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_trade_order_status"] = Array (
	"ctrl" => $TCA["tx_trade_order_status"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title"
	),
	"feInterface" => $TCA["tx_trade_order_status"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:trade/locallang_db.php:tx_trade_order_status.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>