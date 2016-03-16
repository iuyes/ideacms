<?php
if (!defined('IN_IDEACMS')) exit();

/**
 * GET和POST非法字符过滤配置（防非法字符攻击）
 */
 
return array(

    /*
	 * GET参数非法字符过滤
	 */
	 
    'get'  => array(
		'select ',
		'insert ',
		'\'',
		'/*',
		'*',
		'../',
		'..\\',
		'union ',
		'into ',
		'load_file(',
		'outfile ',
		'<script',
	),
	
	/*
	 * POST值非法字符过滤
	 */
	
	'post' => array(
		'<script',
		'<style',
		'<meta',
	),
);