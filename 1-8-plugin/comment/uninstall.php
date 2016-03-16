<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}comment`;",
    "DROP TABLE IF EXISTS `{prefix}comment_data`;"
);