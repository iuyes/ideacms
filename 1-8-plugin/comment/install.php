<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}comment`;",
	"CREATE TABLE IF NOT EXISTS `{prefix}comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) NOT NULL,
  `contentid` mediumint(8) NOT NULL DEFAULT '0',
  `title` char(255) NOT NULL,
  `total` int(8) unsigned DEFAULT '0',
  `lastupdate` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lastupdate` (`lastupdate`),
  KEY `contentid` (`contentid`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
    "DROP TABLE IF EXISTS `{prefix}comment_data`;",
	"CREATE TABLE IF NOT EXISTS `{prefix}comment_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论数据ID',
  `commentid` int(10) NOT NULL COMMENT '评论ID',
  `contentid` int(10) NOT NULL,
  `userid` int(10) unsigned DEFAULT '0' COMMENT '用户ID',
  `username` varchar(20) DEFAULT NULL COMMENT '用户名',
  `addtime` int(10) DEFAULT NULL COMMENT '发布时间',
  `ip` varchar(15) DEFAULT NULL COMMENT '用户IP地址',
  `status` tinyint(1) DEFAULT '0' COMMENT '评论状态{0:未审核,1:通过审核}',
  `content` text COMMENT '评论内容',
  `support` mediumint(8) unsigned DEFAULT '0' COMMENT '支持数',
  `opposition` smallint(8) NOT NULL COMMENT '反对',
  `reply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为回复',
  `lasttime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `support` (`support`),
  KEY `commentid` (`commentid`),
  KEY `opposition` (`opposition`),
  KEY `userid` (`userid`),
  KEY `lasttime` (`lasttime`),
  KEY `contentid` (`contentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"
);