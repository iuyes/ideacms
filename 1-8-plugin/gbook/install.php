<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
  "CREATE TABLE IF NOT EXISTS `{prefix}gbook` (
  `id` smallint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `tel` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `r_name` varchar(100) NOT NULL,
  `r_content` text NOT NULL,
  `r_time` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"
);