<?php
/**
 * Created by 连普创想 Inc. http://www.lygphp.com.
 * User: frank  <976510651@qq.com>
 * Date: 2016/3/15
 * Time: 15:53
 */
if (!defined('IN_IDEACMS')) exit('No permission resources');

class NavigateController extends Admin{
    public function __construct() {
        parent::__construct();
        $this->navigate = $this->model('navigate');
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
    }

    public function indexAction(){
        if($this->post('submit_order') && $this->post('form')=='order'){
            foreach ($_POST as $var=>$value){
                if(strpos($var,'order_')!==false)
                {
                    $id = (int)str_replace('order_','',$var);
                    $this->navigate->update(array('listorder'=>$value), 'id=' . $id);
                }
            }

        }

        if ($this->post('submit_del') && $this->post('form')=='del') {
            foreach ($_POST as $var => $value) {
                if (strpos($var, 'del_')!==false) {
                    $id = (int)str_replace('del_','',$var);
                    $this->navigate->delete('id=' . $id);
                }
            }
        }

        $page = (int)$this->get('page');
        $page = (!$page) ? 1 : $page;
        //分页配置
        $pagelist = $this->instance('pagelist');
        $pagelist->loadconfig();
        $total = $this->navigate->count('navigate');
        $pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
        $url = purl('admin/navigate/index',array('page'=>'{page}'));
        $data = $this->navigate->page_limit($page, $pagesize)->order(array('listorder ASC', 'addtime DESC'))->select();
        $pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
        $this->view->assign(array(
            'list'     => $data,
            'pagelist' => $pagelist,
            ));
        $this->view->display('admin/navigate_list');

    }

    public function addAction(){
        if ($this->post('submit')) {
            $data = $this->post('data');
            if (!$data['name'] || !$data['url']) $this->adminMsg('名称和地址不能为空');
            $data['addtime'] = time();
            $this->navigate->insert($data);
            $this->adminMsg('操作成功',url('admin/navigate'), 3, 1, 1);
        }
        $this->view->display('admin/navigate_add');
    }

    public function editAction(){
        $id = $this->get('id');
        $data = $this->navigate->find($id);
        if (empty($data)) $this->adminMsg('此链接不存在');
        if ($this->post('submit')) {
            unset($data);
            $data = $this->post('data');
            if (!$data['name'] || !$data['url']) $this->adminMsg('名称或地址不能为空');
            $this->navigate->update($data, 'id=' . $id);
            $this->adminMsg('操作成功', url('admin/navigate'), 3, 1, 1);
        }
        $this->view->assign('data', $data);
        $this->view->display('admin/navigate_add');
    }

    public function delAction(){
        $id = $this->get('id');
        $this->navigate->delete('id=' . $id);
        $this->adminMsg('操作成功', url('admin/navigate'), 3, 1, 1);
    }




}
