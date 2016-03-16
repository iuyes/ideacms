<?php

class TagModel extends Model {

    public function get_primary_key() {
        return $this->primary_key = 'id';
    }

    public function get_fields() {
        return $this->get_table_fields();
    }

	public function getList($num, $cache) {
		return $this->order('listorder DESC,id DESC')->limit($num)->select(true, $cache);
	}

	public function getData($kw) {
		return $this->where('letter=?', $kw)->select();
	}

	public function listData($tag, $where, $cache) {
		$data = $this->from('tag_cache')->where('tag=?', $tag)->select(false);
		$data = empty($data) ? $this->addData($tag, $where) : $data;
		if (empty($data)) return false;
	    return time() - $data['addtime'] > $cache ? $this->updateData($data['id'], $where) : $data;
	}

	private function addData($tag, $where) {
	    $data = $this->execute('SELECT id FROM ' . $this->prefix . 'content_' . App::get_site_id() . ' WHERE ' . $where, true);
		if (empty($data)) return false;
		$total= count($data);
		$ids  = '';
		foreach ($data as $t) { $ids .= $t['id'] . ','; }
		$ids  = substr($ids, -1) == ',' ? substr($ids, 0, -1) : $ids;
		$data = array(
		    'params'  => md5($where),
			'addtime' => time(),
			'total'   => $total,
			'tag'     => $tag,
			'sql'     => 'SELECT * FROM ' . $this->prefix . 'content_' . App::get_site_id() . ' WHERE `id` IN (' . $ids . ') ORDER BY `updatetime` DESC',
		);
		$this->set_table_name('tag_cache');
		return $this->insert($data) ? $data : false;
	}

	private function updateData($id, $where) {
	    $data = $this->execute('SELECT id FROM ' . $this->prefix . 'content_' . App::get_site_id() . ' WHERE ' . $where, true);
		$this->set_table_name('tag_cache');
		if (empty($data)) {
		    $this->delete('id=' . $id);
		    return false;
		}
		$total= count($data);
		$ids  = '';
		foreach ($data as $t) {  $ids .= $t['id'] . ','; }
		$ids  = substr($ids, -1) == ',' ? substr($ids, 0, -1) : $ids;
		$data = array(
			'addtime' => time(),
			'total'   => $total,
			'sql'     => 'SELECT * FROM ' . $this->prefix . 'content_' . App::get_site_id() . ' WHERE `id` IN (' . $ids . ') ORDER BY `updatetime` DESC',
		);
		$this->update($data, 'id=' . $id, NULL);
		return $data;
	}

    public function update($data, $where, $params)
    {

        parent::update($data,$where,$params);
    }

}
