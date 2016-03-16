<?php include $this->_include('header.html'); ?>
<form action="" method="post" name="myform" id="myform">
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
        <a href="<?php echo url('admin/wx/config'); ?>" class="on"><em>微信配置</em></a><span>|</span>
        <a href="tencent://message/?uin=976510651" target="_blank"><em>定制应用</em></a>
	</div>
	<div class="bk10"></div>
	<div class="table-list col-tab">
		<div class="contentList pad-10">
            <table width="100%" class="table_form">
            <tr class="i0">
                <th width="200"><font color="red">*</font>&nbsp;URL： </th>
                <td>
					<?php echo SITE_URL; ?>index.php?c=wx
                </td>
            </tr>
            <tr class="i0">
                <th><font color="red">*</font>&nbsp;Token： </th>
                <td>
                <input class="input-text" type="text" name="data[token]" value="<?php echo $data[token] ? $data[token] : md5(rand(0, 999)); ?>" size="40" />
                </td>
            </tr>
            <tr class="i0">
                <th><font color="red">*</font>&nbsp;AppId： </th>
                <td>
                <input class="input-text" type="text" name="data[appid]" value="<?php echo $data[appid]; ?>" size="40" />
                </td>
            </tr>
            <tr class="i0">
                <th><font color="red">*</font>&nbsp;AppSecret： </th>
                <td>
                <input class="input-text" type="text" name="data[appsecret]" value="<?php echo $data[appsecret]; ?>" size="40" />
                </td>
            </tr>
            <tr>
                <th style="border:none;">&nbsp;</th>
                <td><input class="button" type="submit" name="submit" value="<?php echo lang('submit'); ?>" />
				</td>
            </tr>
            </table>
		</div>
	</div>
</div>
</form>
</body>
</html>