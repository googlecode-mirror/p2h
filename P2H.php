<?php

defined('M5CPL') or die('Access deny! in: '.__FILE__);
define('DS', DIRECTORY_SEPARATOR);
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

class P2HConfig{
	/**
	 * 私有化方法防止new和克隆静态类
	 */
	private function __construct(){}
	private function __clone(){}
	
	
	//成功标志
	const SUCCESS = 1;
	
	//失败标志
	const FAIL = 0;
	
	/**
	 * 新生儿-代表是ajax伪静态文件
	 * 因为RW生成的html是ajax伪静态文件，和最终的html不一样，所以要加个参数区分一下。
	 * @var string
	 */
	const NEWBORN = 'newborn';
	
	
	/**
	 * 强制更新静态页的标志符
	 * 项目中有时候会要强制更新
	 * @var string
	 */
	const FRESH = 'u';
	
	
	/**
	 * php地址
	 * 静态页请求更新的时候会在url后面加此参数说明是哪个php地址生成的,用于后面的更新。
	 * @var string
	 */
	const PHP_SELF = 'url';
	
	
	/**
	 * 调试模式:
	 * 0关闭调试
	 * 1开启调试并把错误打印在屏幕上
	 * 2开启调试并把错误保存在文件中
	 * @var int
	 */
	private static $debug = 1;
	
	
	/**
	 * 日志的路径
	 * @var String
	 */
	private static $debugFile = './p2h_error.log';
	
	/**
	 * 是否生成静态
	 * @var boolen
	 */
	public static $isStatic = true;
	
	/**
	 * 是否压缩html
	 * @var boolen
	 */
	public static $minify = true;
	
	/**
	 * 当前应用的URL
	 * @example http://www.xda.cn/
	 * @var 
	 */
	public static $rootURL= '';
	
	
	
	/**
	 * 当前php的文件名，不包括.php后缀
	 * @var String
	 */
	public static $phpName = '';
	
	
	/**
	 * 静态页请求更新的URL
	 * 静态页和php在不同服务器的情况下
	 * 比如a服务器放html，b服务器放php
	 * 用户访问网页时访问a，而a请求b更新html，如果静态页过期了，b把更新好的html同步到a。
	 * 如果在同一个服务器下，设置为相同的地址就行了
	 * @var String
	 */
	public static $updateURL = '';
	
	/**
	 * 
	 * @var String 重写规则
	 */
	private static $rwRule = '_';
	
	/**
	 * 
	 * @var String 重写扩展名
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
	 * 当前路径
	 * @var String
	 */
	private static $rootPath = './';
	
	/**
	 * P2H的路径
	 * @var String
	 */
	public static $p2hPath = '';
	
	
	/**
	 * html路径 
	 * @example D:/www/index/templates/index.html
	 * @var String
	 */
	public static $rwPath = '';	
	
	
	/**
	 * 重写过的URL
	 * @example http://news.xda.cn/20121212/123.html
	 * @var String
	 */
	public static $rwURL = '';
	
	//html的存放目录
	public static $htmlsDir;
	
	/**
	 * $_REQUEST数组
	 * @var Array
	 */
	public static $req = array();
	
	/**
	 * 静态页有效时间(秒)，默认1小时
	 * @var 
	 */
	public static $timeout = 3600;
	
	public static $dir = array();
	
	/**
	 * ajax伪静态html模板
	 * RW后的html是带有ajax请求更新的，当用户访问这个文件的时候，
	 * 请求更新，php会把内容覆盖这个文件，于是内容就呈现出来了。
	 * @var String
	 */
	private static $ajaxTpl = '<!DOCTYPE html><html><head><meta charset="utf-8">
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="Sunday 26 October 2008 01:00 GMT" />  
<script type="text/javascript" src="@JQURL@"></script>
<script> 
$(function(){
	$.get("@ROOTURL@?@QUERY@", function(data){
		setTimeout(function(){
			location.reload(true);
		},1000);
	});     
});
</script></head><body></body></html>';
	
	/**
	 * jquery url
	 * @var String
	 */
	private static $jqueryURL = 'http://jqueryjs.googlecode.com/files/jquery-1.2.min.js';
	
	public function init($config){
		foreach($config as $k=>$v) {
			self::set($k, $v);
		}
	}
	
	public static function pageInfo($path){
		$pageInfo = self::get('pageInfo');
		return isset($pageInfo[$path]) ? $pageInfo[$path] : null;
	}
	
	public static function rootURL($path){
		$pageInfo = self::pageInfo($path);
		return isset($pageInfo['rootURL']) ? $pageInfo['rootURL'] : null;
	}
	
	public static function rootPath($path){
		return self::$appPath.$path;
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
			P2HLog::write('unknow preperty $'.$key);
		
		self::$$key = $value;
		
	}
	
	
	/**
	 * 返回由类的默认属性组成的数组
	 * @return Array
	 */
	public function getVars(){
		return get_class_vars(get_class());
	}

}//P2HConfig end





/**
 * 处理P2H相关的文件
 *
 */
class P2HFile{
	/**
	 * 删除无效的静态文件
	 * @param String $htmlPath
	 */
	public static function delHTML($htmlPath) {
		if(!file_exists($htmlPath)) return true;
		
		chmod($htmlPath, 0777);
		if(false===unlink($htmlPath))
			self::debug('fail to delete this file '.$htmlPath, __LINE__);
		
	}
	
	/**
	 * 创建目录
	 * @param String $dirname
	 */
	public static function mkDir($dirname) {
		if(!is_dir($dirname)) {
			if(false===mkdir($dirname, 0777))
				P2HLog::write("mkdir failed: ".$dirname);
		}
	}
	
	/**
	 * 得到静态目录下所有文件夹和文件
	 * string $root 目录路径
	 * array $extensions 扩展名
	 * return array('files'=>array(...),'dirs'=>array(...))
	 *
	 */
	private function htmlList() {
		$ingore = array('templates', 'PPL', 'module');
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
						if(in_array($file, $ingore)){
							continue;
						}
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
	

}//P2HFile end




/**
 * 记录P2H日志
 *
 */
class P2HLog{
	/**
	 * 记录日志的级别
	 * 0:不记录
	 * 1：输出到屏幕
	 * 2：写入文件
	 */	
	public static $debugLevel = 1;
	
	private $debugFile = 'P2HError.log';
	
	/**
	 * 记录日志
	 * @param mixed $msg
	 */
	public function write($msg, $line = '') {
		if(self::$debugLevel==2){
			self::mkDir(dirname($this->$debugFile));
		}	
		$msg = '['.date('Y-m-d H:i:s').'] {'.get_class().' msg} '.$msg;
		if(!empty($line)) $msg .= ' throw in line '.$line;
		
		switch (intval(self::$debugLevel)) {
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
}




class P2HPath{
	public static function RWURL($url='', $time=0){
		if(!P2HConfig::get('isStatic')) return $url;	
		
		$htmlPath = self::getHtmlPath($url);
		
		$htmlPath = basename($htmlPath, P2HConfig::get('rwEnd'));

		$rootURL = self::getRootURL($url);
		//D($rootURL);
		$result = self::repairPath(self::getRootURL($url)).$htmlPath.P2HConfig::get('rwEnd');

		return $result;
	}
	
	public static function RWPath($url='', $time=0){
		if(!P2HConfig::get('isStatic')) return $url;	
		
		$htmlPath = self::getHtmlPath($url);
		$rootURL = self::getRootURL($url);

		return $result;
	}
	
	public static function phpName($url){
		$url = parse_url($url);
		return basename($url['path'], '.php');
	}

	/**
	 * 获得合法的request数组，符合配置里的args
	 * @param String $url
	 */
	public static function getArgs($url='') {
		$rpath = self::getRelativePath($url);
		$pageInfo = P2HConfig::pageInfo($rpath);
		
		$req = $result = array();
		if(!empty($url)){
			$urlinfo = parse_url($url);
			parse_str($urlinfo['query'], $req);
		}else{
			$req = $_REQUEST;
		}
		//D($req);

		if(isset($pageInfo['args'])){
			foreach($pageInfo['args'] as $v){
				if(isset($req[$v])){
					$result[$v] = $req[$v];
				}
			}
		}

		return $result;
	}
	
	//RW QUERY
	public static function rwArgs($url=''){
		$rwArgs = '';
		$args = self::getArgs($url);

		//删掉末尾的清一色0
		$args = array_values($args);
		if(!is_array($args) || empty($args)){
			return self::phpName($url);
		}

		for($i=count($args)-1; $i>=0; $i--) {
			if($args[$i]==0) {
				unset($args[$i]);
			}else break;
		}

		$rwRule = P2HConfig::get('rwRule');
		foreach($args as $v){
			$rwArgs .= $v.$rwRule;
		}
		$rwArgs = rtrim($rwArgs, $rwRule);
		
		//D($rwArgs);
		return $rwArgs;
	}
	
	/**
	 * 修复路径
	 * @param String $path
	 */
	private function repairPath($path){
		$path= str_replace('\\', '/', $path);//	把\替换成/
		$path = rtrim($path, '/').'/'; //	保证最后一个字符是/
		return $path;
	}
	
	public static function getHtmlPath($url='', $time=0){
		$time = intval($time);
		$result = '';

		$args = self::rwArgs($url);
		$dateDir = self::dateDir($url, $time);
		return $dateDir.$args.P2HConfig::get('rwEnd');
	}
	
	public static function getRootURL($url){
		$rpath = self::getRelativePath($url);
		return P2HConfig::rootURL($rpath);
	}
	
	
	
	public static function dateDir($url, $time){
		$result = '';
		if(self::dealDir($url)!='.' && $time>0){
			$result = date('Ymd', $time);
			P2HFile::mkDir($result);
			
			$result = $result.'/';
			
		}
		return $result;		
	}
	
	
	/**
	* 获取相对于根目录appPath的相对路径
	* 如果是根目录 返回php文件名
	* D(self::$appPath.'        -      '.$_SERVER['PHP_SELF']);
	* D(self::$appPath.'        -      '.$_SERVER['PHP_SELF']);
	* D:\software\xampp\htdocs\p2h_svn\demo        -      /p2h_svn/demo/news/index.php"
	* D:\software\xampp\htdocs\p2h_svn\demo        -      /p2h_svn/demo/news/it/index.php"
	* 	相减再处理下就是当前目录相对根目录的距离 news和news/it
	*/
	public static function getRelativePath($url=''){
		
		$dir = self::dealDir($url);

		if($dir=='.'){
			$page = self::phpName($url);		
		}else{
			$page = $dir;
		}

		return $page;
	}
	
	public static function dealDir($url=''){
		$appPath = P2HConfig::get('appPath');
		
		if(empty($url)){
			$url = $_SERVER['PHP_SELF'];
			$appPathE = explode(DS, $appPath);
		
			foreach($appPathE as $v){
				$url = str_replace($v, '', $url);
			}
		}else{
			$urlinfo = parse_url($url);
			$url = $urlinfo['path'];
		}
		//D($url);
		$url = rtrim($url, '.php');
		$dir = str_replace('./', '', $url);
		$dir = str_replace('//', '', $dir);
		return $dir;
	}
	
	
}//P2HPath end





class P2H {
	
	/**
	 * 私有化方法防止new和克隆静态类
	 */
	private function __construct(){}
	private function __clone(){}
	
	
	/**
	 * Init初始化 载入配置 检查更新 打开ob
	 * 
	 */
	public static function init($date=0){
		//D(self::$config);
		//如果不静态化，返回
		if(!P2HConfig::get('isStatic')){
			return;
		}
	
        self::checkUpdate();
                
		self::ob_end();
		ob_start();

	}
	
	
	/**
	 * 载入配置文件
	 * @param Array $config
	 */
	public static function initConfig($config) {
		P2HConfig::init($config);
	}
	
	
	
	/**
	 * 生成静态
	 * @return boolen
	 */
	public static function toHTML($url) {
		if(!self::$isStatic || self::$req[self::NEWBORN]=='true') return;
		$data = ob_get_contents();		
		$data = self::insertBetween($data, self::loadScript($url), '</body>');
		if(phpversion() >= '5.3') $data = self::minify($data);
			
		$flag = file_put_contents(self::$tplPath, $data);

		unset($data);
		self::ob_end();
		//D(self::getVars());
		if(!isset(self::$req[self::FROM]) && file_exists(self::$tplPath)) self::jump();
		if($flag)
			self::showStatus(array('status'=>1));
		else self::showStatus(array('status'=>0));
	}
	
	/**
	 * 发送更新请求
	 */
	public static function update() {
		if(!isset(self::$req[self::LOCATION]) || trim(self::$req[self::LOCATION])=='')
			self::showStatus(array('status'=>'00'));
		$ch = curl_init();
		$url = str_replace(self::$rootURL, self::$updateURL, self::$req[self::LOCATION]);
		$options = array(
				CURLOPT_TIMEOUT=>30,
				CURLOPT_URL=>$url,
				CURLOPT_HEADER=>false,
		);
		
		curl_setopt_array($ch, $options);

		if(false===curl_exec($ch))
			self::showStatus(array('status'=>'01', 'url'=>$url));
			
		curl_close($ch);
	}
	
	private function showStatus($status) {	
		echo self::$req['callback'].'('.json_encode($status),')';
		exit;
	}
	
	
	
	
	
	/**
	 * 重写地址并生成伪静态文件
	 * @param String $url
	 */
	public static function RW($url, $time=0) {
	return;
		if(!self::$isStatic) return $url;
		$rw = P2HPath::RWURL($url, $time);

		if($rw['dir']){
			$appPath=self::$appPath.$rw['dir']."/";
		}else{
			$appPath=self::$appPath;
		}
		$filename = str_replace($rw['root'], $appPath, $rw['url']);
		//D($filename);
		$flag = self::buildAjax($url, $filename);
		if(false===$flag) self::debug('create ajax failed', __LINE__);
		return $rw['url'];
	}
	
	/**
	 * 重写地址
	 * @param String $url
	 * @return String rwurl
	 */
	public static function RWURL($url='', $time=0) {
		return P2HPath::RWURL($url, $time);
	}
	
	/**
	 * 返回重写之前的地址
	 * @param String $url
	 * @return String http://localhost/web_3.0/news/20121120/20.html
	 */
	public static function UnRWURL($url) {
		if(!self::$isStatic) return $url;
		
		if(strpos($url, self::$rootURL)===false)
			return self::$rootURL.'index.php';

		$urlinfo = parse_url($url);
		
		$rootURLInfo = parse_url(self::$rootURL);
		
		$dirpath = str_replace($rootURLInfo['path'], '', $urlinfo['path']);
		//D($rootURLInfo['path'].'       '. $urlinfo['path']);
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
	 * 生成带有ajax请求的伪静态文件
	 * @param String $url
	 * @param String $filename
	 */
	private function buildAjax($url, $filename) {
		if(is_file($filename)) return;
		$dq = self::dq($url);
		$dirs = explode('/', $dq['dir']);
		$dir = $dirs[0];
		$query = $dq['query'];
		
		//self::mkHtmlDir($url);
		$querys = '';
		if(is_array($query) && !empty($query)) {
			foreach(self::$pageInfo[$dir]['args'] as $k=>$v) {
				if(isset($query[$v]))	$querys .= "{$v}={$query[$v]}&";
			}
		}

		$updateURL = self::$updateURL.$dir.'.php';
		$rootURL = self::$rootURL.$dir.'.php';
		$search = array('@JQURL@', '@URL@', '@QUERY@');
		$replace = array(self::$jqueryURL, $updateURL, $querys.self::FRESH."=true");
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
	return;
	//fresh=true时强制更新 firsttime代表页面是第一次生成 这两种情况都要生成静态页
		if((isset(self::$req[self::FRESH]) && trim(self::$req[self::FRESH])==='true') ||
		(isset(self::$req[self::FIRST_TIME]) && trim(self::$req[self::FIRST_TIME])==='true'))
			return true;

		//设置超时时间
		if(isset(self::$pageInfo[self::$htmlDirName]) && isset(self::$pageInfo[self::$htmlDirName]['timeout'])) 
			self::$timeout = intval(self::$pageInfo[self::$htmlDirName]['timeout']);
			
		$mtime = file_exists(self::$tplPath) ? filemtime(self::$tplPath) : 0;
		//D(time() - $mtime);
		if(time() - $mtime > self::$timeout) return true;
		
		else return false;		
	}
	
	/**
	 * 检查条件是否为真 如果假 不更新静态页
	 * @param boolen $condition
	 */
	public static function check($arr) {
		if(!is_array($arr) || empty($arr))
			exit;
	}
	
	/**
	 * 检查静态页更新
	 * 这个方法在init里头调用了, 所以不需要更新的时候要直接exit终止掉
	 * 不然会一直走完整个php文件直到末尾的获得ob缓冲并重新生成静态
	 */
	private function checkUpdate() {
	return;
		if(self::isTimeout()) {
			return;
		}elseif(isset(self::$req[self::FROM]) && self::$req[self::FROM]=='html') {
			exit;
		}else self::jump();
	}
	
	/**
	 * 跳转
	 */
	private function jump($url = '') {
		if(trim($url)=='') {
			$url = self::$tplURL;
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
	private function loadScript($url) {
		if(!self::$isStatic) return;
		
		//更新静态页的JS, 模板是P2H.php同级目录下的P2H.JS
		$filename = self::$p2hPath.'P2H.js';
		if(!is_file($filename)) self::debug('can not find '.$filename, __LINE__);

		$data = file_get_contents($filename);
		if(empty($data)) self::$debug('P2H.js is empty');
		
		$search = array('@JQURL@', '@updateURL@', '@phpURL@');
		$replace = array(self::$jqueryURL, self::$rootURL.'P2HUpdate.php', $url);
		$data = str_replace($search, $replace, $data);
		if(phpversion() >= '5.3') $data = self::minify($data, 'JSMin');
		return $data;
	}
	
	public static function getHTMLList(){
		$list = P2HFile::htmlList();
		return $list['files'];
	}
	

}//P2H class end

?>