<?php
/**
 * html class file
 */

if (!defined('IN_IDEACMS')) {
	exit();
}

class html extends Fn_base {
	
	/**
	 * 将特殊字符转化为HTML代码
	 * 
	 * @param string $text	待转义的内容
	 * @return string		转义后的内容
	 */
	public static function encode($text) {
		
		if (!is_array($text)) {
			return htmlspecialchars($text);			
		}

		foreach ($text as $key=>$value) {				
			$text[$key] = self::encode($value);
		}
		
		return $text;
	}
	
	/**
	 * 处理超级连接代码
	 * 
	 * @param string $text			文字连接内容
	 * @param string $href			连接URL
	 * @param array  $options		其它内容
	 * @return string
	 */
	public static function link($text, $href='#', $options = array()) {		
		
		if (!empty($href)) {
			$options['href'] = $href;
		}
		
		//为了SEO效果,link的title处理.
		if (empty($options['title']) && empty($options['TITLE'])) {
			$options['title'] = $text;
		}
		
		return self::tag('a', $options, $text);
	}

	/**
	 * 用于完成email的html代码的处理
	 * 
	 * @param string $text
	 * @param string $email
	 * @param array  $options
	 * @return string
	 */
	public static function email($text, $email = null, $options = array()) {		
		
		$options['href'] =  'mailto:' . (is_null($email) ? $text : $email);
			
		return self::tag('a', $options, $text);
	}
	
	/**
	 * 处理图片代码
	 * 
	 * @param string $src		图片网址
	 * @param string $alt		提示内容
	 * @param array	 $options	项目内容
	 * @return string
	 */
	public static function image($src, $options = array(), $alt = null) {		
		
		//参数分析
		if (!$src) {			
			return false;
		}
				
		$options['src'] = $src;
		
		if ($alt) {			
			$options['alt'] = $alt;
			//为了SEO效果,加入title.
			if (empty($options['title'])) {
				$options['title'] = $alt;
			}
		}
		
		return self::tag('img', $options);
	}
	
	/**
	 * 处理标签代码
	 * 
	 * @param string 	$tag
	 * @param array 	$options
	 * @param  string 	$content
	 * @param boolean 	$close_tag
	 * @return string
	 */
	public static function tag($tag, $options = array(), $content = null, $close_tag = true) {		
		
		$option_str = '';
		//当$options不为空或类型不为数组时
		if (!empty($options) && is_array($options)) {			
			foreach ($options as $name=>$value) {			
				$option_str .= ' ' . $name . '="' . $value . '"';
			}
		}
				
		$html = '<' . $tag . $option_str;
		
		if (!is_null($content)) {
			
			return $close_tag ? $html .'>' . $content . '</' . $tag . '>' : $html . '>' . $content;
		} else {
			
			return $close_tag ? $html . '/>' : $html . '>';
		}
	}
	
	/**
	 * 加载css文件
	 * 
	 * @param string $url		CSS网址
	 * @param string $media		media属性
	 * @return string
	 */
	public static function css_file($url, $media = null) {
		
		//参数分析
		if (!empty($media)) {			
			$media = ' media="' . $media . '"';
		}
					
		return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . self::encode($url) . "\"" . $media . " />\r";
	}
	
	/**
	 * 加载JavaScript文件
	 * 
	 * @param string $url	js网址
	 * @return string
	 */
	public static function script_file($url) {
		
		return "<script type=\"text/javascript\" src=\"" . self::encode($url) . "\"></script>\r";
	}
	
	/**
	 * 生成表格的HTML代码
	 * 
	 * @param array $content
	 * @param array  $options
	 * @return string
	 */
	public static function table($content=array(), $options = array()) {		
		
		//参数分析
		if (!$content) {			
			return false;
		}
				
		$html = self::tag('table', $options, false, false);
		
		foreach ($content as $lines) {			
			if (is_array($lines)) {				
				$html .= '<tr>';
				foreach ($lines as $value) {					
					$html .= self::tag('td','',$value);
				}
				$html .= '</tr>';
			}
		}
				
		return $html . '</table>';
	}

	/**
	 * form开始HTML代码,即:将<form>代码内容补充完整.
	 * 
	 * @param string $action
	 * @param string $method
	 * @param array  $options
	 * @param boolean $enctype_item
	 * @return string
	 */
	public static function form_start($action, $options = array(), $method = null, $enctype_item = false) {		
		
		//参数分析
		if (!$action) {			
			return false;
		}
				
		$options['action'] = $action;		
		$options['method'] = empty($method) ? 'post' : $method;
		if ($enctype_item === true) {
			$options['enctype'] = 'multipart/form-data';
		}
				
		return self::tag('form', $options, false, false);
	}
	
	/**
	 * form的HTML的结束代码
	 * 
	 * @return string
	 */
	public static function form_end() {		
		
		return '</form>';
	}

	/**
	 * 处理input代码
	 * 
	 * @param string $type
	 * @param array $options
	 * @return string
	 */
	public static function input($type, $options = array()) {		
		
		//参数分析
		if (!$type) {			
			return false;
		}
				
		$options['type'] = $type;
				
		return self::tag('input', $options);
	}

	/**
	 * 处理text表单代码
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function text($options = array()) {
					
		return self::input('text', $options);
	}

	/**
	 * 处理password输入框代码
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function password($options = array()) {
					
		return self::input('password', $options);
	}

	/**
	 * 处理submit提交按钮代码
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function submit($options = array()) {			
		
		return self::input('submit', $options);
	}
	
	/**
	 * 处理reset按钮代码
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function reset($options = array()) {			
		
		return self::input('reset', $options);
	}
	
	/**
	 * 处理button按钮代码
	 * 
	 * @param array $options
	 * @return string
	 */
	public static function button($options = array()) {
		
		return self::input('button', $options);
	}

	/**
	 * 多行文字输入框TextArea的HTML代码处理
	 * 
	 * @param array  $options	属性
	 * @param string $content	文字内容
	 * @return string
	 */
	public static function textarea($options = array(), $content = null) {		
		
		$option_str = '';
		//当$options不为空或类型不为数组时
		if (!empty($options) && is_array($options)) {			
			foreach ($options as $name=>$value) {			
				$option_str .= ' ' . $name . '="' . $value . '"';
			}
		}
				
		$html = '<textarea' . $option_str . '>';			
		
		return ($content==true) ? $html . $content . '</textarea>' :  $html . '</textarea>';
	}

	/**
	 * 处理下拉框SELECT的HTML代码
	 * 
	 * @param array $content_array
	 * @param array $options
	 * @param boolean $selected
	 * @return string
	 */
	public static function select($content_array, $options = array(), $selected = false) {		
		
		if (!$content_array || !is_array($content_array)) {			
			return false;
		}
				
		$option_str = '';
		foreach ($content_array as $key=>$value) {			
			if ($selected==true) {				
				$option_str .= ($key==$selected) ? '<option value="' . $key . '" selected="selected">' . $value . '</option>' : '<option value="' . $key . '">' . $value . '</option>';
			} else {				
				$option_str .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}
				
		return self::tag('select', $options, $option_str);
	}

	/**
	 * 复选框HTML代码
	 * 
	 * @param array $content_array
	 * @param array $options
	 * @param boolean $selected
	 * @return string
	 */
	public static function checkbox($content_array, $options = array(), $selected = false) {
		
		//参数分析		
		if (!$content_array || !is_array($content_array)) {			
			return false;
		}
				
		$html = '';
		foreach ($content_array as $key=>$value) {			
			$options['value'] = $key;
			if (is_array($selected) && !empty($selected)) {				
				if (in_array($key, $selected)) {					
					$options['checked'] = 'checked';
				} else {
					if (isset($options['checked'])) {
						unset($options['checked']);
					}
				}
			}			
			$html .= '<label>'.self::input('checkbox', $options).$value.'</label>';
		}
				
		return $html;
	}

	/**
	 * 单选框HTML代码
	 * 
	 * @param array $content_array
	 * @param array $options
	 * @param boolean $selected
	 * @return string
	 */
	public static function radio($content_array, $options = array(), $selected = 0) {		

		//参数分析
		if (!$content_array || !is_array($content_array)) {			
			return false;
		}
				
		$html = '';
		foreach ($content_array as $key=>$value) {			
			$options['value'] = $key;			
			if ($selected==$key) {				
				$options['checked'] = 'checked';
			} else {				
				if (isset($options['checked'])) {					
					unset($options['checked']);
				}
			}			
			$html .= '<label>'.self::input('radio', $options).$value.'</label>';
		}
				
		return $html;
	}
}