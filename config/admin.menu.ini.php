<?php
if (!defined('IN_IDEACMS')) exit();

/**
 * 管理后台菜单项
 */

return array (
    /**
	 * 顶部菜单
	 * 格式 array(
	 *          id => array ('name'=>'菜单(语言包)名称', 'url'=>'菜单地址', 'select'=>'选中左侧菜单id号' 'option'=>array(该菜单对应左侧子菜单的权限规则)),
	 *      )
	 */
    'top' => array (
        0 => array('name' => 'a-men-0', 'ico' => 'fa fa-home', 'url' => url('admin/index/main'), 'select' => '1',   'option' => ''),
				1 => array('name' => 'a-men-1', 'ico' => 'fa fa-cog', 'url' => url('admin/index/config'), 'select' => '101', 'option' => array('index-config', 'user-index', 'user-syn','auth-index','index-log','index-attack', 'ip-index')),
        2 => array('name' => 'a-men-90', 'ico' => 'fa fa-globe', 'url' => url('admin/site/config'), 'select' => '121', 'option' => array('site-config', 'site-index','category-index', 'model-index', 'attachment-index', 'position-index', 'relatedlink-index', 'block-index', 'linkage-index', 'form-index')),
        4 => array('name' => 'a-men-26', 'ico' => 'fa fa-list-ul', 'url' => url('admin/category/index'), 'select' => '201', 'option' =>'category-index'),
        9 => array('name' => 'a-men-2', 'ico' => 'fa fa-table', 'url' => '', 'select' => '0', 'option' => array('content-index')),
				6 => array('name' => 'a-men-4', 'ico' => 'fa fa-html5', 'url' => url('admin/html/index'), 'select' => '601', 'option' => array('html-index', 'html-cache')),
				3 => array('name' => 'a-men-5', 'ico' => 'fa fa-user', 'url' => url('admin/member/index'), 'select' => '301', 'option' => array('member-index', 'member-config', 'member-group', 'member-pms','member-extend')),
				5 => array('name' => 'a-men-7', 'ico' => 'fa fa-cubes', 'url' => url('admin/plugin/index'), 'select' => '501', 'option' => array('plugin-index')),
				8 => array('name' => 'a-men-10', 'ico'=>'fa fa-weixin', 'url' => url('admin/wx/config'), 'select' => '270', 'option' => array('weixin-config')),
            ),

	/**
	 * 顶部菜单对应的左侧菜单列表
	 * 格式 array(
	 *          顶部id => array (
	 *              '左侧菜单分组(语言包)名称' => array(
	 *                  '左侧菜单唯一标示' => array ('name'=>'菜单(语言包)名称', 'url'=>'菜单地址', 'option'=>'当前菜单的权限规则'),
	 *              ),
	 *          ),
	 *      )
	 */
	'list' => array(
	            0 => array(
					'a-men-93'  => array(
						1 => array('name' => 'a-men-8', 'url' => url('admin/index/main'), 'option' => ''),
						177 => array('name' => 'da003', 'url' => url('admin/index/bq'), 'option' => 'index-bq'),
						117 => array('name' => 'a-men-66', 'url' => url('admin/index/attack'), 'option' => 'index-attack'),
					),
				),
			    1 => array(
					'a-men-11' => array(
						101 => array('name' => 'a-men-11', 'url' => url('admin/index/config', array('type'=>1)),	'option' => 'index-config'),
						106 => array('name' => 'a-men-18', 'url' => url('admin/index/log'), 'option' => 'index-log'),
						118 => array('name' => 'a-men-67', 'url' => url('admin/ip/index'),  'option' => 'ip-index'),
						159 => array('name' => 'da004', 'url' => url('admin/sms/index'),    'option' => 'sms-index'),
					),
					'a-men-20' => array(
						109 => array('name' => 'a-men-22', 'url' => url('admin/user/index'), 'option' => 'user-index'),
						110 => array('name' => 'a-men-23', 'url' => url('admin/auth/index'), 'option' => 'auth-index'),
					),
					'a-men-25' => array(
						202 => array('name' => 'a-men-27', 'url' => url('admin/model/index'),        'option' => 'model-index'),
						701 => array('name' => 'a-men-60', 'url' => url('admin/model/index', array('typeid'=>3)), 'option' => 'model-index'),
					),
				),
				2 => array(
          'a-men-72' => array(
            121 => array('name' => 'a-men-12', 'url' => url('admin/site/config'),	'option' => 'site-config'),
            122 => array('name' => 'a-men-73', 'url' => url('admin/site/index'),	'option' => 'site-index'),
            203 => array('name' => 'a-men-28', 'url' => url('admin/attachment/index'),   'option' => 'attachment-index'),
                    ),


					'a-men-74' => array(
            204 => array('name' => 'a-men-35', 'url' => url('admin/content/updateurl/'), 'option' => 'content-updateurl'),
            289 => array('name' => 'a-men-30', 'url' => url('admin/block/index'),        'option' => 'block-index'),
						288 => array('name' => 'a-men-31', 'url' => url('admin/position/index'),     'option' => 'position-index'),
						287 => array('name' => 'a-men-32', 'url' => url('admin/tag/index'),          'option' => 'tag-index'),
						286 => array('name' => 'a-men-33', 'url' => url('admin/relatedlink/index'),  'option' => 'relatedlink-index'),
						285 => array('name' => 'a-men-34', 'url' => url('admin/linkage/index'),      'option' => 'linkage-index'),
					),
					'a-men-91' => array(),
					'a-men-47' => array(
						401 => array('name' => 'a-men-48', 'url' => url('admin/theme/index'), 'option' => 'theme-index'),
						402 => array('name' => 'a-men-71', 'url' => url('admin/theme/demo'),  'option' => 'theme-demo'),
						403 => array('name' => 'a-men-19', 'url' => url('admin/theme/cache'), 'option' => 'theme-cache'),
					)
				),
				3 => array(
					'a-men-36' => array(
						301 => array('name' => 'a-men-37',	'url' => url('admin/member/index'),                    'option' => 'member-index'),
						302 => array('name' => 'a-men-38',	'url' => url('admin/member/pms'),                      'option' => 'member-pms'),
						303 => array('name' => 'a-men-39',	'url' => url('admin/member/group'),                    'option' => 'member-group'),
						304 => array('name' => 'a-men-40',	'url' => url('admin/model/index', array('typeid'=>2)), 'option' => 'model-index'),
						311 => array('name' => 'a-mod-167', 'url' => url('admin/model/index', array('typeid'=>4)), 'option' => 'model-index'),
					),
					'a-mod-167' => array(

					),
					'a-men-41' => array(
						305 => array('name' => 'a-men-42', 'url' => url('admin/member/config', array('type'=>'user')),    'option' => 'member-confg'),
						306 => array('name' => 'a-men-43', 'url' => url('admin/member/config', array('type'=>'reg')),     'option' => 'member-confg'),
						307 => array('name' => 'a-men-44', 'url' => url('admin/member/config', array('type'=>'oauth')),   'option' => 'member-confg'),
						308 => array('name' => 'a-men-45', 'url' => url('admin/member/config', array('type'=>'email')),   'option' => 'member-confg'),
						309 => array('name' => 'a-men-46', 'url' => url('admin/member/config', array('type'=>'ucenter')), 'option' => 'member-confg'),
					)
				),

				4 => array(
					'a-men-26' => array(
						201 => array('name' => 'a-cat-12', 'url' => url('admin/category/index'),     'option' => 'category-index'),
						202 => array('name' => 'a-cat-13', 'url' => url('admin/category/add'),        'option' => 'category-add-index'),
					),
					),

				6 => array(

					'a-men-55' => array(
						601 => array('name' => 'a-men-50', 'url' => url('admin/html/index'),          'option' => 'html-index'),
						606 => array('name' => 'a-men-56', 'url' => url('admin/html/indexc'),	'option' => 'html-indexc'),
						607 => array('name' => 'a-men-57', 'url' => url('admin/html/category'),	'option' => 'html-category'),
						608 => array('name' => 'a-men-58', 'url' => url('admin/html/show'),		'option' => 'html-show'),
						609 => array('name' => 'a-men-70', 'url' => url('admin/html/form'),	    'option' => 'html-form'),
					),
					'a-men-49' => array(
						603 => array('name' => 'a-men-52', 'url' => url('admin/index/cache'),         'option' => 'index-cache'),
						605 => array('name' => 'a-men-54', 'url' => url('admin/content/updateurl/'),  'option' => 'content-updateurl'),
						602 => array('name' => 'a-men-51', 'url' => url('admin/html/clear'),          'option' => 'html-clear'),
						604 => array('name' => 'a-men-53', 'url' => url('admin/index/updatemap'),     'option' => 'index-updatemap'),
					),

		        ),
				5 => array(
				    'a-men-61' => array(
						501 => array('name' => 'a-men-61', 'url' => url('admin/plugin/index'), 'option' => 'plugin-index'),
					),
				),
				8 => array(
				    'a-men-10' => array(
						270 => array('name' => 'da019', 'url' => url('admin/wx/config'),    'option' => 'wx-config'),
						274 => array('name' => 'da020', 'url' => url('admin/wx/index'),    'option' => 'wx-index'),
						271 => array('name' => 'da021', 'url' => url('admin/wx/keyword'),    'option' => 'wx-keyword'),
						272 => array('name' => 'da023', 'url' => url('admin/wx/menu'),    'option' => 'wx-menu'),
						273 => array('name' => 'da024', 'url' => url('admin/wx/user'),    'option' => 'wx-user'),
					),
				),
			)
);
