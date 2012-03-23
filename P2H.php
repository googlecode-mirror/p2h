<?php
/**
 * P2H::Init();
 * ... code here ...
 * include './templets/list_index.html';
 * P2H::ToHtml();
 */

defined('M5CPL') or die('Access deny!');

require_once 'Path.php';
require_once 'ReWrite.php';
require_once 'File.php';

class P_2_H {
	
	protected static $p2h;
	public	static $tpl = null;					//string	当前静态页路径 eg:D:/htdocs/html/list_index/3_99.html
	public 	static $req = null;					//array	当前请求参数
	public	static $htmls_dir = null;
	public	static $dir = null;					//string	当前静态页文件夹 eg:list_index
	public 	static $ctime = 0;					//int		当前静态页最近修改时间(时间戳)
	public	static $timeout = 3600;			//int		静态页有效时间
	public	static $html_file_min_size = 1024;
	
	public function getInstance() {
		return empty(self::$p2h) ? new self() : self::$p2h;	
	}
	
	public final function __clone(){
		throw new BadMethodCallException("Clone is not allowed");
	} 
 	   
	/**
	 * Init初始化 检查更新 打开ob
	 * 
	 */
	public static function Init() {
		
		if(!IS_STATIC)		return false;

		self::$req = PorG();		
		self::$htmls_dir = Path::get_htmls_dir();
		//当前静态页的文件夹名称
		self::$dir = Path::PName();

		//静态页路径
		self::$tpl = self::$htmls_dir.Path::h();
		
		//生成时间
		self::$ctime = file_exists(self::$tpl) ? filemtime(self::$tpl) : time();

		self::update();
		
		ob_end_clean();
		ob_start();

	}
	
	/**
	 *
	 * 设置超时时间
	 */
	public function set_timeout() {
		if(strpos(self::$tpl, 'index.html') === TRUE)
			self::$timeout = INDEX_STATIC_TIME;
		else 
			self::$timeout = HTIME;
	}
	
	/** 
	 * 判断静态页是否超过有效期 是返回true  否返回false
	 */
	public function is_timeout() {
		if(self::$req['fresh']=='true') return true; //及时更新
		self::set_timeout();
		
		$D = time() - self::$ctime;
		if($D > self::$timeout) 	return true;	
		else return false;
		
	}
	
	/**
	 * 检查是否已经完成静态化写入
	 * 是返回true  否返回false
	 */
	public function is_write_complete() {
		
		if(!file_exists(self::$tpl) || filesize(self::$tpl) < self::$html_file_min_size ) {
			return false;
		}
		
		$con = file_get_contents(self::$tpl);
		
		if(false===strpos($con, '</html>'))	return false;
		//if(substr(trim($html), -7, 7) != '</html>') return false;
	    else return true;
		
	}
	
	/**
	 * 检查变量是否有效 若无效 不更新HTML
	 * @param string $type
	 * @param array or string $var
	 */
	public static function check_var($var) {
	
		if(!is_array($var)) {
			if(IS_STATIC) {
				if(self::$req['from']=='ajax') {
					$arr=array("status"=>"0", "url"=>'"'.ROOT.'html/index.html"');
					echo self::$req['jsoncallback'].'('.json_encode($arr),')';
					exit;
				}elseif(self::$req['from']=='html') {
					echo 'location.href="'.ROOT.'html/index.html"';
					exit;
				}
			}else{
				header('Location:'.ROOT);
				exit;
			}
		}
	
	}
	
	/**
	 * 静态页更新
	 */
	public  function update() {
		
		if(!self::is_write_complete()) return true;
		if(self::is_timeout()) return true;
		
		if(self::$req['from']=='html') exit;	
		Path::g2h();	
		
	}
	
	/**
	 * ToHtml
	 * @return bool
	 */	 
	public static function ToHtml() {

		if(!IS_STATIC) exit;
		
		$con = ob_get_contents();	

		if(false!==strpos($con, '</html>'))	{
			require_once Bin.'/Html.php';
			$con = Html::minify($con);
			$flag = file_put_contents(self::$tpl, $con);
		}

		unset($con);	
		ob_end_clean();
		
		if(self::$req['from']=='ajax' && $flag)  {
			$arr=array("status"=>"1");
        	echo self::$req['jsoncallback'].'('.json_encode($arr),')';
			exit;
		}
		
		if(self::$req['from']=='html')	 exit;
		Path::g2h(); //第一次生成或者从PHP过来的需要跳转
		
	}
	
	/**
	 * 拿到所有静态页
	 * @return array 静态页数组
	 */
	public function get_all_html() {
		$df = dir_list(Path::get_htmls_dir(), array(substr(RW_END, 1)));
		
		return $df['files'];
		
	}
	
	/**
	 * 更新全部静态页
	 */
	public static function freshHtml() {
		if(!IS_STATIC) exit;
		$htmls = self::get_all_html();
		foreach($htmls as $k=>$v) {
			//fopen(ReWrite::UnRW(Path::html_http_path($v)), 'r');
		}
	}
	
	/**
	 * 删除静态文件
	 * @param string 静态页地址 eg:'http://localhost/android/html/list_index/3_99.html'
	 */
	public static function del($html) {
	
		if(IS_STATIC) {
			$h = Path::html_disk_path($html);
			if(file_exists($h)) {
				if(@unlink($h))	return true;
			}
		}
		
	}
	

}



class P2H extends P_2_H {
	
	/**
	 * 
	 * 转换URL
	 * @param string $php
	 */
	public static function RWURL($url) {
		return ReWrite::RWURL($url);
	}
	
	/**
	 * 
	 * 反转URL
	 * @param string $html
	 */
	public static function UnRW($html) {
		return ReWrite::UnRW($html);
	}
	
	/**
	 * 
	 * 生成基本Ajax页面
	 * @param string $php
	 */
	public static function AjaxHtml($php) {
		Ajax::build($php);
	}
	
	/**
	 * 
	 * 转换URL并生成基本Ajax页
	 * @param string $php
	 */
	public static function RW($php) {
		if(IS_STATIC) {
			self::AjaxHtml($php);
			return self::RWURL($php);
		}else return $php;
	}
	
	/**
	 * 
	 * 跳转到静态页
	 * @param string $html
	 */
	public static function Jump($html='') {
		Path::g2h($html);
	}
	
	
}
?>