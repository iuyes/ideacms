<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = '用您给出的连接字符串无法获得数据库设置.';
$lang['db_unable_to_connect'] = '使用给出的设置无法连接数据库.';
$lang['db_unable_to_select'] = '无法选择指定的数据库: %s';
$lang['db_unable_to_create'] = '无法创建指定的数据库: %s';
$lang['db_invalid_query'] = '查询无效.';
$lang['db_must_set_table'] = '查询中必须设置数据库表.';
$lang['db_must_use_set'] = '必须使用"set"命令更新记录.';
$lang['db_must_use_index'] = '您必须为批量更新指定一个索引.';
$lang['db_batch_missing_index'] = '批量更新中的一个或多个记录缺少指定的索引.';
$lang['db_must_use_where'] = '更新记录时必须包含"where"子句.';
$lang['db_del_must_use_where'] = '删除记录时必须包含"where"或"like"子句.';
$lang['db_field_param_missing'] = '必须提供表名参数才能取得字段信息.';
$lang['db_unsupported_function'] = '您使用的数据库不支持此功能.';
$lang['db_transaction_failure'] = '事务失败: 已回滚.';
$lang['db_unable_to_drop'] = '无法删除数据库.';
$lang['db_unsupported_feature'] = '数据库平台不支持此功能.';
$lang['db_unsupported_compression'] = '不支持此文件压缩格式.';
$lang['db_filepath_error'] = '您指定的文件路径无法写入数据.';
$lang['db_invalid_cache_path'] = '缓存路径无效或不可写.';
$lang['db_table_name_required'] = '必须提供表名.';
$lang['db_column_name_required'] = '必须提供字段名.';
$lang['db_column_definition_required'] = '必须提供字段定义.';
$lang['db_unable_to_set_charset'] = '无法设置客户端连接的字符集: %s';
$lang['db_error_heading'] = '发生了一个数据库错误';

/* End of file db_lang.php */
/* Location: ./system/language/chinese/db_lang.php */