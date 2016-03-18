<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}digg`;",
    "CREATE TABLE IF NOT EXISTS `{prefix}digg` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `contentid` mediumint(8) NOT NULL,
  `title` varchar(100) NOT NULL,
  `cai` mediumint(8) NOT NULL,
  `ding` mediumint(8) NOT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"
);