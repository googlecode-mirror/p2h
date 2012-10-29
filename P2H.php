<?php

defined('M5CPL') or die('Access deny! in: '.__FILE__);

/**
 * 
 * @author zhupp (328877098@qq.com) 20120428 12:43
 * @desc php to html 
 * @example
 * P2H::initConfig($config);
 * P2H::init();
 * include './templets/index.html';
 * P2H::toHtml();
 */

class P2H {
	
	/**
	 * 调试模式:
	 * 0关闭调试
	 * 1开启调试并把错误打印在屏幕上
	 * 2开启调试并把错误保存在文件中 文件保存在log/p2herror.txt
	 * @var int
	 */

	private static $debug = 1;
	
	/**
	 * 错误日志路径
	 * @var String
	 */
	private static $debugFile = './p2h_error.log';
	
	const STATUS_FAIL = 0;
	
	/**
	 * 
	 * @var boolen是否生成静态
	 */
	public static $isStatic = true;
	
	/**
	 * 是否压缩html 默认为true
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
	 * @var String
	 */
	public static $p2hPath = '';
	
	/**
	 * 目录名 例如:www/index/html/list/1.html 里的 list
	 * @var String $htmlDirName
	 */
	public static $htmlDirName = null;
	
	/**
	 * 模板路径 eg:D:/www/index/templates/index.html
	 * @var String
	 */
	public static $tplPath = null;	
	
	/**
	 * 模板URL eg:http://www.xxx.cn/html/index/index.html
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
	private static $ajaxTpl = '<!DOCTYPE html><html>
 <head>
 <meta charset="utf-8">
  <meta http-equiv="pragma" content="no-cache" />
  <meta http-equiv="expires" content="Sunday 26 October 2008 01:00 GMT" />  
    <script type="text/javascript" src="@JQURL@"></script>
    <script> 
	$(function(){              
		$.ajax({
			dataType: "jsonp",
			url: "@URL@",
			data: {@QUERY@},
				success: function(data) {
					if(data && data.status) {
						if(data.status==0 && data.url){
							location.href = eval(data.url);
						}else if(data.status==1){
							setTimeout(function(){
                                location.reload(true);
                            },300);
						}
					}else{
                            location.href = "http://bbs.xda.cn/";
                    }
                                        
				}
		});
                
        });
	</script>
	</head><body></body></html>';
	
	/**
	 * jquery url
	 * @var String
	 */
	private static $jqueryURL = 'http://jqueryjs.googlecode.com/files/jquery-1.2.min.js';
	
	/**
	 * 私有化方法防止new和克隆静态类
	 */
	private function __construct(){}
	private function __clone(){}
	
 	   
	/**
	 * Init初始化 载入配置 检查更新 打开ob
	 * 
	 */
	public static function init() {
		if(!self::$isStatic) return;
		
		self::mkHtmlDir();
		if(self::$debug==2)
			self::mkDir(dirname(self::$debugFile));
	
        self::checkUpdate();
                
		self::ob_end();
		ob_start();

	}
	
	/**
	 * 载入配置文件
	 * @param Array $config
	 */
	public static function initConfig($config) {
		if(!is_array($config) || empty($config))
			self::debug('fail to init config', __LINE__);
	
		foreach($config as $k=>$v) {
			self::set($k, $v);
		}
		
		self::setHtmlDirName();
		
		//set tplPath
		$rw = self::joinArgs(array('dir'=>self::$htmlDirName, 'query'=>self::$req));	
		$footer = self::$htmls.'/'.self::$htmlDirName.'/'.$rw.self::$rwEnd;
		self::set('tplPath', self::$appPath.$footer);
		self::set('tplURL', self::$rootURL.$footer);
		//D(self::getVars());
	}
	
	/**
	 * 生成静态
	 * @return boolen
	 */
	public static function toHTML() {
		if(!self::$isStatic) return;
	
		$data = ob_get_contents();
		$flag = false;
		if(!$data) {
			$status = array('status'=>'0', 'url'=>self::$rootURL);			
			self::showStatus($status);
		}
		
		$data = self::insertBetween($data, self::loadScript(), '</body>');
		//D($data);
		if(phpversion() >= '5.3') $data = self::minify($data);
			
		$flag = file_put_contents(self::$tplPath, $data);

		unset($data);
		self::ob_end();
		if(isset(self::$req['from']) && isset(self::$req['callback'])) {
			if(self::$req['from']=='ajax') {
				if(false!==$flag) $status=array('status'=>'1');
				else $status = array('status'=>'0', 'url'=>self::$rootURL);
	
				self::showStatus($status);
			}
		}else self::jump();
	
	}
	
	/**
	 * 发送更新请求
	 */
	public static function update() {
		if(!isset(self::$req['location']) || trim(self::$req['location'])=='')
			self::showStatus(array('status'=>'00'));
		$ch = curl_init();		
		$options = array(
				CURLOPT_TIMEOUT=>30,
				CURLOPT_URL=>str_replace(self::$rootURL, self::$updateURL, self::UnRWURL(self::$req['location'])),
				CURLOPT_HEADER=>false,
		);
		
		curl_setopt_array($ch, $options);

		if(false===curl_exec($ch)) 
			self::showStatus(array('status'=>'01'));
			
		curl_close($ch);
	}
	
	private function showStatus($status) {	
		echo self::$req['callback'].'('.json_encode($status),')';
		exit;
	}
	
	/**
	 * 修复路径
	 * @param String $path
	 */
	private function repairPath($path) {
		$path= str_replace('\\', '/', $path);
		$path = rtrim($path, '/').'/';
		return $path;
	}
	
	/**
	 * 删除无效的静态文件
	 * @param String $htmlPath
	 */
	private function delHTML($htmlPath) {
		if(!file_exists($htmlPath)) return true;
		
		chmod($htmlPath, 0777);
		if(false===unlink($htmlPath))
			self::debug('fail to delete this file '.$htmlPath, __LINE__);
		
	}
	
	/**
	 * 创建目录
	 * @param String $dirname
	 */
	private function mkDir($dirname) {
		if(!is_dir($dirname)) {
			if(false===mkdir($dirname, 0777))
				self::debug("mkdir failed: ".$dirname, __LINE__);
		}
	}
	

	/**
	 * 创建栏目静态页的文件夹 eg:E:/app/htmls/list/
	 * @param String $dir
	 */
	private function mkHtmlDir($url='') {
		$dirinfo = explode('/', self::getHtmlDir($url));
		$dir = self::$appPath.self::$htmls;
		self::mkDir($dir);
		foreach($dirinfo as $v){
			$dir .= '/'.$v;
			self::mkDir($dir);
		}
	}
	
	/**
	 * 重写地址并生成伪静态文件
	 * @param String $url
	 */
	public static function RW($url) {
		if(!self::$isStatic) return $url;
		$rw = self::RWURL($url);
		$filename = str_replace(self::$rootURL, self::$appPath, $rw);
		$flag = self::buildAjax($url, $filename);
		if(false===$flag) self::debug('create ajax failed', __LINE__);
		return $rw;
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
		
		if(empty($rw)) $rw = 'index';
		
		$rw = self::joinArgs($dq);
		
		return self::$rootURL.self::$htmls.'/'.$dir.'/'.$rw.self::$rwEnd;
	}
	
	/**
	 * 返回重写之前的地址
	 * @param String $url
	 * @return String 
	 */
	public static function UnRWURL($url) {
		if(!self::$isStatic) return $url;
		
		if(strpos($url, self::$rootURL)===false)
			return self::$rootURL.'index.php';

		$urlinfo = parse_url($url);
		$rootURLInfo = parse_url(self::$rootURL.'html');
		$dirpath = str_replace($rootURLInfo, '', $urlinfo['path']);
		$dir = '';
		if(!empty($dirpath)){
			$dir = dirname($dirpath);
			if($dir==='.')	$dir = '';			
			if(!empty($dir))	$dir = ltrim($dir, '/');
		}
		
		$argstr = basename($url, self::$rwEnd);
		$args = explode(self::$rwRule, $argstr);
		$rw = '';
		
		$query = '';
		if(isset(self::$pageInfo[$dir]['args']) && !empty(self::$pageInfo[$dir]['args'])) {
			foreach(self::$pageInfo[$dir]['args'] as $k=>$v) {
				if(isset($args[$k]) && !empty($args[$k]))
					$rw .= $v.'='.$args[$k].'&';
			}
			
			$rw = rtrim($rw, '&');
			$query = empty($rw) ? '' : '?'.$rw;
		}
		//D(self::$rootURL.$dir.'.php'.$query);
		return self::$rootURL.$dir.'.php'.$query;
	}
	
	/**
	 * 拼接参数值
	 * @param Array $dq
	 */
	private function joinArgs($dq) {
		
		$rw = '';
		
		if(empty($dq['query']) || !isset(self::$pageInfo[$dq['dir']]['args']) || empty(self::$pageInfo[$dq['dir']]['args']))
			return 'index';
		
		foreach(self::$pageInfo[$dq['dir']]['args'] as $v) {
			$value = (isset($dq['query'][$v]) && !empty($dq['query'][$v])) ? $dq['query'][$v] : 0;
			$rw .= $value.self::$rwRule;
		}
		
		$rw = rtrim($rw, self::$rwRule);
		
		//删掉末尾的清一色0
		$args = explode(self::$rwRule, $rw);
		for($i=count($args)-1; $i>=0; $i--) {
			if($args[$i]==0) {
				unset($args[$i]);
			}else break;
		}
		$rw= implode('_', $args);

		return empty($rw) ? 'index' : $rw;
	}
	
	/**
	 * 得到目录&query数组
	 * @param String $url
	 */
	private function dq($url) {
		$urlinfo = parse_url($url);
		$query = '';
		if(isset($urlinfo['query']) && !empty($urlinfo['query']))
			parse_str($urlinfo['query'], $query);

		$dir = self::getHtmlDir($url);
		if(!isset(self::$pageInfo[$dir]['args']) || empty(self::$pageInfo[$dir]['args']))
			$q = null;
		else {
			foreach(self::$pageInfo[$dir]['args'] as $v) {
				if($query[$v]) $q[$v] = $query[$v];
			}
		}
		//D(array('dir'=>$dir, 'query'=>$q));
		return array('dir'=>$dir, 'query'=>$q);
	}
	
	/**
	 * html目录
	 * 
	 */
	public function setHtmlDirName() {
		self::set('htmlDirName', self::getHtmlDir());
	}
	
	/**
	 * html目录
	 * 
	 */
	public function getHtmlDir($url='') {
		if(empty($url))
			$urlinfo = parse_url($_SERVER['PHP_SELF']);
		else
			$urlinfo = parse_url($url);
			
		if(substr($urlinfo['path'], -4)!='.php'){
			$urlinfo['path'] = self::repairPath($urlinfo['path']).'/index.php';
		}
		
		$rootURLInfo = parse_url(self::$rootURL);
		//D($rootURLInfo['path'].'   '.$urlinfo['path']);
		$dirpath = str_replace($rootURLInfo, '', $urlinfo['path']);
		$dirname = '';
		if(!empty($dirpath)){
			$dirname = dirname($dirpath);
			//D($dirname);
			if($dirname==='.')	$dirname = '';			
			if(!empty($dirname))	$dirname = ltrim($dirname, '/').'/';
		}
		//D($dirname.basename($urlinfo['path'], '.php'));
		return $dirname.basename($urlinfo['path'], '.php');			
	}
	
	/**
	 * 生成带有ajax请求的伪静态文件
	 * @param String $url
	 * @param String $filename
	 */
	private function buildAjax($url, $filename) {
		if(is_file($filename)) return;
		$dq = self::dq($url);
		$dir = $dq['dir'];
		$query = $dq['query'];
		
		self::mkHtmlDir($url);
		$querys = '';
		if(is_array($query) && !empty($query)) {
			foreach(self::$pageInfo[$dir]['args'] as $k=>$v) {
				if(isset($query[$v]))	$querys .= "'{$v}':'{$query[$v]}', ";
			}
		}
		$querys .= "'from':'ajax'";
		
		$url = self::$updateURL.$dir.'.php';
		$search = array('@JQURL@', '@URL@', '@QUERY@');
		$replace = array(self::$jqueryURL, $url, $querys);
		$tpl = str_replace($search, $replace, self::$ajaxTpl);
		return file_put_contents($filename, $tpl);
	}
	
	/**
	 * 在文档的某个位置插入内容
	 * @param String $data
	 * @param String $insert
	 * @param String $delimiter
	 */
	private function insertBetween($data, $insert, $delimiter = '</body>') {
		if(strpos($data, $delimiter)===false)
			self::debug("delimiter not found in html, can not insert ajax behind your defined delimiter ");
		
		$tpls = explode($delimiter, $data);
		return $tpls[0].$insert.$delimiter.$tpls[1];
	}
	
	/**
	 * 压缩
	 * @param String $data
	 * @param String $type
	 */
	private function minify($data, $type = 'HTML') {
		
		if(!self::$minify) return $data;
		
		$type = trim($type);
		if(!in_array($type, array('HTML', 'JSMin')))
			self::debug('unknown minify method', __LINE__);
		
		$filename = self::$p2hPath.'plugin/'.$type.'.php';
		if(file_exists($filename)) require_once $filename;
		else self::debug($filename.' not exists');

		return $type::minify($data);
		
	}
	
	/** 
	 * 静态页是否超过有效期
	 * @return boolen
	 */
	private function isTimeout() {
		//及时更新 用法:http://localhost/app/index.php?cid=2&fresh=true
		if(isset(self::$req['fresh']) && trim(self::$req['fresh'])==='true')
			return true;
		//设置超时时间
		if(isset(self::$pageInfo[self::$htmlDirName]) && isset(self::$pageInfo[self::$htmlDirName]['timeout'])) 
			self::$timeout = intval(self::$pageInfo[self::$htmlDirName]['timeout']);
			
		$mtime = file_exists(self::$tplPath) ? filemtime(self::$tplPath) : 0;
		if(time() - $mtime > self::$timeout) return true;
		else return false;		
	}
	
	/**
	 * 检查条件是否为真 如果假 不更新静态页
	 * @param boolen $condition
	 */
	public static function check($condition) {
		if(!$condition) {
			$jumpto = self::$rootURL;

			if(!self::$isStatic || !isset(self::$req['from']))
				self::jump($jumpto);
				
			if(self::$req['from']=='ajax') {
				self::showStatus(array("status"=>"0", "url"=>'"'.$jumpto.'"'));
			}elseif(self::$req['from']=='html') {
				self::showStatus(array('status'=>'01'));
			}
			
		}
		
	}
	
	/**
	 * 检查静态页更新
	 * 这个方法在init里头调用了, 所以不需要更新的时候要直接exit终止掉
	 * 不然会一直走完整个php文件直到末尾的获得ob缓冲并重新生成静态
	 */
	private function checkUpdate() {
		if((isset(self::$req['from']) && self::$req['from']=='ajax') || self::isTimeout()) return;
		if(isset(self::$req['from']) && self::$req['from']=='html') {
			exit;
		}
		
		else self::jump();
	}
	
	/**
	 * 跳转
	 */
	private function jump($url = '') {
		if(trim($url)=='') {
			$url = self::$tplURL;
			if(!file_exists(self::$tplPath)) 
				self::debug(self::$tplPath.' not found', __LINE__);
		}
		
		if(!headers_sent()) header('Location: '.$url);
		else{
			$js = <<<EOF
			<script type="text/javascript">
				self.location="{$url}"
			</script>
EOF;
			echo $js;
		}
		exit;
	}
	
	/**
	 * 清空缓冲区
	 */
	private function ob_end() {
		if(ob_get_length() > 0) ob_end_clean();
	}
	
	/**
	 * 加载负责发出更新请求的JS
	 */
	private function loadScript() {
		if(!self::$isStatic) return;
		
		//更新静态页的JS, 模板是P2H.php同级目录下的P2H.JS
		$filename = self::$p2hPath.'P2H.js';
		if(!is_file($filename)) self::debug('can not find '.$filename, __LINE__);

		$data = file_get_contents(self::$p2hPath.'P2H.js');
		if(empty($data)) self::$debug('P2H.js is empty');
		
		$search = array('@JQURL@', '@phpURL@');
		$replace = array(self::$jqueryURL, self::$rootURL.'P2HUpdate.php');
		$data = str_replace($search, $replace, $data);
		if(phpversion() >= '5.3') $data = self::minify($data, 'JSMin');
		return $data;
	}
	
	/**
	 * 得到静态目录下所有文件夹和文件
	 * string $root 目录路径
	 * array $extensions 扩展名
	 * return array('files'=>array(...),'dirs'=>array(...))
	 *
	 */
	private function htmlList() {
		$extensions = array(ltrim(self::$rwEnd, '.'));
		$root = self::$appPath.self::$htmls;
		$files  = array('files'=>array(), 'dirs'=>array());
		$directories  = array();
		$last_letter  = $root[strlen($root)-1];
		$root  = ($last_letter == '\\' || $last_letter == '/') ? $root : $root.DIRECTORY_SEPARATOR;
		 
		$directories[]  = $root;
		 
		while (sizeof($directories)) {
			$dir  = array_pop($directories);
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if ($file == '.' || $file == '..' || $file == '.svn')  continue;
	
					$filepath  = $dir.$file;
					if (is_dir($filepath)) {
						$directory_path = $filepath.DIRECTORY_SEPARATOR;
						array_push($directories, $directory_path);
						$files['dirs'][]  = $directory_path;
					}elseif(is_file($filepath)) {
						 
						if(!empty($extensions)) {
							 
							if(in_array(pathinfo($filepath,PATHINFO_EXTENSION), $extensions))
								$files['files'][]  = $filepath;
							 
						}else    $files['files'][]  = $filepath;
						 
					}
				}
				closedir($handle);
			}
		}
		 
		return $files;
	}
	
	/**
	 * 打印debug信息
	 * @param mixed $msg
	 */
	private function debug($msg, $line = '') {
		$msg = '['.date('Y-m-d H:i:s').'] {'.get_class().' msg} '.$msg;
		if(!empty($line)) $msg .= ' throw in line '.$line;
		
		switch (intval(self::$debug)) {
		case 1:
			exit($msg);
			break;
		
		case 2:
			file_put_contents(self::$debugFile, $msg.PHP_EOL, FILE_APPEND);
			break;
			
		case 3: 
			var_dump(json_encode($msg));
			break;
			
		default:
			return;
		}
		
	}
	
	
	/**
	 * 获取类属性
	 * @param String $key
	 */
	public static function get($key) {
		$key = trim($key);
		
		if(!in_array($key, array_keys(self::getVars())))
			self::debug('unknow preperty $'.$key, __LINE__);
		
		return self::$$key;
	}
	
	/**
	 * 设置属性值
	 * @param String $key
	 * @param mixed $value
	 */
	public static function set($key, $value) {
		$key = trim($key);

		if(!in_array($key, array_keys(self::getVars())))
			self::debug('unknow preperty $'.$key, __LINE__);

		//ensure that path foot has / and replace \ to /
		$needrepair = array(
				'rootURL',
				'updateURL',
				'appPath',
				'p2hPath',
		);
		if(!is_array($value) && in_array($key, $needrepair))
			$value = self::repairPath($value);
		
		self::$$key = $value;
		
	}
	
	/**
	 * 返回由类的默认属性组成的数组
	 * @return Array
	 */
	private function getVars() {
		return get_class_vars(get_class());
	}
		

}//P2H class end

?>