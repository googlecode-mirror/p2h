<?php
/*
 * 此静态类的特点是靠用户的触发生成静态,并且如果没有用户访问时,静态页不更新.
 * 只有在用户访问了某个静态页并且静态页过期了,才会执行更新.
 * 支持跨域请求更新,可专门有一台服务器生成静态,
 * 然后可通过rsync等同步软件同步到静态服务器.
 * 
 * 主要方法:
 * rw() : url重写;
 * init(): 初始化;
 * run(): 生成静态.
 * 
 * 实现静态化大致的流程是这样的:
 * 1.配置P2H文件夹下的config.php里的参数.
 * 2.在需要静态化的php文件中头部加上P2H::init(),在包含模板文件之后执行P2H::run();
 * 3.在模板文件中将需要静态化的链接地址通过P2H::rw($url)返回html地址,
 * 	  执行此方法的时候同时生成了带有AJAX内容的伪静态页(这是用户触发生成静态的关键)
 * 4.模板文件加入一个<script> : update_html.js此文件负责向php发送更新请求
 */
class P2H {
	static $config = NULL;
	static $html_dir = NULL;
	
	public function init() {
		self::set_config();	//设置config(是否静态、静态时间、合法参数...)
		if(!self::$config['IS_STATIC']) return false;
		
		self::check_updatejs_file(); //创建update.js
		self::set_html_dir();	//设置html目录
		
		self::update();	//检查更新,如果没达到更新的条件将在此处exit

		
		//打开ob
		ob_clean();
		ob_start();
	}
	
	public function set_config() {
		if(is_null(self::$config))
			self::$config = require __DIR__.'/config.php';	
	}
	
	public function run() {
		if(!self::$config['IS_STATIC']) return false;
	}
	
	public function update() {
		if(!self::$config['IS_STATIC']) return false;
		
	}
	
	public function check_updatejs_file() {
		$filename = 'update_html.js';
		if(is_file($filename)) return true;
		
		$content = 'update';
		$flag = file_put_contents($filename, $content);
		if(!$flag) throw new Exception('create_updatejs_file() false, '.__FILE__.' on line '.__LINE__);
	}
	
	//检查某个变量是否有效,无效不更新HTML
	public function check_var() {
	
	}
	
	public function build_ajax_file() {
	
	}
	
	public function is_time_out() {
	
	}
	
	//及时更新
	public function is_fresh() {
	
	}
	
	public function get_args() {
		$querys = array();
		$query = parse_str($_SERVER['QUERY_STRING'], $querys);
		
		$ini = array();
		if(false!==strpos(self::$html_dir, '/')) {
			$layer_info = explode('/',  self::$html_dir);
			$arr = "self::\$config['ARGS']";
			foreach($layer_info as $k=>$v) {
				$arr .= "['".strtoupper($v)."']";
			}
			eval("\$ini = $arr;");
		}else $ini = self::$config['ARGS']['MAIN'][strtoupper(self::$html_dir)];

		if(empty($ini)) return NULL;
		
		$res = array();
		//只允许合法参数
		foreach($ini as $k=>$v) {
			$res[$v] = isset($querys[$v]) ? $querys[$v] : 0;
		}

		return $res;
	}
	
	//得到参数值的字符串,如1_3
	public function get_query_string() {
		$args = self::get_args();
		$res = '';
		foreach($args as $v) {
			$res .= $v.self::$config['RW']['RULE'];
		}

		return rtrim($res, self::$config['RW']['RULE']);
	}
	
	//app_name后面的html路径
	public function get_html_footpath() {
		if(false!==strpos(self::$html_dir, '/')) {
			$layer = explode('/', self::$html_dir);
			$max_key = count($layer)-1;
			$layer[$max_key] = 'html/'.$layer[$max_key];
			return implode('/', $layer).'/'.self::get_query_string().'.'.self::$config['RW']['END'];
		}
		return 'html/'.self::$html_dir.'/'.self::get_query_string().'.'.self::$config['RW']['END'];
	}
	
	//得到静态页的磁盘路径
	public function get_html_filepath() {
		return $_SERVER['DOCUMENT_ROOT'].'/'.self::$config['APP_NAME'].'/'.self::$html_dir.'/'.self::get_query_string();
	}
	
	//根据php地址得到静态页路径
	public function get_html_httppath($php) {
		$appname_pos = strpos($php, self::$config['APP_NAME']);
		//$foot = ;
		//str_replace(substr($php, $appname_pos), self::get)
	}
	
	/*
	 * 静态页的目录
	 * 比如:http://localhost/p2h/blog/list.php?gid=3&cid=1得到目录为blog/html
	 */
	public function set_html_dir() {
		if(is_null(self::$html_dir)) {
			$sn = $_SERVER['SCRIPT_NAME'];
			self::$html_dir = substr($sn, strpos($sn, self::$config['APP_NAME'])+strlen(self::$config['APP_NAME'])+1, -4);
		}
	}
	
	public function compress_html() {
	
	}
	
	public function rw($url) {
		if(!self::$config['IS_STATIC']) return $url;
	}

	
} //P2H end
?>