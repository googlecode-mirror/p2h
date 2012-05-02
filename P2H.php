<?php

defined('M5CPL') or die('Access deny!');

/**
 * 
 * @author zhupp 20120428 12:43
 * @desc php to html
 * @example
 * P2H::Init($config);
 * include './templets/list_index.html';
 * P2H::ToHtml();
 */

class P2H {
	
	/**
	 * 
	 * @var boolen 是否开启调试模式
	 */
	private static $debug = false;
	
	/**
	 * 
	 * @var boolen是否生成静态
	 */
	public static $isStatic = true;
	
	/**
	 * 
	 * @var 当前应用的URL, 在重写地址的时候用到, 如:http://www.xda.cn 
	 */
	public static $rootURL= '';
	
	/**
	 * 静态页请求更新的URL
	 * @var String
	 */
	public static $updateURL = '';
	
	/**
	 * 
	 * @var String 静态文件名的连接符号
	 */
	private static $rwRule = '_';
	
	/**
	 * 
	 * @var String 静态文件扩展名
	 */
	private static $rwEnd = '.html';
	
	/**
	 * 
	 * @var Array 各页面的配置信息
	 */
	private static $pageInfo = array();
	
	/**
	 *
	 * @var String 存放html的目录路径 如:D:/www/index/html/
	 */
	public static $htmlPath = './html';
	
	/**
	 * 
	 * @var String $dir 目录名 例如:D:/www/index/html/list/1.html 里的 list
	 */
	public static $dir = null;
	
	/**
	 * 
	 * @var String 模板路径 例如:D:/www/index/templates/index.html
	 */
	public static $tplPath = null;	
	
	/**
	 * 
	 * @var Array $_REQUEST数组
	 */
	public static $req = null;
	
	/**
	 * 
	 * @var Int(10) 当前静态页修改时间
	 */
	public static $mtime = 0;
	
	/**
	 * 
	 * @var 静态有效时间(秒) 默认1小时
	 */
	public static $timeout = 3600;
	
	/**
	 * 包含ajax请求的html模板
	 * @var String
	 */
	private static $ajaxTpl = '<!-- ajax page from p2h --><html><head><script type="text/javascript" src="http://img2.xda-china.com/android/static/js/jquery-1.2.6.pack.js"></script>
	<script>
	$(function() {
		$.getJSON(
			"@URL@%QUERY%",
			function(data){
				if(data.status==0) top.location.href=(eval(data.url));
				else if(data.status==1) top.location.reload();
			}
		);
	});
	</script>
	</head><body></body></html>';
	
	/**
	 * 私有化方法防止new和克隆静态类
	 */
	private function __construct(){}
	private function __clone(){}
	
 	   
	/**
	 * Init初始化 载入配置 检查更新 打开ob
	 * @param array $config 配置数组
	 */
	public static function Init($config) {
		
		self::checkConfig($config); //$config must be array and not empty
		
		//set isStatic to false and return if isStatic equal false
		if(false===$config['isStatic'] || !isset($config['isStatic'])) {
			self::$isStatic = false;
			return;
		}
		
		//init config
		$vars = array_keys(self::getVars());
		foreach($config as $k=>$v) {
			if(!in_array($k, $vars)) self::debug('unkonw property $'.$k);
			elseif($v) {
				if(!is_array($v) && in_array(trim($k), array('rootURL', 'htmlPath'))) {
					//ensure that path foot has / and replace \ to /
					$v = self::repairPath($v);
				}
				self::$$k = $v;
			}
		}
		
		if(isset(self::$req['from']) && self::$req['from']=='html' && self::$req['location']) {
			$res = json_encode(array('location'=>self::$req['location']));
			debug::write($res);
		}
		//ensure that htmls dir is exists
		self::mkHtmlsDir();
		
		//set dir
		self::$dir = self::getHtmlDir($_SERVER['PHP_SELF']);
		self::mkHtmlDir();
		
		//set tplPath
		$rw = self::joinArgs(array('dir'=>self::$dir, 'query'=>self::$req));
		self::$tplPath = self::$htmlPath.self::$dir.'/'.$rw.self::$rwEnd;
//var_dump(self::$tplPath);exit;
		//set mtime
		//self::$mtime = file_exists(self::$tplPath) ? filemtime(self::$tplPath) : time();

		//self::update();

		self::ob_end();
		ob_start();
		
		
	}
	
	private static function checkConfig($config) {
		if(!is_array($config)) 
			self::debug('$config must be array when '.get_class().' Init($config)');
		if(empty($config)) 
			self::debug('$config is empty when '.get_class().' Init($config)');
	}
	
	/**
	 * 给路径末尾添加文件分隔符
	 * @param String $path
	 */
	private static function repairPath($path) {
		$path= str_replace('\\', '/', $path);
		$path = rtrim($path, '/').'/';
		return $path;
	}
	
	/**
	 * 创建静态页的文件夹
	 */
	private static function mkHtmlsDir() {
		if(!isset(self::$htmlPath) || empty(self::$htmlPath))
			self::debug('please set "htmlPath" to array $config  when '.get_class().' Init($config). eg:E:/html/');
		
		if(!is_dir(self::$htmlPath)) {
			if(false===mkdir(self::$htmlPath, 0777))
				self::debug("mkdir failed: ".self::$htmlPath);
		}
	}
	
	private static function mkHtmlDir($dir = '') {
		$filename = self::$htmlPath;
		
		if(!empty($dir))	$filename .= $dir;
		else $filename .= self::$dir;
		
		if(!is_dir($filename)) mkdir($filename, 0777);
	}
	
	/**
	 * 重写地址
	 * @param String $url
	 * @return String rwurl
	 */
	public static function RWURL($url) {
		if(!self::$isStatic) return $url;
		
		$dq = self::dq($url);
		$dir = $dq['dir'];
		
		// rw args str
		$rw = self::joinArgs($dq);
		if(empty($rw)) $rw = 'index';
		$htmlDir = basename(self::$htmlPath);
		return self::$rootURL.$htmlDir.'/'.$dir.'/'.$rw.self::$rwEnd;
	}
	
	private static function joinArgs($dq) {
		$rw = '';
		foreach(self::$pageInfo[$dq['dir']]['args'] as $v) {
			if(isset($dq['query'][$v]))	$rw .= $dq['query'][$v].self::$rwRule;
		}
		
		$rw = rtrim($rw, self::$rwRule);
		return empty($rw) ? 'index' : $rw;
	}
	
	/**
	 * 得到html目录名和query数组
	 * @param String $url
	 */
	public static function dq($url) {
		$urlinfo = parse_url($url);
		
		$query = '';
		parse_str($urlinfo['query'], $query);
		
		return array('dir'=>basename($urlinfo['path'], '.php'), 'query'=>$query);
	}
	
	public static function getHtmlDir($url) {
		$urlinfo = parse_url($url);
		return basename($urlinfo['path'], '.php');
	}
	
	private static function getHtmlsDir() {
		return basename(self::$htmlPath);
	}
	
	private static function buildAjax($url, $filename) {
		$dq = self::dq($url);
		$dir = $dq['dir'];
		$query = $dq['query'];
		
		self::mkHtmlDir($dir);
		
		$querys = '';
		if(is_array($query) && !empty($query)) {
			$querys = '?';
			foreach(self::$pageInfo[$dir]['args'] as $k=>$v) {
				if($query[$v])	$querys .= $v.'='.$query[$v].'&';
			}
			$querys .= 'from=ajax';
		}
		$url = self::$updateURL.$dir.'.php';
		$search = array('@URL@', '%QUERY%');
		$replace = array($url, $querys);                        
		$data = str_replace($search, $replace, self::$ajaxTpl);
		return file_put_contents($filename, $data);
	}
	
	public static function RW($url) {
		if(!self::$isStatic) return $url;
		
		$rw = self::RWURL($url);
		$filename = str_replace(self::$rootURL.self::getHtmlsDir().'/', self::$htmlPath, $rw);
		self::buildAjax($url, $filename);
		return $rw;
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
	 * 静态页是否超过有效期
	 * @return boolen
	 */
	public function is_timeout() {
		if(trim(self::$req['fresh'])==='true') return true; //及时更新
		
		self::set_timeout();
		
		$D = time() - self::$ctime;
		if($D > self::$timeout) 	return true;	
		else return false;
		
	}
	
	/**
	 * 检查是否已经完成静态化写入
	 * @return boolen
	 */
	public function is_write_complete() {
		
		if(!file_exists(self::$tpl)) return false;
		
		$con = file_get_contents(self::$tpl);
		if(false===strstr($con, '<!-- ajax page from p2h -->')) return false;
		
		return Html::is_complete($con);
		
	}
	
	/**
	 * 检查变量是否有效 若无效 不更新HTML
	 * @param mixed $var
	 */
	public static function check_var($var) {
	
		if(!is_array($var)) {
			if(IS_STATIC) {
						if(self::$req['from']=='ajax') {
						        $arr=array("status"=>"0", "url"=>'"'.ROOT.'"');  
        						echo self::$req['jsoncallback'].'('.json_encode($arr),')';
								exit;
						}elseif(self::$req['from']=='html') {
							echo 'location.href="'.ROOT.'"';
							exit;
						}else {
							header('Location:'.ROOT);
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
	 * @return boolen
	 */	 
	public static function ToHtml() {
		if(!self::$isStatic) return;
		
		$data = ob_get_contents();
		$flag = false;
		//var_dump(self::$tplPath);exit;
		file_put_contents(self::$tplPath, $data);
		/*
		if(Html::is_complete($con)) {
			$flag = file_put_contents(self::$tpl, Html::min($data));
		}
		*/
		unset($data);	
		self::ob_end();
		
		if(!isset(self::$req['from'])) return;
		
		if(self::$req['from']=='ajax' && $flag!==false)  {
			$arr=array("status"=>"1");
        	echo self::$req['jsoncallback'].'('.json_encode($arr),')';
			exit;
		}
		
		if(self::$req['from']=='html')	 exit;
		//Path::g2h(); //第一次生成或者从PHP过来的需要跳转
			
	}
	
	/**
	 * 清空缓冲区
	 */
	private static function ob_end() {
		if(ob_get_length() > 0) ob_end_clean();
	}
	
	/**
	 * 打印debug信息
	 * @param mixed $msg
	 */
	private static function debug($msg) {
		if(!self::$debug) return;
		
		exit(get_class().' ERROR: '.$msg);
	}
	
	/**
	 * 返回由类的默认属性组成的数组
	 * @return Array
	 */
	private static function getVars() {
		return get_class_vars(get_class());
	}
		

}//P2H class end

?>