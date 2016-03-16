<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}yuedu`;",	//收费订单
	"CREATE TABLE IF NOT EXISTS `{prefix}yuedu` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `cid` int(10) NOT NULL,
	  `modelid` smallint(5) NOT NULL,
	  `title` varchar(255) NOT NULL,
	  `userid` mediumint(8) NOT NULL,
	  `username` char(20) NOT NULL,
	  `addtime` int(10) NOT NULL COMMENT '订购时间',
	  `paytime` int(10) NOT NULL COMMENT '支付时间',
	  `price` decimal(10,2) NOT NULL COMMENT '价格',
	  `status` tinyint(1) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `userid` (`userid`),
	  KEY `addtime` (`addtime`),
	  KEY `cid` (`cid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
);