<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}shop_address`;",
	"CREATE TABLE IF NOT EXISTS `{prefix}shop_address` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) NOT NULL,
  `username` char(20) NOT NULL,
  `name` char(20) NOT NULL,
  `address` varchar(200) NOT NULL,
  `zip` char(6) NOT NULL,
  `tel` char(20) NOT NULL,
  `default_value` tinyint(1) NOT NULL COMMENT '默认地址',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
    "DROP TABLE IF EXISTS `{prefix}shop_order`;",
	"CREATE TABLE IF NOT EXISTS `{prefix}shop_order` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `order_sn` bigint(20) NOT NULL COMMENT '订单编号',
  `userid` mediumint(8) NOT NULL,
  `username` char(20) NOT NULL,
  `items` text NOT NULL COMMENT '商品信息array格式',
  `addtime` int(10) NOT NULL COMMENT '订购时间',
  `paytime` int(10) NOT NULL COMMENT '支付时间',
  `sendtime` int(10) NOT NULL COMMENT '发货时间',
  `confirmtime` int(10) NOT NULL COMMENT '确认时间',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `shipping_id` char(20) NOT NULL COMMENT '运单编号',
  `shipping_name` varchar(20) NOT NULL COMMENT '物流名称',
  `shipping_price` decimal(5,2) NOT NULL COMMENT '物流价格',
  `name` varchar(20) NOT NULL COMMENT '收货人',
  `address` varchar(200) NOT NULL COMMENT '收货地址',
  `zip` char(6) NOT NULL COMMENT '邮政编码',
  `tel` char(20) NOT NULL COMMENT '联系电话',
  `status` tinyint(1) NOT NULL,
  `adminlog` text NOT NULL COMMENT '操作日志',
  `note` varchar(200) NOT NULL COMMENT '备注信息',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`),
  KEY `userid` (`userid`),
  KEY `addtime` (`addtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
    "DROP TABLE IF EXISTS `{prefix}shop_shipping`;",
	"CREATE TABLE IF NOT EXISTS `{prefix}shop_shipping` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '物流名称',
  `price` decimal(5,2) NOT NULL COMMENT '运送价格',
  `description` text NOT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"
);