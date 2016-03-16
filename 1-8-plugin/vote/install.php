<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}vote`;",
	"CREATE TABLE IF NOT EXISTS `{prefix}vote` (
	  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	  `subject` char(255) NOT NULL,
	  `options` text NOT NULL,
	  `ischeckbox` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
	  `votedata` text NOT NULL,
	  `votenums` mediumint(8) NOT NULL,
	  `description` text NOT NULL,
	  `status` tinyint(1) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"
);