<?php
/**
 * image_lib class file
 * 用于处理图片常用操作,如:生成缩略图,图片水印生成等
 */

if (!defined('IN_IDEACMS')) {
	exit();
}

class image_lib extends Ia_base {

	/**
	 * 原图片路径,该图片在验证码时指背景图片,在水印图片时指水印图片.
	 * 
	 * @var string
	 */
	public $image_url;
	
	/**
	 * 字体名称
	 * 
	 * @var sting
	 */
	public $font_name;
	
	/**
	 * 字体大小
	 * 
	 * @var integer
	 */
	public $font_size;
	
	/**
	 * 图片实例化名称
	 * 
	 * @var object
	 */
	protected $image;
	
	/**
	 * 图象宽度
	 * 
	 * @var integer
	 */
	protected $width;
	
	/**
	 * 图象高度
	 * 
	 * @var integer
	 */
	protected $height;
	
	/**
	 * 图片格式, 如:jpeg, gif, png
	 * 
	 * @var string
	 */
	protected $type;
	
	/**
	 * 文字的横坐标
	 * 
	 * @var integer
	 */
	public $font_x;
	
	/**
	 * 文字的纵坐标
	 * 
	 * @var integer
	 */
	public $font_y;
	
	/**
	 * 字体颜色
	 * 
	 * @var string
	 */
	protected $font_color;		
	
	/**
	 * 生成水印图片的原始图片的宽度
	 * 
	 * @var integer
	 */
	protected $image_width;
	
	/**
	 * 生成水印图片的原始图片的高度
	 * 
	 * @var integer
	 */
	protected $image_height;
	
	/**
	 * 生成缩略图的实际宽度
	 * 
	 * @var integer
	 */
	protected $width_new;
	
	/**
	 * 生成缩略图的实际高度
	 * 
	 * @var integer
	 */
	protected $height_new;
	
	/**
	 * 水印图片的实例化对象
	 * 
	 * @var object
	 */
	protected $water_image;
	
	/**
	 * 生成水印区域的横坐标
	 * 
	 * @var integer
	 */
	protected $water_x;
	
	/**
	 * 生成水印区域的纵坐标
	 * 
	 * @var integer
	 */
	protected $water_y;
	
	/**
	 * 生成水印图片的水印区域的透明度
	 * 
	 * @var integer
	 */
	protected $alpha;

	/**
	 * 文字水印字符内容
	 * 
	 * @var string
	 */
	protected $text_content;
	
	/**
	 * 水印图片的宽度
	 * 
	 * @var integer
	 */
	protected $water_width;
	
	/**
	 * 水印图片的高度
	 * 
	 * @var integer
	 */
	protected $water_height;
	
	
	/**
	 * 构造函数
	 * 
	 * @access public 
	 * @return boolean
	 */
	public function __construct() {
		$this->font_size = 14;
		$this->font_name = SYS_ROOT . 'fonts/elephant.ttf';
		return true;
	}
	
	/**
	 * 初始化运行环境,获取图片格式并实例化.
	 * 
	 * @param string $url 图片路径
	 * @return boolean
	 */
	protected function parse_image_info($url) {
		list($this->image_width, $this->image_height, $type) = getimagesize($url);
		switch ($type) {
			case 1:
				$this->image = imagecreatefromgif ($url);
				$this->type = 'gif';
				break;
			case 2:
				$this->image = imagecreatefromjpeg($url);
				$this->type = 'jpg';
				break;
			case 3:
				$this->image = imagecreatefrompng($url);
				$this->type = 'png';
				break;
			case 4:
				$this->image = imagecreatefromwbmp($url);
				$this->type = 'bmp';
				break;
		}
		return true;
	}
	
	/**
	 * 设置字体名称.
	 * 
	 * @param sting $name	字体名称(字体的路径)	
	 * @param integer $size	字体大小
	 */
	public function set_font_name($name, $size = null) {
		if (!empty($name)) $this->font_name = $name;
		if (!is_null($size)) $this->font_size = (int)$size;
		return $this;
	}
	
	/**
	 * 设置字体大小.
	 * 
	 * @param integer $size	字体大小
	 * @return $this
	 */
	public function set_font_size($size) {
		if (!empty($size)) $this->font_size = intval($size);
		return $this;
	}
	
	/**
	 * 获取颜色参数.
	 * 
	 * @param integer $x	RGB色彩中的R的数值
	 * @param integer $y	RGB色彩中的G的数值
	 * @param integer $z	RGB色彩中的B的数值
	 * @return $this
	 */
	public function set_font_color($x=false, $y=false, $z=false) {
		$this->font_color = (is_int($x) && is_int($y) && is_int($z)) ? array($x, $y, $z) : array(255, 255, 255);	
		return $this;
	}

	/**
	 * 水印图片的URL.
	 * 
	 * @param string $url	图片的路径(图片的实际地址)
	 * @return $this
	 */
	public function set_image_url($url) {
		if (!empty($url)) $this->image_url = $url;
		return $this;
	}

	/**
	 * 设置生成图片的大小.
	 * 
	 * @param integer $width	图片的宽度
	 * @param integer $height	图片的高度
	 * @return $this
	 */
	public function set_image_size($width, $height) {
		if (!empty($width)) $this->width = (int)$width;
		if (!empty($height)) $this->height = (int)$height;
		return $this;
	}
	
	/**
	 * 设置文字水印字符串内容.
	 * 
	 * @param string $content
	 * @return $this
	 */
	public function set_text_content($content) {
		if (!empty($content)) $this->text_content = $content;
		return $this;
	}

	/**
	 * 设置文字水印图片文字的坐标位置.
	 * 
	 * @param integer $x	水印区域的横坐标
	 * @param integer $y	水印区域的纵坐标
	 * @return $this
	 */
	public function set_text_position($x, $y) {
		if (!empty($x)) $this->font_x = (int)$x;
		if (!empty($y)) $this->font_y = (int)$y;
		return $this;
	}	
	 

	/**
	 * 设置水印图片水印的坐标位置.
	 * 
	 * @param integer $x	水印区域的横坐标
	 * @param integer $y	水印区域的纵坐标
	 * @return $this
	 */
	public function set_watermark_position($x, $y) {
		if (!empty($x)) $this->water_x = (int)$x;
		if (!empty($y)) $this->water_y = (int)$y;
		return $this;
	}	

	/**
	 * 设置水印图片水印区域的透明度.
	 * 
	 * @param integer $param	水印区域的透明度
	 * @return $this
	 */
	public function set_watermark_alpha($param) {
		if (!empty($param)) $this->alpha = intval($param);
		return $this;
	}
	
	/**
	 * 常设置的文字颜色转换为图片信息.
	 * 
	 * @return boolean
	 */
	protected function handle_font_color() {
		if (empty($this->font_color)) $this->font_color = array(255, 255, 255);
		return imagecolorallocate($this->image, $this->font_color[0], $this->font_color[1], $this->font_color[2]);
	}			
	
	/**
	 * 根据图片原来的宽和高的比例,自适应性处理缩略图的宽度和高度
	 * 
	 * @return boolean
	 */
	protected function handle_image_size() {
		//当没有所生成的图片的宽度和高度设置时.
		if (!$this->width || !$this->height) Controller::halt('You do not set the image height size or width size!');
		$per_w = $this->width/$this->image_width;		
		$per_h = $this->height/$this->image_height;
		if (ceil($this->image_height*$per_w)>$this->height) {			
			$this->width_new = ceil($this->image_width*$per_h);			
			$this->height_new = $this->height;
		} else {			
			$this->width_new = $this->width;			
			$this->height_new = ceil($this->image_height*$per_w);
		}
		return true;
	}
	
	/**
	 * 生成图片的缩略图.
	 * 
	 * @param string $url			原始图片路径
	 * @param string $dist_name 	生成图片的路径(注:无须后缀名)
	 * @return boolean
	 */
	public function make_limit_image($url, $dist_name = null) {
		if (!$url) return false;
		//原图片分析.
		$this->parse_image_info($url);
		$this->handle_image_size();
		//新图片分析.
		$image_dist = imagecreatetruecolor($this->width_new, $this->height_new);
		//生成新图片.
		imagecopyresampled($image_dist, $this->image, 0, 0, 0, 0, $this->width_new, $this->height_new, $this->image_width, $this->image_height);
		$this->create_image($image_dist, $dist_name, $this->type);
		imagedestroy($image_dist);
		imagedestroy($this->image);
		return true;			
	}
	
	/**
	 * 生成目标图片.
	 * 
	 * @param string $image_dist	原始图片的路径
	 * @param string $dist_name		生成图片的路径
	 * @param string $image_type	图片格式
	 */
	protected function create_image($image_dist, $dist_name = null, $image_type) {
		if (!$image_dist || !$image_type) return false;
		if (!is_null($dist_name)) {
			switch ($image_type) {
				case 'gif':
					imagegif ($image_dist, $dist_name);
					break;
				case 'jpg':
					imagejpeg($image_dist, $dist_name);
					break;
				case 'png':
					imagepng($image_dist, $dist_name);
					break;
				case 'bmp':
					imagewbmp($image_dist, $dist_name);
					break;
			}
		} else {
			switch ($image_type) {
				case 'gif':
					header('Content-type:image/gif');
					imagegif ($image_dist);
					break;
				case 'jpg':
					header('Content-type:image/jpeg');
					imagejpeg($image_dist);
					break;
				case 'png':
					header('Content-type:image/png');
					imagepng($image_dist);
					break;
				case 'bmp':
					header('Content-type:image/png');
					imagewbmp($image_dist);
					break;
			}
		}
		return true;
	}
	
	/**
	 * 生成文字水印图片
	 * @return boolean
	 */
	public function make_text_watermark($image_url, $pos = null, $size = 14) {
		if (!$image_url) return false;
		$this->text_content = empty($this->text_content) ? 'IdeaCMS' : $this->text_content;
		$this->parse_image_info($image_url);
		$size	= empty($size) ? 14 : $size;
		$bbox   = @imagettfbbox($size, 0, $this->font_name, $this->text_content);
		//文字margin_right为5px,特此加5			
		$width  = $bbox[2] - $bbox[0] + 5; 			
		$height = abs($bbox[7] - $bbox[1]);
		//当所要生成的文字水印图片有大小尺寸限制时(缩略图功能)
		if ($this->width && $this->height) {
			$this->handle_image_size();
			//新图片分析
			$image_dist = imagecreatetruecolor($this->width_new, $this->height_new);
			//生成新图片
			imagecopyresampled($image_dist, $this->image, 0, 0, 0, 0, $this->width_new, $this->height_new, $this->image_width, $this->image_height);
			//所生成的图片进行分析
			$font_color = $this->handle_font_color();
			list($wx, $wy) = $this->make_image_pos($pos, $this->width_new, $this->height_new, $width, $height);
			//生成新图片
			imagettftext($image_dist, $size, 0, $wx, $wy, $font_color, $this->font_name, $this->text_content);
			$this->create_image($image_dist, $image_url, $this->type);
			imagedestroy($image_dist);
		} else {
			//所生成的图片进行分析.
			$font_color = $this->handle_font_color();
			list($wx, $wy) = $this->make_image_pos($pos, $this->image_width, $this->image_height, $width, $height);
			//生成新图片.
			@imagettftext($this->image, $size, 0, $wx, $wy, $font_color, $this->font_name, $this->text_content);
			$this->create_image($this->image, $image_url, $this->type);
		}
		imagedestroy($this->image);
		return true;
	}
	
	/**
	 * 图片位置
	 * 
	 * @param int $w_pos	位置代码
	 * @param int $source_w 原图宽
	 * @param int $source_h	原图高
	 * @param int $source_h	原图高
	 * @param int $width	缩略图/文字宽
	 * @param int $height	缩略图/文字高
	 */
	private function make_image_pos($w_pos, $source_w, $source_h, $width, $height) {
	    switch ($w_pos) {
			case 1:
				$wx = 5;
				$wy = 5;
				break;
			case 2:
				$wx = ($source_w - $width) / 2;
				$wy = 0;
				break;
			case 3:
				$wx = $source_w - $width;
				$wy = 0;
				break;
			case 4:
				$wx = 0;
				$wy = ($source_h - $height) / 2;
				break;
			case 5:
				$wx = ($source_w - $width) / 2;
				$wy = ($source_h - $height) / 2;
				break;
			case 6:
				$wx = $source_w - $width;
				$wy = ($source_h - $height) / 2;
				break;
			case 7:
				$wx = 0;
				$wy = $source_h - $height;
				break;
			case 8:
				$wx = ($source_w - $width) / 2;
				$wy = $source_h - $height;
				break;
			default :
				$wx = $source_w - $width;
				$wy = $source_h - $height;
				break;
		}
		return array($wx, $wy);
	}
	
	/**
	 * 生成图片水印
	 */
	public function make_image_watermark($source, $w_pos=null, $img) {
		if (empty($source) || empty($img)) return false;
		$w_img = EXTENSION_DIR . 'watermark' . DIRECTORY_SEPARATOR . $img;
		if (!(extension_loaded('gd') && preg_match("/\.(jpg|jpeg|gif|png)/i", $source, $m) && is_file($source) && function_exists('imagecreatefrom'.($m[1] == 'jpg' ? 'jpeg' : $m[1])))) return false;
		if (!is_file($w_img)) return false;
		$source_info = getimagesize($source);
		$source_w    = $source_info[0];
		$source_h    = $source_info[1];
		//水印图片的透明度参数
		$this->alpha = empty($this->alpha) ? 85 : $this->alpha;
		switch ($source_info[2]) {
			case 1 :
				$source_img = imagecreatefromgif($source);
				break;
			case 2 :
				$source_img = imagecreatefromjpeg($source);
				break;
			case 3 :
				$source_img = imagecreatefrompng($source);
				break;
			default :
				return false;
		}
		$water_info = getimagesize($w_img);
		$width      = $water_info[0];
		$height     = $water_info[1];
		switch ($water_info[2]) {
			case 1 :
				$water_img = imagecreatefromgif($w_img);
				break;
			case 2 :
				$water_img = imagecreatefromjpeg($w_img);
				break;
			case 3 :
				$water_img = imagecreatefrompng($w_img);
				break;
			default :
				return false;
		}
		list($wx, $wy) = $this->make_image_pos($w_pos, $source_w, $source_h, $width, $height);
		if ($water_info[2] == 3) {
			imagecopy($source_img, $water_img, $wx, $wy, 0, 0, $width, $height);
		} else {
			imagecopymerge($source_img, $water_img, $wx, $wy, 0, 0, $width, $height, $this->alpha);
		}
		switch ($source_info[2]) {
			case 1 :
				imagegif($source_img, $source);
				break;
			case 2 :
				imagejpeg($source_img, $source, 80);
				break;
			case 3 :
				imagepng($source_img, $source);
				break;
			default :
				return false;
		}
		imagedestroy($water_img);
		unset($source_info, $water_info);
		imagedestroy($source_img);
		return true;
	}
	
	/**
	 * 析构函数
	 * 
	 * @return void
	 */
	public function __destruct() {		
		
	}
}