<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}pay_account`;",	//充值记录
	"CREATE TABLE IF NOT EXISTS `{prefix}pay_account` (
	  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	  `order_sn` bigint(20) NOT NULL,
	  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
	  `username` char(20) NOT NULL,
	  `money` decimal(10,2) NOT NULL,
	  `addtime` bigint(10) NOT NULL DEFAULT '0',
	  `paytime` bigint(10) NOT NULL DEFAULT '0',
	  `paytype` char(10) NOT NULL,
	  `ip` char(15) NOT NULL DEFAULT '0.0.0.0',
	  `adminnote` char(20) NOT NULL,
	  `status` tinyint(3) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `order_sn` (`order_sn`),
	  KEY `userid` (`userid`),
	  KEY `status` (`status`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
	"DROP TABLE IF EXISTS `{prefix}pay_data`;",	//金额数据
	"CREATE TABLE IF NOT EXISTS `{prefix}pay_data` (
	  `userid` mediumint(8) NOT NULL,
	  `username` char(20) CHARACTER SET utf8 NOT NULL,
	  `freeze` decimal(10,2) NOT NULL,
	  `available` decimal(10,2) NOT NULL,
	  UNIQUE KEY `userid` (`userid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;",
	"DROP TABLE IF EXISTS `{prefix}pay_spend`;",	//消费记录
	"CREATE TABLE IF NOT EXISTS `{prefix}pay_spend` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
	  `username` varchar(20) NOT NULL,
	  `money` decimal(10,2) NOT NULL,
	  `addtime` bigint(10) NOT NULL,
	  `note` varchar(100) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `userid` (`userid`),
	  KEY `addtime` (`addtime`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
	"DROP TABLE IF EXISTS `{prefix}pay_card`;",	//充值卡
	"CREATE TABLE IF NOT EXISTS `{prefix}pay_card` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `card_sn` char(20) NOT NULL,
	  `password` char(6) NOT NULL,
	  `money` decimal(10,2) NOT NULL,
	  `status` tinyint(1) NOT NULL,
	  `adduser` char(20) NOT NULL,
	  `addtime` bigint(10) NOT NULL,
	  `endtime` bigint(10) NOT NULL,
	  `usertime` bigint(10) NOT NULL,
	  `userid` mediumint(8) NOT NULL,
	  `username` char(20) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `card_sn` (`card_sn`),
	  KEY `status` (`status`),
	  KEY `username` (`username`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
);
