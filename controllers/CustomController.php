<?php

/**
 * CustomController.php 用户自定义控制器
 */

class CustomController extends Common {
    
    public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 示例控制器方法 test
	 * 访问地址 index.php?c=custom&a=test
	 * 调用地址 url('custom/test')
	 */
	public function testAction() {
	    /**
		 * --------------------------
		 *           程序区
		 * -------------------------
		 */
		
	    /**
		 * 变量赋值给模板
		 */
	    $this->view->assign(array(
			'meta_title' => '示例控制器方法（test）的网页标题',
	    ));
		/**
		 * 调用指定模板，不需要加扩展名
		 */
	    $this->view->display('index');
	}
	
	
	
	
}