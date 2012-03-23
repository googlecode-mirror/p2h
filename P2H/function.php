<?php
/**
 * @exception 常用的小函数放在这儿
 * @copyright by vyouzhi on 20080811
 * @name function.php
 */

/**
* @ignore
*/
if (!defined('M5CPL'))exit;

/**
* UTF-8 中文切字
* @param    string    需要切分的字符串
* @param    int    切分开始处
* @param    int    切分的长度
* @return    int    切分后的字符串
*/
 
function msubstr($str, $start, $length=NULL)
{
	if(strlen($str) < $length){
		return $str;
	}
	
    preg_match_all("/./u", $str, $ar);
 
    if(func_num_args() >= 3) {
       $end = func_get_arg(2);
       return join("",array_slice($ar[0],$start,$end)).'...';
    } else {
       return join("",array_slice($ar[0],$start)).'...';
    }
}

	/**
	 +----------------------------------------------------------
	 * 字符串截取，支持中文和其他编码
	 +----------------------------------------------------------
	 * @param string $str 需要转换的字符串
	 * @param string $start 开始位置
	 * @param string $length 截取长度
	 * @param string $charset 编码格式
	 * @param string $suffix 截断显示字符
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
function S($str, $start=0, $length, $charset="utf-8", $suffix=true) {
		/*if(function_exists("mb_substr"))
			return mb_substr($str, $start, $length, $charset);
		elseif(function_exists('iconv_substr')) 
			return iconv_substr($str,$start,$length,$charset);*/
		
		if(strlen($str) <= $length) return $str;
	
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']	  = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']	  = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		
		$slice = join("",array_slice($match[0], $start, $length));
		
		//if($suffix) return $slice;
		
		return $slice;
}




// 浏览器友好的变量输出
function D($var, $echo=true, $label=null, $strict=true) {

    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        exit;
        //return null;
    }else
        return $output;
}


/**
 * ShowMsg
 *
 * @param string $msg
 * @param string $url
 * @param int $type
 */
function ShowMsg($msg,$url='',$limittime=0){
	
        $htmlhead  = "<html>\r\n<head>\r\n<title>系统提示</title>\r\n";
        $htmlhead .= "<meta http-equiv=\"Content-Type\" content=\"text/html;\" />\r\n";
        $htmlhead .= "<base target='_self'/>\r\n</head>\r\n";
        $htmlhead .= "<body leftmargin='0' topmargin='0'>\r\n<center>\r\n<script>\r\n";
        
        $htmlfoot  = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";

        if($limittime==0) $litime = 3000;
        else $litime = $limittime;

        if($url=="-1"){
                if($limittime==0) $litime = 3000;
                $url = "javascript:history.go(-1);";
        }

        if($url==""){
                $msg = "<script>alert(\"".str_replace("\"","“",$msg)."\");</script>";
        }else{
                $func = "var pgo=0;
                        function JumpUrl(){
                        if(pgo==0){ location='$url'; pgo=1; }
                        }\r\n";
                        
                $rmsg = $func;
                $rmsg .= "document.write(\"<br/>";
                $rmsg .= "<div style='width:400px;padding-top:4px;height:24;font-size:10pt;border-left:1px solid #b9df92;border-top:1px solid #b9df92;border-right:1px solid #b9df92;background-color:#def5c2;'>提示信息：</div>\");\r\n";
                $rmsg .= "document.write(\"<div style='width:400px;height:100;font-size:10pt;border:1px solid #b9df92;background-color:#f9fcf3'><br/><br/>\");\r\n";
                $rmsg .= "document.write(\"".str_replace("\"","“",$msg)."\");\r\n";
                $rmsg .= "document.write(\"";
                
                //if($onlymsg==0){
                if($url!="javascript:;" && $url!=""){ $rmsg .= "<br/><br/><a href='".$url."'>如果你的浏览器没反应，请点击这里...</a>"; }
                $rmsg .= "<br/><br/></div>\");\r\n";
                if($url!="javascript:;" && $url!=""){ $rmsg .= "setTimeout('JumpUrl()',$litime);"; }
                //}else{ $rmsg .= "<br/><br/></div>\");\r\n"; }
                $msg  = $htmlhead.$rmsg.$htmlfoot;
        }
        
        echo $msg;
        exit();
}

/**
*  insert_charset_header
*
*
*/

function insert_charset_header() {
	header('Content-Type: text/html; charset=UTF-8');
}

/**
 * PostorGet
 * @author by vyouzhi
 * @param string $action
 * @return string
 *   extract(array_map('itrim',string));
 */
function PorG(){
	$request = array();
	if(isset($_REQUEST)){
	    $request = $_REQUEST; 
	    @array_walk($request, filter);
	    return $request;
	}
}

function filter($v, $k){
	global $request; 
	if(is_array($v)){
		//foreach
        foreach($v as $ks=>$vs){
		    $v[$ks] = check($vs);
		}
	}else{
		$v = check($v);
	    $request[$k]=$v;
	}
}

function check($str){
	return $str;
	//return addslashes($str);	
}
/**
 *display
 *
 */
function display($filename){
    if(is_file(Tmp."/".$filename.".htm")) return Tmp."/".$filename.".htm";
	elseif(is_file(Tmp."/".$filename.".html") ) return Tmp."/".$filename.".html";
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


/**
 * MySQL 默认读写
 * @param string $rw  r w 
 * @param string $key  如果 $key = "1" 并且 rw = "w" 的时候，代表有返回值
 * @param int $extime 
 * @return array
 */
function GetPB($rw='r', $key='', $extime=0, $type=0){
	$returnid = 0;

   $key=='' && $key=md5(time());
	$key == 1 && $returnid = 1;
	
	return array("rw"=>$rw, "key"=>$key, "extime"=>$extime, "returnid"=>$returnid, "type"=>$type);
}

/**
 * MySQL 默认读写
 * @param string $rw  r w 
 * @param string $key  如果 $key = "1" 并且 rw = "w" 的时候，代表有返回值
 * @param int $extime 
 * @return array
 */
function GetPB_BBS($rw='r', $key='', $extime=0, $type=0){
	$bbs_pb = GetPB($rw, $key, $extime, $type);
	$bbs_pb["config"]="bbs.";
	
	return  $bbs_pb;
}

/**
 * MySQL 默认读写
 * @param string $rw  r w 
 * @param string $key  如果 $key = "1" 并且 rw = "w" 的时候，代表有返回值
 * @param int $extime 
 * @return array
 */
function GetPB_WPBBS($rw='r', $key='', $extime=0, $type=0){
	$bbs_pb = GetPB($rw, $key, $extime, $type);
	$bbs_pb["config"]="wp7.";
	
	return  $bbs_pb;
}

/**
 *
 *
 *
 */

function DebugSQL($st=0, $val=''){
	global $DEBUG_LOGFILE;

	if($st==0){
		if(defined('DEBUG_SQL')){
			$start_time = microtime_float();
			if(defined('DEBUG_LOG')){

				$log = "xjc in line. ".__LINE__."\n";
				$log .= "xjc in func: ".$_SERVER[PHP_SELF]."\n";
				$log .= "xjc is :<br />".$val. "\n";
				$logfile = file_get_contents(Root."/temp/logfile");
				file_put_contents(Root."/temp/logfile", $logfile.$log);
			}
		}
	}else{

		if(defined('DEBUG_SQL')){
			
			$end_time = microtime_float();
			$logfile = file_get_contents(Root."/temp/logfile");
			$log .= "link sql use time: ".($end_time - $start_time )."\n";
			file_put_contents(Root."/temp/logfile", $logfile.$log);
			
		}
	}
}




/**
 *ckeditor 分页
 *@Param $content , type string
 *@Param $pageIndex, type int
 *@Param $url, type string;
 *
 *return array, array[0] is the content, array[1] is the index
 */
function getPageIndex($content = null, $pageIndex = 1, $url = ''){
    $result = "";
    $index = "";
    $resultArray = array();
    if($content != null){
       
       $mode = '#<div style="page-break-after: always;">(.*)<span style="display: none;">&nbsp;</span></div>#iUs';

        $arr = preg_split($mode, $content);
        
        $pageSum = count($arr);
        //set the contents
        if($pageSum > 0){
            $result = $arr[$pageIndex - 1];
        }
        
        //set the index
        if(count($arr) == 1){
            $index = "共[1]页，第[1]页";
        }else{
            $index = "共[$pageSum]页,";
            for($i=0; $i<count($arr); $i++){
                $index .= '[<a href="'.$url.'?pageIndex='.($i+1).'">'.($i+1).'</a>]';
            }
        }  
    }
    $resultArray[0] = $result;
    $resultArray[1] = $index;
    return $resultArray;
}


/**
 * 根据ID获取昵称
 * @Param $id , type int
 * return string
 */

function getNameById($id){
	
	$format = '';
	$format = "SELECT nickname FROM `%s`.admin_user ";
	$format .= "WHERE `id` =$id";
	$sql = sprintf($format,ACL_DB);
	$res = MySQL::FetchArray($sql,GetPB());
	if(is_array($res)){
		return $res[0]['nickname'];
	}else{
		return 'NULL';
	}
}

/**
 * 是否显示
 * @Param $status , type string
 * return string
 */
function displayShow($status){

	if ($status =='F')
		return '否';
	if ($status =='A')
		return '是';
}

/**
 * 是否显示
 * @Param $status , type string
 * return string
 */
function is_hide($status) {
	if ($status ==0)		return false;
	elseif ($status ==1)	return true;
}


function putErrorPNG() {
	echo '<img src="'.MPIC_1.'/static/images/common/error.png" title="sorry,加载失败" />';
}


/**
 * GteSeo
 * @param int $type 1 代表首页 2 ....
 * @return array;
 *
 */

function GetSeo($type=1){
    $format = "SELECT * FROM `%s`.`seo` where `web`='1' and `types` ='%d' limit 1;";
	$sql = sprintf($format, WEB_SEO, $type);
	
	$res = MySQL::FetchArray($sql, GetPB());
	
	return $res[0];
}

function GetIP(){
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
	    if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
		$ip = $xip;
		break;
            }
        }
    }
    return $ip;
}

//去除链接
function strip_href($cont) {
	return preg_replace('/\<a(.*)href(.*)\<\/a\>/', '', $cont);
}

/*fck分页
<div style="page-break-after: always;">
	<span style="display: none;">&nbsp;</span></div>
*/
function fck_page($cont) { 
	//D($cont);
	$cont = htmlspecialchars_decode($cont);
	$pattern = "%<div style=\"page-break-after: always;?\">\\s*<span style=\"display: none;?\">&nbsp;</span>\\s*</div>%iUs";	
	$strSplit = preg_split($pattern, $cont, -1, PREG_SPLIT_NO_EMPTY); 
	
	return $strSplit;
}

/**
 *合并文件
 * @param type $arrs 
 */
function merge_file($module,$version,array $arrs, $dir=APP) {
	$target = APP.'/'.$dir;
	$suffix = substr($arrs[0], -(strrpos($arrs[0], '.')));
	if(!is_dir($target)) mkdir($target, 0777);
    $filename=$target.'/'.$module."_".$version.$suffix;
    $tmpfilename=$target.'/'.$module."_".$version.".txt";
    $isBuild=true;
    if(file_exists($tmpfilename)){
        $fp=fopen($tmpfilename,"r");
        $item=fgets($fp);
        if($item){
            $item=trim($item);
            if($item==$version){
                $isBuild=false;
            }
        }
    }
    if(!$isBuild){
        return basename($filename);
    }else{
        $str="";
        foreach($arrs as $url){
             $js=file_get_contents($url);
             $str=$str.$js."\r\n";
        }     
        if(!file_exists($filename)){
            $f1=fopen($filename,'w+');
            fwrite($f1, $str);
        }
        if(!file_exists($tmpfilename)){
            $f2=fopen($tmpfilename,'w+');
            fwrite($f2, $version);
        }
        unset($str);
        return basename($filename);
    }
    return '';
}

/*
*合并lib文件
* $ver:版本号,空则不合并, $dir:目录 $cache_pre合并和的文件名前缀
*/
function require_libs($ver="", $dir="./lib/", $cache_pre="cache_lib_") {
	$cache = $dir.'/'.$cache_pre.$ver.'.php';
	if(file_exists($cache)) {
		require $cache;
		return true;
	}
	
	$libs = array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
				$pathinfo = explode('.', $file);
				if($pathinfo[count($pathinfo)-1]==='php') {
					$libs[] = $dir.$file;
				}			
		}
	}
	//D($libs);
	if(empty($libs)) return null;
	
	if(empty($ver)) {
		foreach($libs as $v) {
			if(strpos($v, $cache_pre)===false) require $v;
		}
		return true;
	}
	
	$cont = '';
	foreach($libs as $k=>$v) {
		if(strpos($v, $cache_pre)!==false) {
			chmod($v, 0666);
			unlink($v);
			continue;
		}
		$cont .= file_get_contents($v);
	}
	$patterns = array('/\n[\s| ]*\r/',);
	$cont=preg_replace($patterns, '', $cont);
	file_put_contents($cache, $cont);
	require $cache;
}
/*
 *加载js文件
* $ver:版本号,空则不合并, $dir:目录 $cache_pre合并和的文件名前缀
*/
function load_js($ver="", $js=array(), $cache_dir="js_cache/") {
	if(empty($js)) return false;
	//如果没有版本号 则分别加载
	if(empty($ver)) {
		foreach($js as $v) {
			foreach($v as $vv) {
				echo '<script type="text/javascript" src="'.MJS_1.'static/js/'.$vv.'"></script>'.PHP_EOL;
			}
		}
		return true;
	}
	
	$ver = 'version_'.$ver;
	$jsdir = APP.'/html/'.$cache_dir.$ver.'/';
	if(false!==strpos(ROOT, 'http://www.xda.cn'))
			$jspath = 'http://img.xda-china.com/index/html/'.$cache_dir.$ver.'/';
	else $jspath = HTML_PATH.$cache_dir.$ver.'/';

	//如果存在当前版本文件 则直接加载
	if(is_dir($jsdir)) {
		if ($handle = opendir($jsdir)) {
			while (false!== ($file = readdir($handle))) {
				$pathinfo = explode('.', $file);
				if($pathinfo[count($pathinfo)-1]==='js') {
					echo '<script type="text/javascript" src="'.$jspath.$file.'"></script>';
				}
			}
		}
		return true;
	}else{
		if(!is_dir(APP.'/html/'.$cache_dir))	mkdir(APP.'/html/'.$cache_dir, 0777);
		if(!is_dir($jsdir))	mkdir($jsdir, 0777);
	}
	
	$cont = '';
	require_once Bin.'/JSMin.php';
	foreach($js as $k=>$v) {
		foreach($v as $vv) {
			$cont .= file_get_contents(APP.'/static/js/'.$vv);
		}
		$cont = JSMin::minify($cont); //压缩js
		$flag = file_put_contents(HTML_DIR.$cache_dir.$ver.'/'.$k.'.js', $cont);
		if(false!==$flag) echo '<script type="text/javascript" src="'.$jspath.$k.'.js"></script>';//加载
		$cont = '';
	}
	
	//if(false!==$flag) { //如果写入成功
		/*
		if ($handle = opendir(HTML_DIR)) {//删除原来的cache
			while (false!== ($file = readdir($handle))) {
					$pathinfo = explode('.', $file);
					if($pathinfo[count($pathinfo)-1]==='js') {
						if(strpos($file, $cache_pre)!==false && $file!==$cache_pre.$ver.'.js') {
							chmod(HTML_DIR.$file, 0666);
							unlink(HTML_DIR.$file);
						}
					}			
			}
		}
		*/
		
	/*}else{ //没有写入成功则分别加载
		foreach($js as $v) {
			echo '<script type="text/javascript" src="'.MJS_1.'static/js/'.$v.'"></script>'.PHP_EOL;
		}
	}
	*/
	
}
?>