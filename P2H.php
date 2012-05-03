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
	 * 是否压缩html
	 * @var boolen
	 */
	public static $minify = true;
	
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
	 * P2H的路径
	 * @var unknown_type
	 */
	public static $p2hPath = '';
	
	/**
	 * 
	 * @var String $dir 目录名 例如:D:/www/index/html/list/1.html 里的 list
	 */
	public static $dir = null;
	
	/**
	 * 
	 * @var String 模板路径 eg:D:/www/index/templates/index.html
	 */
	public static $tplPath = null;	
	
	/**
	 * 
	 * @var String 模板URL eg:http://www.xda.cn/html/index/index.html
	 */
	public static $tplURL = null;
	
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
	 * 带有ajax请求的html模板
	 * @var String
	 */
	private static $ajaxTpl = '<html><head><script type="text/javascript" src="@JQURL@"></script>
	<script>
	$(function() {
		$.getJSON(
			"@URL@@QUERY@",
			function(data){
				if(data.status==0) top.location.href=(eval(data.url));
				else if(data.status==1) top.location.reload();
			}
		);
	});
	</script>
	</head><body></body></html>';
	
	/**
	 * jquery url
	 * @var String
	 */
	private static $jqueryURL = 'http://jqueryjs.googlecode.com/files/jquery-1.2.min.js';
	
	/**
	 * ajax文件标志
	 * @var String
	 */
	private static $ajaxFlag = '<!-- ajax page from p2h -->';
	
	/**
	 * 更新静态页的JSURL
	 * @var String
	 */
	public static $P2HJSURL = null;
	
	/**
	 * 私有化方法防止new和克隆静态类
	 */
	private function __construct(){}
	private function __clone(){}
	
 	   
	/**
	 * Init初始化 载入配置 检查更新 打开ob
	 * @param array $config 配置数组
	 */
	public static function init($config) {
		//$config must be array and not empty
		self::checkConfig($config); 
		
		//set isStatic to false and return if isStatic equal false
		if(false===$config['isStatic'] || !isset($config['isStatic'])) {
			self::$isStatic = false;
			return;
		}
		
		//init config
		self::initConfig($config);
		
		//ensure that htmls dir is exists
		self::mkHtmlsDir();
		
		//set dir
		self::$dir = self::getHtmlDir($_SERVER['PHP_SELF']);
		self::mkHtmlDir();
		
		//set tplPath
		$rw = self::joinArgs(array('dir'=>self::$dir, 'query'=>self::$req));
		self::$tplPath = self::$htmlPath.self::$dir.'/'.$rw.self::$rwEnd;
		self::$tplURL = self::$rootURL.basename(self::$htmlPath).'/'.self::$dir.'/'.$rw.self::$rwEnd;

		//set mtime
		self::$mtime = file_exists(self::$tplPath) ? filemtime(self::$tplPath) : 0;

		self::update();

		self::ob_end();
		ob_start();		
	}
	
	public static function initConfig($config) {
		$vars = array_keys(self::getVars());
		foreach($config as $k=>$v) {
			if(!in_array($k, $vars)) self::debug('unkonw property $'.$k);
			elseif($v) {
				if(!is_array($v) && in_array(trim($k), array('rootURL', 'updateURL', 'htmlPath', 'p2hPath'))) {
					//ensure that path foot has / and replace \ to /
					$v = self::repairPath($v);
				}
				self::$$k = $v;
			}
		}
	}
	
	/**
	 * 检查载入的配置的格式是否正确
	 * @param Array $config
	 */
	private static function checkConfig($config) {
		if(!is_array($config)) 
			self::debug('$config must be array when init($config)');
		if(empty($config)) 
			self::debug('$config is empty when init($config)');
	}
	
	/**
	 * 修复路径
	 * @param String $path
	 */
	private static function repairPath($path) {
		$path= str_replace('\\', '/', $path);
		$path = rtrim($path, '/').'/';
		return $path;
	}
	
	/**
	 * 创建静态页总的文件夹
	 */
	private static function mkHtmlsDir() {
		if(!isset(self::$htmlPath) || empty(self::$htmlPath))
			self::debug('please set "htmlPath" to array $config  when init($config). eg:E:/html/');
		
		if(!is_dir(self::$htmlPath)) {
			if(false===mkdir(self::$htmlPath, 0777))
				self::debug("mkdir failed: ".self::$htmlPath);
		}
	}
	
	/**
	 * 创建栏目静态页的文件夹
	 * @param String $dir
	 */
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
	
	/**
	 * 返回重写之前的地址
	 * @param String $url
	 * @return String 
	 */
	public static function UnRWURL($url) {
		$dir = basename(dirname($url));
		$argstr = basename($url, self::$rwEnd);
		$args = explode(self::$rwRule, $argstr);
		$rw = '';
		
		foreach(self::$pageInfo[$dir]['args'] as $k=>$v) {
			if(isset($args[$k]) && !empty($args[$k]))	$rw .= $v.'='.$args[$k].'&';
		}
		
		$rw = rtrim($rw, '&');
		$query = empty($rw) ? '' : '?'.$rw;
		
		return self::$rootURL.$dir.'.php'.$query;
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
		if(is_file($filename)) return;
		
		$dq = self::dq($url);
		$dir = $dq['dir'];
		$query = $dq['query'];
		
		self::mkHtmlDir($dir);
		
		$querys = '';
		if(is_array($query) && !empty($query)) {
			$querys = '?';
			foreach(self::$pageInfo[$dir]['args'] as $k=>$v) {
				if(isset($query[$v]))	$querys .= $v.'='.$query[$v].'&';
			}
			$querys .= 'from=ajax&jsoncallback=?';
		}
		$url = self::$updateURL.$dir.'.php';
		$search = array('@JQURL@', '@URL@', '@QUERY@');
		$replace = array(self::$jqueryURL, $url, $querys);
		$tpl = self::$ajaxFlag.self::$ajaxTpl;            
		$data = str_replace($search, $replace, $tpl);
		return file_put_contents($filename, $data);
	}
	
	public static function RW($url) {
		if(!self::$isStatic) return $url;
		
		$rw = self::RWURL($url);
		$filename = str_replace(self::$rootURL.self::getHtmlsDir().'/', self::$htmlPath, $rw);
		$flag = self::buildAjax($url, $filename);
		if(false===$flag) self::debug('create ajax failed');
		return $rw;
	}
	
	/**
	 *
	 * 设置超时时间
	 */
	public static function setTimeout() {
		if(isset(self::$pageInfo[self::$dir]) && isset(self::$pageInfo[self::$dir]['timeout'])) 
			self::$timeout = intval(self::$pageInfo[self::$dir]['timeout']);
	}
	
	/** 
	 * 静态页是否超过有效期
	 * @return boolen
	 */
	public static function isTimeout() {
		if(isset(self::$req['fresh']) && trim(self::$req['fresh'])==='true')
			return true; //及时更新
		
		self::setTimeout();
		//file_put_contents('./log.txt', self::$tplURL.'--timeout:'.self::$timeout.'--mtime:'.date('Y-m-d H:i:s', self::$mtime));
		if(time() - self::$mtime > self::$timeout) return true;
		else return false;		
	}
	
	/**
	 * 检查静态文件是否写入完整
	 * @return boolen
	 */
	public static function isWriteComplete() {
		if(!file_exists(self::$tplPath)) return false;
		
		$con = file_get_contents(self::$tplPath);
		return (!self::isAjaxFile($con) && strpos($con, '</html>'));
		
	}
	
	public static function isAjaxFile($con = '') {
		if(empty($con)) $con = file_get_contents(self::$tplPath);
		return strstr($con, self::$ajaxFlag);
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
	private static function update() {
		
		if(!self::isWriteComplete() || self::isTimeout()) return true;
		
		exit;		
	}
	
	/**
	 * 生成静态
	 * @return boolen
	 */
	public static function toHTML() {
		if(!self::$isStatic) return;
		
		$data = ob_get_contents();
		$flag = false;

		if(self::$minify) {
			require_once self::$p2hPath.'HTML.php';
			$data = HTML::minify($data);
		}
		
		$flag = file_put_contents(self::$tplPath, $data);

		unset($data);
		self::ob_end();
	
		if(isset(self::$req['from']) && isset(self::$req['jsoncallback'])) {
			if(self::$req['from']=='ajax') {
				if(false!==$flag) $status=array("status"=>"1");
				else $status = array('status'=>0);
				
				echo self::$req['jsoncallback'].'('.json_encode($status),')';
				exit;
			}			
		}else self::g2h();
				
	}
	
	/**
	 * 跳转
	 */
	public static function g2h($url = '') {
		if(trim($url)=='') $url = self::$tplURL;
		header('Location: '.$url);
		exit;
	}
	
	/**
	 * 清空缓冲区
	 */
	private static function ob_end() {
		if(ob_get_length() > 0) ob_end_clean();
	}
	
	public static function loadScript() {
		if(self::$isStatic)
			echo '<script type="text/javascript" src="'.self::$P2HJSURL.'"></script>';
	}
	
	/**
	 * 打印debug信息
	 * @param mixed $msg
	 */
	private static function debug($msg) {
		if(!self::$debug) return;
		
		exit(get_class().' ERROR: '.$msg);
	}
	
	public static function get($name) {
		$name = trim($name);
		if(empty($name)) self::debug('preperty name is empty');
		if(!in_array($name, array_values(self::getVars())))
			self::debug('unknow preperty $'.$name);
		else return self::$$name;
	}
	
	/**
	 * 返回由类的默认属性组成的数组
	 * @return Array
	 */
	public static function getVars() {
		return get_class_vars(get_class());
	}
		

}//P2H class end

?>