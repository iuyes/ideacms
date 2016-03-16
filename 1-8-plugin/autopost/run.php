<?php
$cache = new cache_file('autopost');
$post  = $cache->get('time');
$site  = (int)App::get_site_id();
$model = Controller::model('content_' . $site);
$end   = $end_time   ? $end_time   : 10;
$start = $start_time ? $start_time : 8;
if (empty($post) || ($post != date('Y-m-d') && date('H') >= $start && date('H') <= $end)) {
    //判断今天是否已经发布过，且在指定时间内 
	$category = $model->from('category')->where('site=' . $site . ' and child=0 and typeid=1')->select();
	foreach ($category as $cat) {
		if ($nums) {
			$offset = explode(',', $nums);
			$limit  = rand((int)$offset[0], (int)$offset[1]);
		} else {
			$limit  = 10;
		}
		$data = $model->where('`status`=0 and catid=' . $cat['catid'])->order('inputtime ASC')->limit(0, $limit)->select();
		if ($data) {
			foreach ($data as $t) {
				$time = strtotime(date('Y-m-d ') . rand($start, $end) . ':' . rand(0, 59) . ':00'); //随机时间
				$set  = array(
					'inputtime'  => $time, 
					'updatetime' => $time, 
					'status'     => 1
				);
				$model->update($set, 'id=' . $t['id']);
				if (function_exists('sitemap_xml')) {
				    sitemap_xml();
				} else {
				    sitemap();
				}
			} 
		}
	}
	$cache->set('time', date('Y-m-d'));
    unset($start, $end, $nums, $where, $count, $limit, $cache);
}