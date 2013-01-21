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
	const LOCATION = 'location';
	
	
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
	private static $debugDir = './';
	
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
	const AJAX_TPL = <<<EOF
	<!DOCTYPE html><html><head><meta charset="utf-8">
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="Sunday 26 October 2008 01:00 GMT" />  
<script type="text/javascript" src="@JQURL@"></script>
<script> 
$(function(){
	$.get("@ROOTURL@?@QUERY@", function(data){
		setTimeout(function(){
			location.reload(true);
		},300);
	});     
});
</script></head><body></body></html>
EOF;
	const UPDATE_JS = <<<EOF
	<script type="text/javascript">

updateHTML('@JQURL@', '@updateURL@', '@phpURL@');

function updateHTML(JQURL, updateURL, phpURL) {
	if(typeof jQuery==='undefined') {
		loadScriptOnce(JQURL, function(){
			askToUpdate(updateURL, phpURL);
		});
	}else{
			askToUpdate(updateURL, phpURL);
	}
}

function askToUpdate(updateURL, phpURL) {
	$.getJSON(
			updateURL+"?callback=?&"+phpURL,
			function(data) {				
			}
		);
}

function loadScriptOnce(url,callback) {
    var script=document.createElement("script");
    script.type="text/javascript";
    if(script.readyState){
        script.onreadystatechange=function(){
            if(script.readyState=='loaded'||script.readyState=='complete'){
                script.onreadystatechange=null;
                callback();
            }
        }
    }else{script.onload=function(){callback();}}
    script.src=url;
    document.getElementsByTagName("head")[0].appendChild(script);
}
</script>
EOF;
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

	/**
	 * 获得根路径
	 *@example news/news : news, news/index : news
	 * @param unknown_type $path
	 * @return unknown
	 */
	public static function rootPath($path=''){
		if(empty($path) || $path=='index'){
			return self::$appPath;
		}
		//$path = str_replace(array("/", "\\"), DS, $path);
		
		if(dirname($path)!='.') $path = DS.dirname($path);
		else{
			//P2HLog::write('dirname($path)=. : '.$path);
			$path = '';	
		}
		return self::$appPath.$path;
	}
	
	public static function timeout(){
		$rpath = P2HPath::getRelativePath();
		$pageInfo = self::pageInfo($rpath);
		return isset($pageInfo['timeout']) ? $pageInfo['timeout'] : 0;
	}
	
	/**
	 * 获取类属性
	 * @param String $key
	 */
	public static function get($key) {
		$key = trim($key);       
     	
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
	 * 压缩
	 * @param String $data
	 * @param String $type
	 */
	public static function minify($data, $type = 'HTML') {
		
		if(!P2HConfig::get('minify')) return $data;
		
		$type = trim($type);

		require_once P2HConfig::get('p2hPath').'/plugin/'.$type.'.php';

		return $type::minify($data);
		
	}
	
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
	public static function htmlList() {
		$ingore = array('templates', 'PPL', 'module');
		$extensions = array(ltrim(P2HConfig::get('rwEnd'), '.'));
		$root = P2HConfig::get('appPath');
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

	private static $log = '';
	
	/**
	 * 记录日志
	 * 这里先保存到一个数组中，到了析构函数再执行写入
	 * @param mixed $msg
	 */
	public function write($msg, $line = ''){
		$msg = '['.date('Y-m-d H:i:s').'] {'.get_class().' msg} '.$msg;

		switch (intval(P2HConfig::get('debug'))){
			case 1:
				exit($msg);
				break;
			
			case 2:
				$debugDir = P2HConfig::get('debugDir');
				P2HFile::mkDir($debugDir);
	
				$logFile = $debugDir.DS.date('Y_m_d').'.log';
				file_put_contents($logFile, $msg.PHP_EOL, FILE_APPEND);
				break;
				
			case 3: 
				var_dump(json_encode($msg));
				break;
				
			default:
				return;
		
		}
		
		
	}
	
	public function __destruct(){
		//file_put_contents(P2HConfig::get('debugFile'), self::$log.' >< ', FILE_APPEND);
		/*
		switch (intval(P2HConfig::get('debug'))){
			case 1:
				exit($msg);
				break;
			
			case 2:
				P2HFile::mkDir(dirname(P2HConfig::get('debugFile')));
				file_put_contents(P2HConfig::get('debugFile'), $msg.PHP_EOL, FILE_APPEND);
				break;
				
			case 3: 
				var_dump(json_encode($msg));
				break;
				
			default:
				return;
		
		}
		*/
	}
	
	
}//P2HLog end




class P2HPath{
	//http://localhost/p2h_svn/demo/news/20121208/3.html
	public static function RWURL($url='', $time=0){
		$rootURL = self::getRootURL($url);
		//P2HLog::write('url:'.$url.'=>rootURL=>'.$rootURL);
		if(!P2HConfig::get('isStatic')){
			$purl = parse_url($url);
			if($purl['query']) $query='?'.$purl['query']; 
			$result = $rootURL.P2HPath::phpName($url).'.php'.$query;
		}else{
			$htmlPath = self::getHtmlPath($url, $time);
			$result = self::repairPath($rootURL).$htmlPath;
		}
		
		return $result;
	}
	
	public static function RWPath($url='', $time=0){
		if(!P2HConfig::get('isStatic')) return;	

		$htmlPath = str_replace('/', DS, self::getHtmlPath($url, $time));
		$rpath = self::getRelativePath($url);
		
		$dir = dirname($rpath);
		if($dir!='.'){
			$dirs = explode('/', $dir);
			$mdir = P2HConfig::get('appPath');
			foreach($dirs as $v){
				$mdir .= DS.$v;
				P2HFile::mkDir($mdir);
			}
			
			$dateDir = self::dateDir($url, $time);
			if($dateDir){
				P2HFile::mkDir($mdir.DS.$dateDir);
			}
		}
		
		//P2HLog::write('url:'.$url.';rpath:'.$rpath.';dir:'.$dir.';mdir:'.$mdir.';dateDir:'.$dateDir.PHP_EOL);
		
		$result = P2HConfig::rootPath($rpath).DS.$htmlPath;
		return $result;
	}
	
	public static function phpName($url=''){
		if(empty($url)){
			$url = $_SERVER['PHP_SELF'];
		}
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
				$result[$v] = intval($req[$v]);
			}
		}

		return $result;
	}
	
	//RW QUERY
	public static function rwArgs($url=''){
		$rwArgs = '';
		$args = self::getArgs($url);
		if(!is_array($args) || empty($args)){
			return self::phpName($url);
		}
		
		//删掉末尾的清一色0
		$args = array_values($args);
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
		return empty($rwArgs) ? 'index' : $rwArgs;
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
	
	//20121221/3.html
	public static function getHtmlPath($url='', $time=0){
		$time = intval($time);
		$result = '';

		$args = self::rwArgs($url);
		$dateDir = self::dateDir($url, $time);
	
		return $dateDir.$args.P2HConfig::get('rwEnd');
	}
	
	public static function getRootURL($url=''){
		$rpath = self::getRelativePath($url);
		//P2HLog::write('url:'.$url.'=>rpath=>'.$rpath);
		return P2HConfig::rootURL($rpath);
	}
	

	public static function dateDir($url, $time){
		$result = '';
		//如果php是index.php，那么静态页不需要放在日期文件夹里
		if(self::phpName($url)!='index' && $time>0){
			$result = date('Ymd', $time);			
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
	
	//	news/news
	public static function dealDir($url=''){
		$appPath = P2HConfig::get('appPath');
		
		if(empty($url)){
			$url = $_SERVER['SCRIPT_FILENAME'];
			$appPathE = explode(DS, $appPath);
			foreach($appPathE as $v){
				$url = preg_replace("/{$v}/", '', $url, 1);
			}
		}else{
			$urlinfo = parse_url($url);
			$url = $urlinfo['path'];
		}
		
		$url = rtrim($url, '.php');
		$dir = str_replace(array('./', '//'), '', $url);
		$dir = ltrim($dir, '/');
		return $dir;
	}
	
	
}//P2HPath end





class P2H {
	private static $rwPath = '';
	public  static $rwURL = '';
	
	/**
	 * 私有化方法防止new和克隆静态类
	 */
	private function __construct(){}
	private function __clone(){}
	
	
	/**
	 * Init初始化 载入配置 检查更新 打开ob
	 * 
	 */
	public static function init($time=0){

		if(!P2HConfig::get('isStatic')){
			return;
		}
		
		self::$rwPath = self::RWPath($time);
		self::$rwURL = self::RWURL($time);
		
        self::checkUpdate($time);
                		
		self::ob_end();
		ob_start();

	}
	
	
	/**
	 * 载入配置文件
	 * @param Array $config
	 */
	public static function initConfig($config) {
		if(!P2HConfig::get('isStatic')){
			return;
		}
		P2HConfig::init($config);
	}
	
	
	/**
	 * 重写地址并生成伪静态文件
	 * @param String $url
	 */
	public static function RW($url, $time=0) {
		$rw = P2HPath::RWURL($url, $time);

		$flag = self::buildAjax($url, $time);
		if(false===$flag) P2HLog::write('create ajax failed:'.$rw);
		return $rw;
	}
	
	/**
	 * 重写地址
	 * @param String $url
	 * @return String rwurl
	 */
	public static function RWURL($time=0, $url='') {
		return P2HPath::RWURL($url, $time);
	}
	
	/**
	 * 返回重写之前的地址
	 * @param String $url
	 * @return String http://localhost/web_3.0/news/20121120/20.html
	 */
	public static function UnRWURL($url) {
		if(!P2HConfig::get('isStatic')){
			return $url;
		}
		
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
	
	public static function RWPath($time=0, $url=''){
		return P2HPath::RWPath($url, $time);
	}
	
	/**
	 * 生成静态
	 * @return boolen
	 */
	public static function toHTML(){
		//P2HLog::write('rwurl:'.self::$rwURL.';   rwpath: '.self::$rwPath);
		if(!P2HConfig::get("isStatic")) return;
		$data = ob_get_contents();
		$data = self::insertBefore($data, self::loadScript(), '</head>');
		
		if(phpversion() >= '5.3') $data = P2HFile::minify($data, 'HTML');
		$flag = file_put_contents(self::$rwPath, $data);

		unset($data);
		self::ob_end();
		$req = P2HConfig::get('req');
		$location = $req[P2HConfig::LOCATION];
		$new = $req[P2HConfig::NEWBORN];

		if(!isset($location) && !isset($new) && file_exists(self::$rwPath)){
			self::jump();
		}

	}

	/**
	 * 检查静态页更新
	 * 这个方法在init里头调用了, 所以不需要更新的时候要直接exit终止掉
	 * 如果是php访问要直接跳转到静态页
	 */
	private function checkUpdate($time=0) {
		$req = P2HConfig::get('req');
		if(self::isTimeout($time)){
			//P2HLog::write('<timeout>'.self::$rwURL.':'.P2HConfig::timeout());
			return;
		}elseif(isset($req[P2HConfig::LOCATION])){
			exit;
		}else self::jump();
	}
	
	/**
	 * 发送更新请求
	 */
	public static function update($url='') {
	//return;
		if(empty($url)){
			$req = P2HConfig::get('req');
			$location = urldecode($req[P2HConfig::LOCATION]);
		}else{
			$location = $url;
		}
		
		$ch = curl_init();
		$options = array(
				CURLOPT_TIMEOUT=>30,
				CURLOPT_URL=>$location,
				CURLOPT_HEADER=>false,
		);
		
		curl_setopt_array($ch, $options);

		if(false===curl_exec($ch)){
			$flag = P2HConfig::FAIL;
		}else{
			$flag = P2HConfig::SUCCESS;			
		}
		curl_close($ch);
		
		self::showStatus(array('flag'=>$flag, 'rw'=>P2HPath::RWURL($location), 'original'=>$location));
	}
	
	private function showStatus($status){
		$req = P2HConfig::get('req');
		echo $req['callback'].'('.json_encode($status).')';
		exit;
	}
	
	/**
	 * 生成带有ajax请求的伪静态文件
	 * @param String $url
	 * @param String $filename
	 */
	private function buildAjax($url, $time){
		if(!P2HConfig::get('isStatic')) return;
		$rwPath = self::RWPath($time, $url);
			
		if(is_file($rwPath)) return;
		
		$args = P2HPath::getArgs($url);
		
		$querys = self::buildQuery($args).'&'.P2HConfig::NEWBORN.'=true' ;

		$search = array('@JQURL@', '@ROOTURL@', '@QUERY@');
		
		$replace = array(P2HConfig::get('jqueryURL'), P2HPath::getRootURL($url).P2HPath::phpName($url).'.php', $querys);
		$tpl = str_replace($search, $replace, P2HConfig::AJAX_TPL);
		return file_put_contents($rwPath, $tpl);
	}
	
	private static function buildQuery($args){
		$querys = '';
		if(is_array($args) && !empty($args)) {
			foreach($args as $k=>$v) {
				$querys .= "{$k}={$v}&";
			}
		}
		$querys = rtrim($querys, '&');
		
		return $querys;
	}
	
	/**
	 * 在文档的某个位置插入内容
	 * @param String $data
	 * @param String $insert
	 * @param String $delimiter
	 */
	private function insertBefore($data, $insert, $delimiter = '</body>') {
		if(strpos($data, $delimiter)===false)
			P2HLog::write("delimiter <".strip_tags($delimiter)."> not found in html");
		
		$tpls = explode($delimiter, $data);
		return $tpls[0].$insert.$delimiter.$tpls[1];
	}
	
	/** 
	 * 静态页是否超过有效期
	 * @return boolen
	 */
	private function isTimeout($time=0) {
		//fresh=true时强制更新 NEWBORN代表页面是ajax伪静态文件 这两种情况都要生成静态页
		$req = P2HConfig::get('req');
		$fresh = $req[P2HConfig::FRESH];
		$new = $req[P2HConfig::NEWBORN];
		if(isset($fresh) || isset($new)){
			return true;
		}

		$timeout = P2HConfig::timeout();			
		$mtime = file_exists(self::RWPath($time)) ? filemtime(self::RWPath($time)) : 0;
		if(time() - $mtime > $timeout) return true;
		
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
	 * 跳转
	 */
	private function jump($url = '') {
		if(trim($url)=='') {
			$url = self::$rwURL;
		}		
		
		if(!headers_sent()) header('Location: '.$url);
		else{
			echo <<<EOF
			<script type="text/javascript">
				self.location="{$url}"
			</script>
EOF;
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
	 * 加载发出更新请求的JS
	 */
	private function loadScript() {
		$data = P2HConfig::UPDATE_JS;
		
		$args = self::buildQuery(P2HPath::getArgs());
		if(!empty($args)) $args = '?'.$args;
		$phpURL = P2HPath::getRootURL().P2HPath::phpName().'.php'.$args;
		$search = array('@JQURL@', '@updateURL@', '@phpURL@');
		$replace = array(P2HConfig::get('jqueryURL'), P2HConfig::get('updateURL').'P2HUpdate.php', P2HConfig::LOCATION.'='.urlencode($phpURL));
		$data = str_replace($search, $replace, $data);
		$data = P2HFile::minify($data, 'JSMin');
		return $data;
	}
	
	public static function getHTMLList(){
		$list = P2HFile::htmlList();
		return $list['files'];
	}
	

}//P2H class end

?>