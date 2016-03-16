<?php
if (!defined('IN_IDEACMS')) exit();

/**
 * 模块权限配置信息
 */
return array(
    'index' => array(
	    'name'   =>'a-men-1', 
		'option' => array(
		    'config'      => 'a-men-11',
			'cache'       => 'a-men-19',
			'log'         => 'a-ind-43',
			'clearlog'    => 'a-ind-44',
			'updatemap'   => 'a-aut-12',
			'clearattack' => 'a-aut-26',
			'attack'      => 'a-men-66',
		)
    ),
	
	'site' => array(
		'name'   => 'a-men-72',
		'option' => array(
			'index'	 => 'a-men-73',
			'config' => 'a-sit-7',
			'add'	 => 'a-add', 
			'edit'   => 'a-edit', 
			'del'    => 'a-del', 
		)
	),
    
    'auth' => array(
	    'name'   => 'a-aut-13', 
		'option' => array(
		    'index' => 'a-list', 
			'add'   => 'a-add', 
			'edit'  => 'a-edit', 
			'del'   => 'a-del', 
			'list'  => 'a-aut-9', 
			'cache' => 'a-cache'
		)
    ),
    
    'user' => array(
	    'name'   =>'a-men-22', 
		'option' => array(
		    'index' => 'a-list', 
			'add'   => 'a-add', 
			'edit'  => 'a-edit', 
			'del'   => 'a-del'
		)
    ),
    
    'model' => array(
	    'name'   => 'a-aut-14', 
		'option' => array(
		    'index'     => 'a-list',
			'add'       => 'a-add', 
			'edit'      => 'a-edit', 
			'del'       => 'a-del', 
			'import'    => 'a-aut-15', 
			'export'    => 'a-aut-16', 
			'cdisabled' => 'a-aut-17', 
			'fields'    => 'a-aut-18', 
			'addfield'  => 'a-aut-19', 
			'editfield' => 'a-aut-20', 
			'delfield'  => 'a-aut-21', 
			'disable'   => 'a-aut-22', 
			'cache'     => 'a-cache'
		)
    ),
    
    'category'   => array(
	    'name'   =>'a-men-26', 
		'option' => array(
		    'index' => 'a-list',
			'add'   => 'a-add',
			'edit'  => 'a-edit',
			'del'   => 'a-del',
			'url'   => 'a-cat-14',
			'cache' => 'a-cache'
		)
    ),
    
    'content' => array(
	    'name'   => 'a-men-29', 
		'option' => array(
		    'index'			=> 'a-list', 
			'add'			=> 'a-add', 
			'edit'			=> 'a-edit',
			'del'			=> 'a-del',
			'verify'		=> 'a-mod-138',
			'editverify'	=> 'a-mod-142',
			'delverify'		=> 'a-mod-141', 
			'updateurl'		=> 'a-men-35'
		)
    ),
    
    'position' => array(
	    'name'   => 'a-men-31', 
		'option' => array(
		    'index'    => 'a-list', 
			'add'      => 'a-add', 
			'edit'     => 'a-edit',
			'del'      => 'a-del', 
			'list'     => 'a-pos-0', 
			'adddata'  => 'a-pos-1', 
			'editdata' => 'a-aut-24', 
			'deldata'  => 'a-aut-23', 
			'cache'    => 'a-cache'
		)
    ),
    
    'theme' => array(
	    'name'   => 'a-men-6', 
		'option' => array(
		    'index' => 'a-list', 
			'add'   => 'a-add', 
			'edit'  => 'a-edit', 
			'del'   => 'a-del', 
			'cache' => 'a-cache'
		)
    ),
    
    'plugin' => array(
	    'name'   => 'a-men-7', 
		'option' => array(
		    'index'   => 'a-list', 
			'set'     => 'a-plu-0', 
			'disable' => 'a-aut-25', 
			'del'     => 'a-plu-1', 
			'unlink'  => 'a-del', 
			'add'     => 'a-add'
		)
    ),
    
    'attachment' => array(
	    'name'   => 'a-men-28', 
		'option' => array(
		    'index' => 'a-list', 
			'del'   => 'a-del'
		)
    ),
	
	'tag' => array(
	    'name'   => 'a-men-32', 
		'option' => array(
		    'index'  => 'a-list', 
			'add'    => 'a-add', 
			'edit'   => 'a-edit', 
			'del'    => 'a-del', 
			'import' => 'a-aut-15', 
			'cache' => 'a-cache'
		)
    ),
    
    'relatedlink' => array(
	    'name'   => 'a-men-33', 
		'option' => array(
		    'index'  => 'a-list', 
			'add'    => 'a-add', 
			'edit'   => 'a-edit', 
			'del'    => 'a-del', 
			'import' => 'a-aut-15', 
			'cache' => 'a-cache'
		)
    ),
    
    'block' => array(
	    'name'   => 'a-men-30', 
		'option' => array(
		    'index' => 'a-list', 
			'add'   => 'a-add', 
			'edit'  => 'a-edit', 
			'del'   => 'a-del', 
			'cache' => 'a-cache'
		)
    ),
	
	'sms' => array(
	    'name'   => 'a-men-67', 
		'option' => array(
		    'index' => 'a-list', 
			'add'   => 'a-add',
			'cache' => 'a-cache'
		)
    ),
	'ip' => array(
	    'name'   => 'da004',
		'option' => array(
		    'index' => 'a-list',
			'add'   => 'a-add',
			'edit'  => 'a-edit',
			'cache' => 'a-cache'
		)
    ),
	
	'member' => array(
	    'name'   => 'a-men-5', 
		'option' => array(
		    'index'  => 'a-list',
			'reg'    => 'a-mem-0',
			'edit'   => 'a-edit',
			'del'    => 'a-del',
			'config' => 'a-men-41',
			'group'  => 'a-men-39',
			'extend' => 'a-mod-167',
			'pms'    => 'a-men-38',
			'cache'  => 'a-cache'
		)
    ),
	
	'linkage' => array(
	    'name'   => 'a-men-34', 
		'option' => array(
		    'index'   => 'a-list', 
			'add'     => 'a-add', 
			'edit'    => 'a-edit', 
			'del'     => 'a-del', 
			'list'    => 'a-lin-0', 
			'addson'  => 'a-add', 
			'editson' => 'a-edit',
			'cache'   => 'a-cache'
		)
    ),
	
	'html' => array(
	    'name'   => 'a-men-4',
		'option' => array(
		    'index'    => 'a-men-50',
			'indexc'   => 'a-men-56',
			'category' => 'a-men-57',
			'show'     => 'a-men-58',
			'form'	   => 'a-men-70',
			'clear'    => 'a-men-51'
		)
    ),
	
	'form' => array(
	    'name'   => 'a-men-3', 
		'option' => array(
		    'index'  => 'a-list', 
			'list'   => 'a-list', 
			'config' => 'a-for-0',
			'add'    => 'a-add', 
			'edit'   => 'a-edit', 
			'del'    => 'a-del'
		)
    ),
);