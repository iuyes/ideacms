<?php
if (!defined('IN_IDEACMS')) exit();
return array (
  'types' => 
  array (
    'id' => 'smallint unsigned',
    'aid' => 'smallint',
    'typeid' => 'tinyint',
    'name' => 'char',
    'setting' => 'text',
    'logo' => 'varchar',
    'startdate' => 'int',
    'enddate' => 'int',
    'addtime' => 'int',
    'clicks' => 'int',
    'disabled' => 'tinyint unsigned',
    'listorder' => 'tinyint',
  ),
  'fields' => 
  array (
    0 => 'id',
    1 => 'aid',
    2 => 'typeid',
    3 => 'name',
    4 => 'setting',
    5 => 'logo',
    6 => 'startdate',
    7 => 'enddate',
    8 => 'addtime',
    9 => 'clicks',
    10 => 'disabled',
    11 => 'listorder',
  ),
  'primary_key' => 'id',
);