<?php
/**
 * check class file
 * 常用的正则表达式来验证信息.如:网址 邮箱 手机号等
 */

if (!defined('IN_IDEACMS')) exit();

class check extends Ia_base {

	/**
	 * 正则表达式验证email格式
	 * 
	 * @param string $str	所要验证的邮箱地址
	 * @return boolean
	 */
	public static function is_email($str) {
		if (!$str) return false;
		return preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $str) ? true : false;
	}

	/**
	 * 正则表达式验证网址
	 * 
	 * @param string $str	所要验证的网址
	 * @return boolean
	 */
	public static function is_url($str) {
		if (!$str) return false;
		return preg_match('#^(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str) ? true : false;
	}

	/**
	 * 验证字符串中是否含有汉字
	 * 
	 * @param integer $string	所要验证的字符串。注：字符串编码仅支持UTF-8
	 * @return boolean
	 */
	public static function is_chinese_character($string) {
		if (!$string) return false;
		return preg_match('~[\x{4e00}-\x{9fa5}]+~u', $string) ? true : false;
	}
	
	/**
	 * 验证字符串中是否含有非法字符
	 * 
	 * @param string $string	待验证的字符串
	 * @return boolean
	 */
	public static function is_invalid_str($string) {
		if (!$string) return false;
		return preg_match('#[!#$%^&*(){}~`"\';:?+=<>/\[\]]+#', $string) ? true : false;
	}

	/**
	 * 用正则表达式验证邮证编码
	 * 
	 * @param integer $num	所要验证的邮政编码
	 * @return boolean
	 */
	public static function is_post_num($num) {
		if (!$num) return false;
		return preg_match('#^[1-9][0-9]{5}$#', $num) ? true : false;
	}

	/**
	 * 正则表达式验证身份证号码
	 * 
	 * @param integer $num	所要验证的身份证号码
	 * @return boolean
	 */
	public static function is_personal_card($num) {
		if (!$num) return false;
		return preg_match('#^[\d]{15}$|^[\d]{18}$#', $num) ? true : false;
	}

	/**
	 * 正则表达式验证IP地址, 注:仅限IPv4
	 * 
	 * @param string $str	所要验证的IP地址
	 * @return boolean
	 */
	public static function is_ip($str) {
		if (!$str) return false;
		if (!preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str)) {
			return false;			
		}
		$ip_array = explode('.', $str);
		//真实的ip地址每个数字不能大于255（0-255）		
		return ($ip_array[0]<=255 && $ip_array[1]<=255 && $ip_array[2]<=255 && $ip_array[3]<=255) ? true : false;
	}

	/**
	 * 用正则表达式验证出版物的ISBN号
	 * 
	 * @param integer $str	所要验证的ISBN号,通常是由13位数字构成
	 * @return boolean
	 */
	public static function is_book_isbn($str) {
		if (!$str) return false;
		return preg_match('#^978[\d]{10}$|^978-[\d]{10}$#', $str) ? true : false;
	}

	/**
	 * 用正则表达式验证手机号码(中国大陆区)
	 * @param integer $num	所要验证的手机号
	 * @return boolean
	 */
	public static function is_mobile($num) {
		if (!$num) return false;
		return preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#', $num) ? true : false;
	}
	
	/**
	 * 过滤XSS(跨网站攻击)代码
	 * 
	 * 通常用于富文本提交内容的过滤.提升网站安全必备
	 * @access public
	 * @author thinkphp(extend clsss)
	 * @param string $val	待过滤的内容
	 * @return string
	 */
	public static function remove_xss($val) {
   		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   		// this prevents some character re-spacing such as <java\0script>
   		// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   		$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
   		// straight replacements, the user should never need these since they're normal characters
   		// this prevents like <IMG SRC=@avascript:alert('XSS')>
   		$search = 'abcdefghijklmnopqrstuvwxyz';
   		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   		$search .= '1234567890!@#$%^&*()';
   		$search .= '~`";:?+/={}[]-_|\'\\';
  		 for ($i = 0; $i < strlen($search); $i++) {
      		// ;? matches the ;, which is optional
      		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

      		// @ @ search for the hex values
      		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      		// @ @ 0{0,7} matches '0' zero to seven times
      		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   		}
   		// now the only remaining whitespace attacks are \t, \n, and \r
   		$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   		$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   		$ra = array_merge($ra1, $ra2);

   		$found = true; // keep replacing as long as the previous round replaced something
   		while ($found == true) {
      		$val_before = $val;
      		for ($i = 0; $i < sizeof($ra); $i++) {
         		$pattern = '/';
         		for ($j = 0; $j < strlen($ra[$i]); $j++) {
            		if ($j > 0) {
               		$pattern .= '(';
               		$pattern .= '(&#[xX]0{0,8}([9ab]);)';
               		$pattern .= '|';
               		$pattern .= '|(&#0{0,8}([9|10|13]);)';
               		$pattern .= ')*';
            		}
            		$pattern .= $ra[$i][$j];
        	 	}
         		$pattern .= '/i';
         		$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         		$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         		if ($val_before == $val) {
           		 // no replacements were made, so exit the loop
            	$found = false;
         		}
      	   }
   		}
   		return $val;
	}
}