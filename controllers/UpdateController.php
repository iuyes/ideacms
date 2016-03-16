<?php


class UpdateController extends Common {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 数据更新程序
     */
    public function indexAction() {

        $this->adminMsg('网站更新完成，请登陆后台更新全站缓存', '', 3, 1, 1);
        exit;
        $page = (int)$this->input->get('page');
        if (!$page) {
            $this->adminMsg(
                '正在为您清空全站数据...',
                iurl('update/index', array('page' => $page + 1)),
                3, 1, 2);
        }
        exit;
        switch($page) {
            case 1:

                // 删除表
                $_table = $this->db->query("SHOW TABLE STATUS FROM `{$this->db->database}`")->result_array();
                foreach ($_table as $t) {
                    if (strpos($t['Name'], $this->db->dbprefix) === 0) {
                        // 删除内容表
                        if (strpos($t['Name'], $this->db->dbprefix.'content_') === 0
                            && $t['Name'] != $this->db->dbprefix.'content_1') {
                            $this->db->query("DROP TABLE ".$t['Name']);
                        }
                        // 删除表单表
                        if (strpos($t['Name'], $this->db->dbprefix.'form_') === 0
                            ) {
                            $this->db->query("DROP TABLE ".$t['Name']);
                        }
                    }
                }
                // 删除内容
                $this->db->query('TRUNCATE `'.$this->db->dbprefix.'content_1`');
                $this->db->query('TRUNCATE `'.$this->db->dbprefix.'category`');
                $this->db->query('TRUNCATE `'.$this->db->dbprefix.'block`');
                $this->db->query('TRUNCATE `'.$this->db->dbprefix.'position`');
                $this->db->query('TRUNCATE `'.$this->db->dbprefix.'position_data`');
                // 删除站点
                $site = App::get_site();
                if (count($site) > 1) {
                    foreach ($site as $i => $t) {
                        if ($i != 1) {
                            @unlink(FCPATH.'config/site/'.$i.'.ini.php');
                        }
                    }
                }
                // 删除模型
                $data = $this->db->get('model')->result_array();
                if ($data) {
                    $mid = array();
                    foreach ($data as $t) {
                        if (strpos($t['tablename'], 'content_') === 0) {
                            // 内容模型
                            $mid[] = $t['modelid'];
                        } elseif (strpos($t['tablename'], 'form_') === 0) {
                            // 表单模型
                            $mid[] = $t['modelid'];
                        }
                    }
                    if ($mid) {
                        $this->db->where_in('modelid', $mid)->delete('model');
                        $this->db->where_in('modelid', $mid)->delete('model_field');
                    }
                }
                $this->adminMsg('正在导入默认数据...', iurl('update/index', array('page' => $page + 1)), 3, 1, 2);
                break;

            case 2:

                $this->adminMsg('正在导入默认数据...', iurl('update/index', array('page' => $page + 1)), 3, 1, 2);
                break;

            default:
                $this->adminMsg('网站更新完成，请登陆后台更新全站缓存', '', 3, 1, 1);
                break;
        }
    }
}