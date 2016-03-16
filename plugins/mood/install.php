<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}mood`;",
"CREATE TABLE IF NOT EXISTS `{prefix}mood` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总数',
  `n1` int(10) unsigned NOT NULL DEFAULT '0',
  `n2` int(10) unsigned NOT NULL DEFAULT '0',
  `n3` int(10) unsigned NOT NULL DEFAULT '0',
  `n4` int(10) unsigned NOT NULL DEFAULT '0',
  `n5` int(10) unsigned NOT NULL DEFAULT '0',
  `n6` int(10) unsigned NOT NULL DEFAULT '0',
  `n7` int(10) unsigned NOT NULL DEFAULT '0',
  `n8` int(10) unsigned NOT NULL DEFAULT '0',
  `n9` int(10) unsigned NOT NULL DEFAULT '0',
  `n10` int(10) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `contentid` (`contentid`),
  KEY `total` (`total`),
  KEY `lastupdate` (`lastupdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
);