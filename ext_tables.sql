#
# Table structure for table 'tx_trade_products'
#
CREATE TABLE tx_trade_products (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	subheader text NOT NULL,
	code tinytext NOT NULL,
	description text NOT NULL,
	category_uid blob NOT NULL,
	price1 tinytext NOT NULL,
	price2 tinytext NOT NULL,
	price3 tinytext NOT NULL,
	tax tinytext NOT NULL,
	image blob NOT NULL,
	weight tinytext NOT NULL,
	stock tinytext NOT NULL,
	manufacturer_id int(11) DEFAULT '0' NOT NULL,
	manufacturer tinytext NOT NULL,
	url tinytext NOT NULL,
	attributes tinytext NOT NULL,
	datasheet blob NOT NULL,
	special tinyint(3) DEFAULT '0' NOT NULL,
	other_products blob NOT NULL,
	viewcount tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_trade_categories'
#
CREATE TABLE tx_trade_categories (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	description text NOT NULL,
	image blob NOT NULL,
	parent blob NOT NULL,
	sorting int NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_trade_orders'
#
CREATE TABLE tx_trade_orders (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	feusers_uid blob NOT NULL,
	tracking_code tinytext NOT NULL,
	status int(11) DEFAULT '0' NOT NULL,
	order_data text NOT NULL,
	comment text NOT NULL,
	price_total_tax tinytext NOT NULL,
	total_items int(11) NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_trade_order_status'
#
CREATE TABLE tx_trade_order_status (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_trade_manufacturers'
#
CREATE TABLE tx_trade_manufacturers (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	image blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_trade_shipping_name tinytext NOT NULL,
	tx_trade_shipping_address tinytext NOT NULL,
	tx_trade_shipping_city tinytext NOT NULL,
	tx_trade_shipping_zip tinytext NOT NULL,
	tx_trade_shipping_zone tinytext NOT NULL,
	tx_trade_shipping_country tinytext NOT NULL,
	tx_trade_discount tinytext NOT NULL,
	tx_trade_wishlist blob NOT NULL,
	tx_trade_state tinytext NOT NULL
);