<?php
if (!defined('M5CPL'))	exit;
/**
 * 
 * 路径解析
 * @author zhupp 20110823
 *
 */

class Path {
	public static $path = NULL;
	public static $html_dir = NULL;
    public static $args = array(
		'index'=>array(''),
	);

	private function __construct() {
		
	}
	private function __clone() {
		
	}

    public static function getInstance() {
        return !is_null(self::$path) ? self::$path : new self(); 
    }
	
	/**
	 * 返回PHP文件名(不带参数) 
	 * http://localhost/android/list_index.php?gid=1&cid=18将返回:
	 * list_index.php
	 */
	public function PHPName() {
		return basename($_SERVER['PHP_SELF']);
	}	

	/**
	 * 返回PHP文件名(不带.php后缀和参数) 
	 * http://localhost/android/list_index.php?gid=1&cid=18将返回:
	 * list_index
	 */
	public function PName() {
	     return strtolower(substr(self::PHPName(), 0, -4));
	}
	
	//HTML DIR
	public function get_htmls_dir() {
		if(is_null(self::$html_dir)) {
			$sf = $_SERVER['SCRIPT_FILENAME'];
			self::$html_dir = substr($sf, 0, strrpos($sf, '/')).'/html/';
		}		
		return self::$html_dir;
	}
	
	/**
     * 分解url得到dir和query(html目录和请求参数)供静态化使用
     * in:		http://localhost/android/list_index.php?gid=1&cid=69
     * out:	array(2) {
	 *			  ["d"] => string(10) "list_index"
	 *			  ["q"] => array(2) {
	 *			    ["gid"] => string(1) "1"
	 *			    ["cid"] => string(2) "69"
	 *			  }
	 *			}
     */
	public function dq($url='') {
		$dq_arr = array();
		$q = $req = array();
		$host = $d = '';

		if(empty($url)){
			$d = self::PName();
			$req = PorG();
			if($d=='index' || $d=='') return array('d'=>'index','q'=>'');
		}else{
			$path = parse_url($url);		
			//$d = preg_replace('/(\.php)|(\/)|(\.)/i', '', $path['path']);不够精确 故注释
			if(false!==strpos($path['path'], '/'))
				$d = substr($path['path'], strrpos($path['path'], '/')+1, -4);
			else $d = substr($path['path'], 0, -4);
			if($d=='index' || $d=='') return array('d'=>'index','q'=>'');
			parse_str($path['query'], $req);
		}
		//query要限制一下参数 这样做的好处是能限制参数去生成静态 ,
		//并且我们写的时候也可以不按照顺序写参数,而组装起来的静态地址是按照预定义顺序的		
		$args = self::$args;
		$q = array();
		if(isset($args[$d])) {
			foreach($args[$d] as $k=>$v) {
				if(is_null($req[$v])) $value = 0;
				else $value = $req[$v];
				$q[$v] = $value;
			}
		}
		$dq_arr['d'] = $d;
		$dq_arr['q'] = $q;
		return $dq_arr;
	}
	

	/**
	 * 静态页路径
	 * in:		http://localhost/android/list_index.php?gid=1&cid=69
	 * out:	list_index/1_69.html
	 */
	public function h($url='') {
			$dq = self::dq($url);

			if($dq['d']=='index') return 'index'.RW_END;
			
			$hs = '';
			foreach($dq['q'] as $k=>$v) {
				if($k!='fresh') $hs .=$v.RW_RULE;
			}
			$hs = rtrim($hs,RW_RULE);
					
			$args = explode(RW_RULE, $hs);
			for($i=count($args)-1; $i>=0; $i--) {
				if($args[$i]==0) {
					unset($args[$i]); //如果末尾有清一色0 干掉
				}else break;
			}
			$hs = implode('_', $args);
			$hs = empty($hs) ? 'index' : $hs;

			return  $dq['d'].'/'.$hs.RW_END;
	}
	
	
	
	/**
	 * go to html
	 */
	public function g2h($php='', $exit=true) {		
		if(!empty($php)) {
			$html_dir = self::get_htmls_dir().self::h($php);
			$html_path = HTML_PATH.self::h($php);
			if(file_exists($html_dir)) {
				//ob_end_clean();
				header("Location:".$html_path);
				if($exit) exit;
			}
		}
		//if $html is empty
		$h = self::h();
		
		if(file_exists(self::get_htmls_dir().$h)) {
			//ob_end_clean();
			header("Location:".HTML_PATH.$h);	
			if($exit) exit;			
		}
		
		
		/*
		$js_jump = '<script type="text/javascript">
			  					self.location="%s"
						 </script>';
		$js_jump = sprintf($js_jump, ANDROID_PATH.$h);
		
		echo $js_jump;
		*/
				
	}


} //Path类定义结束
?>