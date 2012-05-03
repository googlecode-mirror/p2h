<?php

defined('M5CPL') or die('Access deny!');

/**
 * 
 * @author zhupp 20120428 12:43
 * @desc php to html 
 * 通过ob缓冲把内容写到文件的形式实现静态, 详细流程如下:
 * 1.配置文件有个数组保存着一些必要的信息, 如合法参数数组, 静态有效期, 各种路径...
 * 2.在项目的各个php文件中通过P2H::init($config)
 *    把第一步所描述的数组载入并赋值给相应属性,
 * 	   在这个过程中, P2H得到该页的静态页路径、有效期...
 * 	   这个过程还会调用P2H::checkUpdate()去检查静态页是否存在或者已经过时需要更新,
 *    如果不需要更新则直接在这里终止程序运行, 这也是init最好写在文件顶部的缘故
 * 3.模板中通过P2H::RW()把路径重写成静态地址并生成对应的带有ajax的伪静态文件
 * 4.在包含模板之后调用P2H::toHTML()把缓冲内容写到静态文件里并跳转
 * 5.当点击模板上重写过的地址时, 实际上访问的是一个带有ajax请求的伪静态文件,
 *    此时如果返回写入静态文件成功的状态吗, 重新载入该页面而达到展现, 更新亦是如此
 * 
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
	 * 项目的路径
	 * @var String
	 */
	private static $appPath = './';
	
	/**
	 *
	 * @var String 存放html的文件夹的名字 eg:htmls
	 */
	public static $htmls = 'html';
	
	/**
	 * P2H的路径
	 * @var unknown_type
	 */
	public static $p2hPath = '';
	
	/**
	 * 目录名 例如:D:/www/index/html/list/1.html 里的 list
	 * @var String $dir
	 */
	public static $dir = null;
	
	/**
	 * 模板路径 eg:D:/www/index/templates/index.html
	 * @var String
	 */
	public static $tplPath = null;	
	
	/**
	 * 模板URL eg:http://www.xda.cn/html/index/index.html
	 * @var String
	 */
	public static $tplURL = null;
	
	/**
	 * $_REQUEST数组
	 * @var Array
	 */
	public static $req = null;
	
	/**
	 * 
	 * @var 当前静态页有效时间(秒) 默认1小时
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
		$footer = self::$htmls.'/'.self::$dir.'/'.$rw.self::$rwEnd;
		self::$tplPath = self::$appPath.$footer;
		self::$tplURL = self::$rootURL.$footer;

		self::checkUpdate();

		self::ob_end();
		ob_start();		
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
	 * 发送更新请求
	 */
	public static function update() {
		if(!isset(self::$req['location']) || trim(self::$req['location'])=='')
			die(json_encode(array('status'=>0)));
		fopen(str_replace(self::$rootURL, self::$updateURL, self::UnRWURL(self::$req['location'])), 'r');
	}
	
	/**
	 * 载入配置文件
	 * @param Array $config
	 */
	public static function initConfig($config) {
		//如果没有指定updateURL, 那么默认和rootURL是一样的
		if(!isset($config['updateURL']) && isset($config['rootURL']))
			 self::$updateURL = $config['rootURL'];
		
		foreach($config as $k=>$v) {
			self::set($k, $v);
			
			//ensure that path foot has / and replace \ to /
			$needrepair = array('rootURL', 'updateURL', 'appPath', 'p2hPath');
			if(!is_array($v) && in_array(trim($k), $needrepair))				
				$v = self::repairPath($v);
			
			self::$$k = $v;
			
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
	 * 创建静态页总的文件夹 eg:E:/app/htmls/
	 */
	private static function mkHtmlsDir() {
		if(!isset(self::$htmls) || empty(self::$htmls))
			self::debug('please set "htmls" to array $config  when init($config). eg:E:/html/');
		
		$dirname = self::$appPath.self::$htmls;
		
		if(!is_dir($dirname)) {
			if(false===mkdir($dirname, 0777))
				self::debug("mkdir failed: ".$dirname);
		}
	}
	
	/**
	 * 创建栏目静态页的文件夹 eg:E:/app/htmls/list/
	 * @param String $dir
	 */
	private static function mkHtmlDir($dir = '') {
		$filename = self::$appPath.self::$htmls.'/';
		
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

		return self::$rootURL.self::$htmls.'/'.$dir.'/'.$rw.self::$rwEnd;
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
		if(isset($urlinfo['query']) && !empty($urlinfo['query']))
			parse_str($urlinfo['query'], $query);
		
		return array('dir'=>basename($urlinfo['path'], '.php'), 'query'=>$query);
	}
	
	public static function getHtmlDir($url) {
		$urlinfo = parse_url($url);
		return basename($urlinfo['path'], '.php');
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
		$filename = str_replace(self::$rootURL, self::$appPath, $rw);
		$flag = self::buildAjax($url, $filename);
		if(false===$flag) self::debug('create ajax failed');
		return $rw;
	}
	
	/** 
	 * 静态页是否超过有效期
	 * @return boolen
	 */
	public static function isTimeout() {
		//及时更新 用法:http://localhost/app/index.php?cid=2&fresh=true
		if(isset(self::$req['fresh']) && trim(self::$req['fresh'])==='true')
			return true;
		
		//设置超时时间
		if(isset(self::$pageInfo[self::$dir]) && isset(self::$pageInfo[self::$dir]['timeout'])) 
			self::$timeout = intval(self::$pageInfo[self::$dir]['timeout']);
		
		file_put_contents('./log.txt', self::$tplURL.'--timeout:'.self::$timeout.'--mtime:'.date('Y-m-d H:i:s', self::$mtime));

		$mtime = file_exists(self::$tplPath) ? filemtime(self::$tplPath) : 0;
		if(time() - $mtime > self::$timeout) return true;
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
	 * 检查变量是否有效 若无效 不更新静态页
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
	 * 检查静态页更新
	 * 这个方法在init里头调用了, 所以不需要更新的时候要直接exit终止掉
	 * 不然会一直走完整个php文件直到末尾的获得ob缓冲并重新生成静态
	 */
	private static function checkUpdate() {
		
		if(!self::isWriteComplete() || self::isTimeout()) return true;
		exit;
		//exit(json_encode(array('status'=>'still fresh')));		
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
		if(!self::$isStatic) return;
		
		//更新静态页的JS, 模板是P2H.php同级目录下的P2H.JS
		$filename = self::$p2hPath.'P2H.js';
		if(!is_file($filename)) self::debug('can not find P2H.js file');
		$data = file_get_contents(self::$p2hPath.'P2H.js');
		if(empty($data)) self::$debug('P2H.js is empty');
		
		$search = array('@JQURL@', '@phpURL@');
		$replace = array(self::$jqueryURL, self::$rootURL.'P2HUpdate.php');
		$data = str_replace($search, $replace, $data);
		echo $data;
	}
	
	/**
	 * 打印debug信息
	 * @param mixed $msg
	 */
	private static function debug($msg) {
		if(!self::$debug) return;
		
		exit(get_class().' ERROR: '.$msg);
	}
	
	public static function get($key) {
		$key = trim($key);
		if(empty($key)) self::debug('preperty value is empty');
		if(!in_array($key, array_keys(self::getVars())))
			self::debug('unknow preperty $'.$key);
		else return self::$$key;
	}
	
	public static function set($key, $value) {
		$key = trim($key);
		if(empty($key)) self::debug('preperty $'.$key."'s value can not be empty");
		if(!in_array($key, array_keys(self::getVars())))
			self::debug('unknow preperty $'.$key);
		else self::$$key = $key;
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