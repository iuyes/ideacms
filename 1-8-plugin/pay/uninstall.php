<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    "DROP TABLE IF EXISTS `{prefix}pay_account`;",
    "DROP TABLE IF EXISTS `{prefix}pay_data`;",
    "DROP TABLE IF EXISTS `{prefix}pay_spend`;",
    "DROP TABLE IF EXISTS `{prefix}pay_card`;"
);