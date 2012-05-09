<?php
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
 *    此时如果返回写入静态文件成功的状态码, 重新载入该页面而达到展现, 更新亦是如此
 * 
 * @example
 * P2H::Init($config);
 * include './templets/list_index.html';
 * P2H::ToHtml();
 */

class P2H {
	
	/**
	 * 调试模式:
	 * 0关闭调试
	 * 1开启调试并把错误打印在屏幕上
	 * 2开启调试并把错误保存在文件中 文件保存在log/p2herror.txt
	 * @var int
	 */

	private static $debug = 0;
	
	/**
	 * 错误日志路径
	 * @var String
	 */
	private static $debugFile = './p2herror.log';
	
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
	private static $ajaxTpl = '<html><head>
    <script type="text/javascript" src="@JQURL@"></script>
	<script type="text/javascript">
	$(function() {
		$.getJSON(
			"@URL@@QUERY@",
			function(data){
                                if(window.console&&window.console.log){
                                   window.console.log(JSON.stringify(data));
                                   window.console.log(data.status);
                                   
                                }
				if(data.status==0){
                                    if(window.console&&window.console.log){
                                       window.console.log(0);
                                    }
                                    location.href=data.url;
                                }else { 
                                    if(window.console&&window.console.log){
                                       window.console.log(0);
                                    }
                                    location.reload(true);
                                }
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
	 * 
	 */
	public static function init() {
		if(!self::$isStatic) return;
		//ensure that dir is exists
		self::mkHtmlsDir();				
		self::set('dir', self::getHtmlDir());
		self::mkHtmlDir();
		
		//set tplPath
		$rw = self::joinArgs(array('dir'=>self::$dir, 'query'=>self::$req));
		
		$footer = self::$htmls.'/'.self::$dir.'/'.$rw.self::$rwEnd;
		self::set('tplPath', self::$appPath.$footer);
		self::set('tplURL', self::$rootURL.$footer);
		//D(self::getVars());
		self::checkUpdate();

		self::ob_end();
		ob_start();

	}
	
	/**
	 * 载入配置文件
	 * @param Array $config
	 */
	public static function initConfig($config) {
		//$config must be array and not empty
		self::checkConfig($config);
	
		foreach($config as $k=>$v) {
			self::set($k, $v);
		}
		
	}
	
	/**
	 * 生成静态
	 * @return boolen
	 */
	public static function toHTML() {
		if(!self::$isStatic) return;
	
		$data = ob_get_contents();
		$flag = false;
	
		if(phpversion() >= '5.3') $data = self::minify($data);
			
		$flag = file_put_contents(self::$tplPath, $data);

		unset($data);
		self::ob_end();
	
		if(isset(self::$req['from']) && isset(self::$req['jsoncallback'])) {
			if(self::$req['from']=='ajax') {
				if(false!==$flag) $status=array('status'=>'1');
				else $status = array('status'=>'0', 'url'=>"'".self::$rootURL."'");
	
				echo self::$req['jsoncallback'].'('.json_encode($status),')';
				exit;
			}
		}else self::jump();
	
	}
	
	/**
	 * 发送更新请求
	 */
	public static function update() {
		if(!isset(self::$req['location']) || trim(self::$req['location'])=='')
			die(json_encode(array('status'=>'00')));
		
		
		$ch = curl_init();		
		$options = array(
				CURLOPT_TIMEOUT=>20,
				CURLOPT_URL=>str_replace(self::$rootURL, self::$updateURL, self::UnRWURL(self::$req['location'])),
				CURLOPT_HEADER=>false,
		);
		
		curl_setopt_array($ch, $options);

		if(false===curl_exec($ch)) die(json_encode(array('status'=>'01')));
		curl_close($ch);
		
		//fopen(str_replace(self::$rootURL, self::$updateURL, self::UnRWURL(self::$req['location'])), 'r');
	}
	
	/**
	 * 检查载入的配置的格式是否正确
	 * @param Array $config
	 */
	private static function checkConfig($config) {
		if(!is_array($config))
			self::debug('$config must be array when init($config)', __LINE__);
		if(empty($config))
			self::debug('$config is empty when init($config)', __LINE__);
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
	 * 删除无效的静态文件
	 * @param String $htmlPath
	 */
	private static function delHTML($htmlPath) {
		if(!file_exists($htmlPath)) return true;
		
		chmod($htmlPath, 0777);
		if(false===unlink($htmlPath))
			self::debug('fail to delete this file '.$htmlPath, __LINE__);
		
	}
	
	/**
	 * 创建目录
	 * @param String $dirname
	 */
	private static function mkDir($dirname) {
		if(!is_dir($dirname)) {
			if(false===mkdir($dirname, 0777))
				self::debug("mkdir failed: ".$dirname, __LINE__);
		}
	}
	
	/**
	 * 创建静态页总的文件夹 eg:E:/app/htmls/
	 */
	private static function mkHtmlsDir() {
		if(!isset(self::$htmls) || empty(self::$htmls))
			self::debug('please set "htmls" to array $config  when init($config). eg:E:/html/', __LINE__);
		
		$dirname = self::$appPath.self::$htmls;
		
		self::mkDir($dirname);
	}
	
	/**
	 * 创建栏目静态页的文件夹 eg:E:/app/htmls/list/
	 * @param String $dir
	 */
	private static function mkHtmlDir($dir = '') {
		$dirname = self::$appPath.self::$htmls.'/';
		
		if(!empty($dir))	$dirname .= $dir;
		else $dirname .= self::$dir;

		self::mkDir($dirname);
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
		if(!self::$isStatic) return $url;
		
		$dir = basename(dirname($url));

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
		
		return self::$rootURL.$dir.'.php'.$query;
	}
	
	/**
	 * 拼接参数值
	 * @param Array $dq
	 */
	private static function joinArgs($dq) {
		
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
	 * 得到html目录名和query数组
	 * @param String $url
	 */
	public static function dq($url) {
		$urlinfo = parse_url($url);
		
		$query = '';
		if(isset($urlinfo['query']) && !empty($urlinfo['query']))
			parse_str($urlinfo['query'], $query);
		
		$dir = basename($urlinfo['path'], '.php');
		
		foreach(self::$pageInfo[$dir]['args'] as $v) {
			if($query[$v]) $querys[$v] = $qu;
		}
		return array('dir'=>$dir, 'query'=>$query);
	}
	
	/**
	 * 得到页面的html子目录
	 * 
	 */
	public static function getHtmlDir() {
		$urlinfo = parse_url($_SERVER['PHP_SELF']);
		return basename($urlinfo['path'], '.php');
	}
	
	/**
	 * 生成带有ajax请求的伪静态文件
	 * @param String $url
	 * @param String $filename
	 */
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
				
		$ajaxTpl = self::$ajaxTpl;
		
		$url = self::$updateURL.$dir.'.php';
		$search = array('@JQURL@', '@URL@', '@QUERY@');
		$replace = array(self::$jqueryURL, $url, $querys);
		
		$delimiter = '<body>';
		$tpls = explode($delimiter, $ajaxTpl);
		$tpl = $tpls[0].$delimiter.self::$ajaxFlag.$tpls[1];
		//$tpl = self::$ajaxFlag.$ajaxTpl;
		$data = str_replace($search, $replace, $tpl);
		
		return file_put_contents($filename, $data);
	}
	
	/**
	 * 压缩
	 * @param String $data
	 * @param String $type
	 */
	private static function minify($data, $type = 'HTML') {
		
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
	
	/**
	 * 是否是伪静态文件
	 * @param String $con
	 */
	public static function isAjaxFile($con = '') {
		if(empty($con)) $con = file_get_contents(self::$tplPath);
		return strstr($con, self::$ajaxFlag);
	}
	
	/**
	 * 检查变量是否有效 若无效 不更新静态页
	 * @param mixed $var
	 */
	public static function checkVar($var, $type = 'array') {
		$flag = false;
		eval("\$flag = is_$type(\$var);");
		
		if(!$flag) {
			//删除静态文件
			self::delHTML(self::$tplPath);
			//返回状态码
			if(self::$dir==='index') {
				if(strpos(self::$rootURL, 'http://www.xda.cn')) 
					$jumpto = 'http://bbs.xda.cn/';
				else $jumpto = 'http://www.xda.cn/';
			}else $jumpto = self::$rootURL;
			//D(self::$dir);
			if(!self::$isStatic)  header('Location:'.$jumpto);
			if(!isset(self::$req['from'])) header('Location:'.$jumpto);
				
			if(self::$req['from']=='ajax') {
					$arr=array("status"=>"0", "url"=>'"'.$jumpto.'"');
					echo self::$req['jsoncallback'].'('.json_encode($arr),')';					
			}elseif(self::$req['from']=='html') {
				die(json_encode(array('status'=>'01')));
			}			
			exit;
		}
		
	}
	
	/**
	 * 检查静态页更新
	 * 这个方法在init里头调用了, 所以不需要更新的时候要直接exit终止掉
	 * 不然会一直走完整个php文件直到末尾的获得ob缓冲并重新生成静态
	 */
	private static function checkUpdate() {
		if(!self::isWriteComplete() || self::isTimeout()) return;
		if(isset(self::$req['from']) && self::$req['from']=='html') {			
			exit;
		}else self::jump();
	}
	
	/**
	 * 跳转
	 */
	public static function jump($url = '') {
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
	private static function ob_end() {
		if(ob_get_length() > 0) ob_end_clean();
	}
	
	/**
	 * 加载负责发出更新请求的JS
	 */
	public static function loadScript() {
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
		echo $data;
	}
	
	/**
	 * 得到静态目录下所有文件夹和文件
	 * string $root 目录路径
	 * array $extensions 扩展名
	 * return array('files'=>array(...),'dirs'=>array(...))
	 *
	 */
	public static function htmlList() {
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
	private static function debug($msg, $line = '') {
		$msg = '['.date('Y-m-d H:i:s').'] '.$msg;
		if(!empty($line)) $msg .= ' throw in line '.$line;
		
		switch (intval(self::$debug)) {
		case 0: 
			return;
			break;
		
		case 1:
			exit($msg);
			break;
		
		case 2:
			self::mkDir(dirname(self::$debugFile));
			file_put_contents(self::$debugFile, $msg.PHP_EOL, FILE_APPEND);
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
	public static function getVars() {
		return get_class_vars(get_class());
	}
		

}//P2H class end

?>